<?php

namespace App\Services\Payments;

use App\Models\Order;
use App\Models\PaymentGateway;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HyperPayService implements PaymentGatewayInterface
{
    protected PaymentGateway $gateway;
    protected string $baseUrl;
    protected array $credentials;

    public function __construct()
    {
        $this->gateway = PaymentGateway::where('code', 'hyperpay')->first() ?? new PaymentGateway();
        $this->credentials = $this->gateway->credentials ?? [];
        $isSandbox = $this->gateway->is_sandbox ?? true;

        $this->baseUrl = $isSandbox ? 'https://test.oppwa.com' : 'https://oppwa.com';
    }

    /**
     * Get appropriate Entity ID based on payment brand/method.
     */
    public function getEntityId(?string $brand = null): string
    {
        $brand = strtolower(trim((string)$brand));

        if (in_array($brand, ['mada', 'hyperpay_mada'])) {
            return $this->credentials['entity_id_mada'] ?? $this->credentials['entity_id_visa_master'] ?? '';
        }

        if (in_array($brand, ['applepay', 'apple_pay', 'hyperpay_applepay'])) {
            return $this->credentials['entity_id_applepay'] ?? $this->credentials['entity_id_visa_master'] ?? '';
        }

        if (in_array($brand, ['stcpay', 'stc_pay', 'hyperpay_stcpay'])) {
            return $this->credentials['entity_id_stcpay'] ?? $this->credentials['entity_id_visa_master'] ?? '';
        }

        return $this->credentials['entity_id_visa_master'] ?? $this->credentials['entity_id_mada'] ?? '';
    }

    public function getAccessToken(): string
    {
        return $this->credentials['access_token'] ?? '';
    }

    /**
     * Initiate HyperPay Checkout session
     */
    public function initiateCheckout(Order $order, array $options = []): array
    {
        $brand = $options['payment_brand'] ?? $order->payment_method ?? 'visa';
        $entityId = $this->getEntityId($brand);
        $token = $this->getAccessToken();

        if (empty($token) || empty($entityId)) {
            return [
                'success' => false,
                'message' => 'HyperPay credentials (Access Token or Entity ID) not configured in Admin Settings.',
            ];
        }

        $params = [
            'entityId' => $entityId,
            'amount' => number_format((float)$order->total_amount, 2, '.', ''),
            'currency' => 'SAR',
            'paymentType' => 'DB', // Direct Debit / Purchase
            'merchantTransactionId' => (string)($order->order_number ?? $order->id),
            'customer.email' => $order->email ?? 'customer@grassflorist.com',
            'customer.givenName' => $order->first_name ?? 'Customer',
            'customer.surname' => $order->last_name ?? 'GrassFlorist',
            'billing.street1' => substr($order->address ?? 'Jeddah City', 0, 50),
            'billing.city' => $order->city ?? 'Jeddah',
            'billing.state' => $order->state ?? 'Makkah Region',
            'billing.country' => 'SA',
            'billing.postcode' => $order->zip_code ?? '22231',
        ];

        try {
            $response = Http::timeout(5)
                ->withToken($token)
                ->asForm()
                ->post("{$this->baseUrl}/v1/checkouts", $params);

            $data = $response->json();

            if ($response->successful() && !empty($data['id'])) {
                return [
                    'success' => true,
                    'checkout_id' => $data['id'],
                    'entity_id' => $entityId,
                    'is_sandbox' => $this->gateway->is_sandbox,
                    'script_url' => "{$this->baseUrl}/v1/paymentWidgets.js?checkoutId={$data['id']}",
                    'raw' => $data,
                ];
            }

            return [
                'success' => false,
                'message' => $data['result']['description'] ?? 'HyperPay checkout initiation failed.',
                'raw' => $data,
            ];
        } catch (\Exception $e) {
            Log::error('HyperPay initiateCheckout error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Exception in HyperPay initiateCheckout: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Query / Verify transaction status directly from HyperPay API
     */
    public function verifyPayment(string $referenceId, ?string $brand = null): array
    {
        $entityId = $this->getEntityId($brand);
        $token = $this->getAccessToken();

        if (empty($token) || empty($entityId)) {
            return [
                'is_paid' => false,
                'status' => 'unconfigured',
                'message' => 'HyperPay credentials missing.',
            ];
        }

        try {
            $url = "{$this->baseUrl}/v1/checkouts/{$referenceId}/payment?entityId={$entityId}";
            $response = Http::timeout(5)->withToken($token)->get($url);
            $data = $response->json();

            $resultCode = $data['result']['code'] ?? '';
            // Regex for HyperPay successful transaction codes
            $isSuccessful = (bool)preg_match('/^(000\.000\.|000\.100\.1|000\.[36])/', $resultCode);

            return [
                'is_paid' => $isSuccessful,
                'status' => $isSuccessful ? 'paid' : 'pending_or_failed',
                'result_code' => $resultCode,
                'result_description' => $data['result']['description'] ?? '',
                'transaction_id' => $data['id'] ?? null,
                'amount' => isset($data['amount']) ? (float)$data['amount'] : null,
                'payment_brand' => $data['paymentBrand'] ?? null,
                'raw' => $data,
            ];
        } catch (\Exception $e) {
            Log::error('HyperPay verifyPayment error: ' . $e->getMessage());
            return [
                'is_paid' => false,
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Handle incoming server-to-server webhook from HyperPay
     */
    public function handleWebhook(array $payload, array $headers = []): array
    {
        // HyperPay webhook payload parsing
        $checkoutId = $payload['id'] ?? $payload['checkoutId'] ?? null;
        $merchantTxId = $payload['merchantTransactionId'] ?? null;
        $resultCode = $payload['result']['code'] ?? '';
        $isSuccessful = (bool)preg_match('/^(000\.000\.|000\.100\.1|000\.[36])/', $resultCode);

        return [
            'handled' => true,
            'checkout_id' => $checkoutId,
            'order_number' => $merchantTxId,
            'is_paid' => $isSuccessful,
            'transaction_id' => $payload['id'] ?? null,
            'raw' => $payload,
        ];
    }
}
