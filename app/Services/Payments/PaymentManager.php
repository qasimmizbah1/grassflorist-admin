<?php

namespace App\Services\Payments;

use App\Models\Order;
use App\Models\PaymentGateway;
use App\Models\EmailTemplate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PaymentManager
{
    /**
     * Resolve gateway service instance by code.
     */
    public static function resolveService(string $code): ?PaymentGatewayInterface
    {
        $code = strtolower(trim($code));

        if (str_starts_with($code, 'hyperpay') || in_array($code, ['mada', 'applepay', 'stcpay'])) {
            return new HyperPayService();
        }

        if (str_starts_with($code, 'tabby')) {
            return new TabbyService();
        }

        if (str_starts_with($code, 'tamara')) {
            return new TamaraService();
        }

        return null;
    }

    /**
     * Mark an order as paid, update stock, trigger email, and write payment log.
     */
    public static function markOrderAsPaid(Order $order, string $transactionId, ?string $gateway = null, array $rawPayload = [], string $source = 'WEBHOOK'): bool
    {
        if ($order->payment_status === 'paid' && $order->status === 'processing') {
            return true; // Already processed
        }

        try {
            DB::transaction(function () use ($order, $transactionId, $gateway, $rawPayload, $source) {
                $order->payment_status = 'paid';
                $order->date_paid = now();
                if ($order->status === 'pending') {
                    $order->status = 'processing';
                }
                if (!empty($transactionId)) {
                    $order->razorpay_payment_id = $transactionId; // Transaction Ref
                }
                $order->save();

                // Record in payment logs
                \App\Models\PaymentLog::create([
                    'order_id' => $order->id,
                    'order_number' => $order->order_number ?? (string)$order->id,
                    'gateway' => $gateway ?? $order->payment_method ?? 'hyperpay',
                    'event_type' => $source,
                    'status' => 'success',
                    'razorpay_payment_id' => $transactionId,
                    'amount' => $order->total_amount,
                    'message' => "Payment successfully recorded via {$source}",
                    'payload' => $rawPayload,
                ]);
            });

            // Send Confirmation Email
            self::sendOrderConfirmationEmail($order);

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to mark order #{$order->id} as paid: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send bilingual order confirmation email using dynamic admin template.
     */
    public static function sendOrderConfirmationEmail(Order $order): void
    {
        try {
            $template = EmailTemplate::where('event_key', 'order_confirmed')
                ->where('is_active', true)
                ->first();

            if (!$template || empty($order->email)) {
                return;
            }

            $lang = $order->order_language ?? 'ar';

            $data = [
                'customer_name' => trim(($order->first_name ?? '') . ' ' . ($order->last_name ?? '')),
                'order_id' => $order->order_number ?? (string)$order->id,
                'total_amount' => number_format((float)$order->total_amount, 2) . ' SAR',
                'payment_method' => strtoupper((string)$order->payment_method),
                'delivery_date' => $order->delivery_date ?? date('Y-m-d'),
                'delivery_time' => $order->delivery_time ?? 'Standard Delivery',
                'recipient_name' => $order->recipient_name ?? $order->first_name ?? 'Recipient',
                'recipient_phone' => $order->recipient_phone ?? '',
                'gift_message' => $order->delivery_message ?? 'N/A',
            ];

            $rendered = $template->render($data, $lang);

            Mail::html($rendered['body'], function ($message) use ($order, $rendered) {
                $message->to($order->email)
                    ->subject($rendered['subject']);
            });
        } catch (\Exception $e) {
            Log::warning("Order confirmation email failed for #{$order->id}: " . $e->getMessage());
        }
    }
}
