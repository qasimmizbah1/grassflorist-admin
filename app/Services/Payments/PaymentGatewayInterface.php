<?php

namespace App\Services\Payments;

use App\Models\Order;

interface PaymentGatewayInterface
{
    /**
     * Create a checkout session / transaction with the gateway.
     *
     * @param Order $order
     * @param array $options
     * @return array ['success' => bool, 'checkout_id' => string, 'redirect_url' => string, 'raw' => array]
     */
    public function initiateCheckout(Order $order, array $options = []): array;

    /**
     * Verify payment status directly with the gateway API.
     *
     * @param string $referenceId (checkout_id, payment_id, or order_number)
     * @return array ['is_paid' => bool, 'status' => string, 'transaction_id' => ?string, 'amount' => ?float, 'raw' => array]
     */
    public function verifyPayment(string $referenceId): array;

    /**
     * Process an incoming server-to-server webhook.
     *
     * @param array $payload
     * @param array $headers
     * @return array ['handled' => bool, 'order_number' => ?string, 'is_paid' => bool, 'raw' => array]
     */
    public function handleWebhook(array $payload, array $headers = []): array;
}
