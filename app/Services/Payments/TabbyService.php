<?php

namespace App\Services\Payments;

use App\Models\Order;
use App\Models\PaymentGateway;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TabbyService implements PaymentGatewayInterface
{
    protected PaymentGateway $gateway;
    protected string $baseUrl;
    protected array $credentials;

    public function __construct()
    {
        $this->gateway = PaymentGateway::where('code', 'tabby')->first() ?? new PaymentGateway();
        $this->credentials = $this->gateway->credentials ?? [];
        $this->baseUrl = 'https://api.tabby.ai/api/v2';
    }

    public function getPublicKey(): string
    {
        return $this->credentials['public_key'] ?? '';
    }

    public function getSecretKey(): string
    {
        return $this->credentials['secret_key'] ?? '';
    }

    public function getMerchantCode(): string
    {
        return $this->credentials['merchant_code'] ?? 'grassflorist';
    }

    public function initiateCheckout(Order $order, array $options = []): array
    {
        $secretKey = $this->getSecretKey();
        $merchantCode = $this->getMerchantCode();

        if (empty($secretKey)) {
            return [
                'success' => false,
                'message' => 'Tabby Secret Key not configured in Admin Settings.',
            ];
        }

        $items = [];
        if ($order->orderItems) {
            foreach ($order->orderItems as $item) {
                $items[] = [
                    'title' => $item->product_name ?? 'Flower Bouquet',
                    'quantity' => (int)($item->quantity ?? 1),
                    'unit_price' => number_format((float)($item->price ?? 0), 2, '.', ''),
                    'category' => 'Flowers & Gifts',
                ];
            }
        }

        if (empty($items)) {
            $items[] = [
                'title' => 'Fresh Flowers & Gifts Arrangement',
                'quantity' => 1,
                'unit_price' => number_format((float)$order->total_amount, 2, '.', ''),
                'category' => 'Flowers & Gifts',
            ];
        }

        $payload = [
            'payment' => [
                'amount' => number_format((float)$order->total_amount, 2, '.', ''),
                'currency' => 'SAR',
                'description' => "Grass Florist Order #{$order->order_number}",
                'buyer' => [
                    'phone' => $order->customer_phone ?? $order->recipient_phone ?? '+966500000000',
                    'email' => $order->email ?? 'customer@grassflorist.com',
                    'name' => trim(($order->first_name ?? 'Valued') . ' ' . ($order->last_name ?? 'Customer')),
                ],
                'shipping_address' => [
                    'city' => $order->city ?? 'Jeddah',
                    'address' => $order->address ?? 'Jeddah, Saudi Arabia',
                    'zip' => $order->zip_code ?? '22231',
                ],
                'order' => [
                    'reference_id' => (string)($order->order_number ?? $order->id),
                    'items' => $items,
                    'shipping_amount' => number_format((float)($order->shipping_amount ?? 0), 2, '.', ''),
                    'tax_amount' => number_format((float)($order->tax_amount ?? 0), 2, '.', ''),
                ],
                'buyer_history' => [
                    'registered_since' => date('c'),
                    'loyalty_level' => 0,
                ],
            ],
            'lang' => app()->getLocale() === 'ar' ? 'ar' : 'en',
            'merchant_code' => $merchantCode,
            'merchant_urls' => [
                'success' => url("/checkout/success?order_id={$order->id}"),
                'cancel' => url("/checkout/cancel?order_id={$order->id}"),
                'failure' => url("/checkout/failed?order_id={$order->id}"),
            ],
        ];

        try {
            $response = Http::timeout(5)->withToken($secretKey)
                ->post("{$this->baseUrl}/checkout", $payload);

            $data = $response->json();

            if ($response->successful() && !empty($data['id'])) {
                $webUrl = $data['configuration']['available_products']['installments'][0]['web_url'] ?? $data['web_url'] ?? null;

                return [
                    'success' => true,
                    'checkout_id' => $data['id'],
                    'redirect_url' => $webUrl,
                    'is_sandbox' => $this->gateway->is_sandbox,
                    'raw' => $data,
                ];
            }

            return [
                'success' => false,
                'message' => $data['error'] ?? $data['message'] ?? 'Tabby checkout session creation failed.',
                'raw' => $data,
            ];
        } catch (\Exception $e) {
            Log::error('Tabby initiateCheckout error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function verifyPayment(string $referenceId): array
    {
        $secretKey = $this->getSecretKey();
        if (empty($secretKey)) {
            return ['is_paid' => false, 'status' => 'unconfigured'];
        }

        try {
            $response = Http::timeout(5)->withToken($secretKey)->get("{$this->baseUrl}/payments/{$referenceId}");
            $data = $response->json();

            $status = strtolower($data['status'] ?? '');
            $isPaid = in_array($status, ['authorized', 'captured', 'closed']);

            return [
                'is_paid' => $isPaid,
                'status' => $status,
                'transaction_id' => $data['id'] ?? $referenceId,
                'amount' => isset($data['amount']) ? (float)$data['amount'] : null,
                'raw' => $data,
            ];
        } catch (\Exception $e) {
            Log::error('Tabby verifyPayment error: ' . $e->getMessage());
            return ['is_paid' => false, 'status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function handleWebhook(array $payload, array $headers = []): array
    {
        $paymentId = $payload['id'] ?? null;
        $orderNumber = $payload['order']['reference_id'] ?? null;
        $status = strtolower($payload['status'] ?? '');
        $isPaid = in_array($status, ['authorized', 'captured', 'closed']);

        return [
            'handled' => true,
            'checkout_id' => $paymentId,
            'order_number' => $orderNumber,
            'is_paid' => $isPaid,
            'transaction_id' => $paymentId,
            'raw' => $payload,
        ];
    }
}
