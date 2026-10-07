<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentGateway;
use App\Models\DeliverySlot;
use App\Models\EmailTemplate;

class PhaseOneSettingsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Payment Gateways
        $gateways = [
            [
                'code' => 'hyperpay',
                'name_en' => 'HyperPay (Mada, Apple Pay, Visa/Mastercard, STC Pay)',
                'name_ar' => 'هايبر باي (مدى، أبل باي، فيزا/ماستركارد، إس تي سي باي)',
                'description_en' => 'All Saudi payment methods including Mada, Apple Pay, Credit/Debit cards and STC Pay.',
                'description_ar' => 'جميع وسائل الدفع السعودية بما فيها مدى، أبل باي، البطاقات الائتمانية و STC Pay.',
                'icon' => 'heroicon-o-credit-card',
                'is_active' => true,
                'is_sandbox' => true,
                'credentials' => [
                    'access_token' => '',
                    'entity_id_mada' => '',
                    'entity_id_applepay' => '',
                    'entity_id_visa_master' => '',
                    'entity_id_stcpay' => '',
                    'webhook_secret' => '',
                ],
                'min_order_amount' => 1.00,
                'max_order_amount' => 50000.00,
                'extra_fee' => 0.00,
                'sort_order' => 1,
            ],
            [
                'code' => 'tabby',
                'name_en' => 'Tabby (Pay in 4 Installments)',
                'name_ar' => 'تابي (قسمها على 4 دفعات بدون فوائد)',
                'description_en' => 'Split your bill in 4 interest-free payments.',
                'description_ar' => 'قسّم فاتورتك على 4 دفعات شهرية بدون أي فوائد أو رسوم إضافية.',
                'icon' => 'heroicon-o-calendar-days',
                'is_active' => true,
                'is_sandbox' => true,
                'credentials' => [
                    'public_key' => '',
                    'secret_key' => '',
                    'merchant_code' => '',
                ],
                'min_order_amount' => 50.00,
                'max_order_amount' => 5000.00,
                'extra_fee' => 0.00,
                'sort_order' => 2,
            ],
            [
                'code' => 'tamara',
                'name_en' => 'Tamara (Split in 3 or 4 Payments)',
                'name_ar' => 'تمارا (قسمها على 3 أو 4 دفعات)',
                'description_en' => 'Shop now and split your payments easily with Tamara.',
                'description_ar' => 'تسوق الآن وقسم مشترياتك بكل سهولة مع تمارا بدون فوائد.',
                'icon' => 'heroicon-o-sparkles',
                'is_active' => true,
                'is_sandbox' => true,
                'credentials' => [
                    'api_token' => '',
                    'notification_token' => '',
                    'public_key' => '',
                ],
                'min_order_amount' => 50.00,
                'max_order_amount' => 5000.00,
                'extra_fee' => 0.00,
                'sort_order' => 3,
            ],
            [
                'code' => 'paypal',
                'name_en' => 'PayPal Express',
                'name_ar' => 'باي بال إكسبريس',
                'description_en' => 'Safe & secure international checkout via PayPal.',
                'description_ar' => 'الدفع الآمن دولياً عبر حساب باي بال أو البطاقات العالمية.',
                'icon' => 'heroicon-o-globe-alt',
                'is_active' => true,
                'is_sandbox' => true,
                'credentials' => [
                    'client_id' => '',
                    'client_secret' => '',
                ],
                'min_order_amount' => 10.00,
                'max_order_amount' => 10000.00,
                'extra_fee' => 0.00,
                'sort_order' => 4,
            ],
            [
                'code' => 'cod',
                'name_en' => 'Cash on Delivery (COD)',
                'name_ar' => 'الدفع عند الاستلام',
                'description_en' => 'Pay cash when your flowers are delivered to the recipient address.',
                'description_ar' => 'ادفع نقداً عند استلام باقة الورد في العنوان المحدد.',
                'icon' => 'heroicon-o-banknotes',
                'is_active' => true,
                'is_sandbox' => false,
                'credentials' => [],
                'min_order_amount' => 1.00,
                'max_order_amount' => 1000.00,
                'extra_fee' => 15.00, // 15 SAR handling fee
                'sort_order' => 5,
            ],
        ];

        foreach ($gateways as $gw) {
            PaymentGateway::updateOrCreate(['code' => $gw['code']], $gw);
        }

        // 2. Seed Delivery Slots (Regular Days vs Friday)
        $slots = [
            // Regular Days (Sat - Thu)
            [
                'day_type' => 'regular',
                'title_en' => '11:00 AM to 03:00 PM',
                'title_ar' => 'من 11 صباحاً وحتى 3 مساءً',
                'start_time' => '11:00:00',
                'end_time' => '15:00:00',
                'cutoff_hours_before' => 1,
                'max_orders_capacity' => 50,
                'extra_charge' => 0.00,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'day_type' => 'regular',
                'title_en' => '07:00 PM to 10:00 PM',
                'title_ar' => 'من 7 مساءً وحتى 10 مساءً',
                'start_time' => '19:00:00',
                'end_time' => '22:00:00',
                'cutoff_hours_before' => 1,
                'max_orders_capacity' => 50,
                'extra_charge' => 0.00,
                'is_active' => true,
                'sort_order' => 2,
            ],
            // Friday Special Slots (Jummah Prayer adjusted)
            [
                'day_type' => 'friday',
                'title_en' => '04:00 PM to 07:00 PM',
                'title_ar' => 'من 4 مساءً وحتى 7 مساءً',
                'start_time' => '16:00:00',
                'end_time' => '19:00:00',
                'cutoff_hours_before' => 1,
                'max_orders_capacity' => 40,
                'extra_charge' => 0.00,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'day_type' => 'friday',
                'title_en' => '07:30 PM to 10:00 PM',
                'title_ar' => 'من 7 و النصف مساءً وحتى 10 مساءً',
                'start_time' => '19:30:00',
                'end_time' => '22:00:00',
                'cutoff_hours_before' => 1,
                'max_orders_capacity' => 40,
                'extra_charge' => 0.00,
                'is_active' => true,
                'sort_order' => 2,
            ],
        ];

        DeliverySlot::truncate();
        foreach ($slots as $sl) {
            DeliverySlot::create($sl);
        }

        // 3. Seed Email Templates
        $emailTemplates = [
            [
                'event_key' => 'order_confirmed',
                'name' => 'Order Confirmation & Payment Success',
                'subject_en' => 'Thank you for your order #{order_id} - Grass Florist 🌸',
                'subject_ar' => 'شكراً لطلبك #{order_id} من جراس فلوريست 🌸',
                'body_en' => '<h2>Hello {customer_name},</h2><p>Thank you for choosing Grass Florist! Your payment of <strong>{total_amount}</strong> via <strong>{payment_method}</strong> has been received successfully.</p><p><strong>Delivery Details:</strong><br>Date: {delivery_date}<br>Time: {delivery_time}<br>Recipient: {recipient_name} ({recipient_phone})</p><p>Gift Message: <em>"{gift_message}"</em></p><p>We are preparing your fresh flowers with care!</p>',
                'body_ar' => '<h2 dir="rtl">مرحباً {customer_name}،</h2><p dir="rtl">شكراً لاختيارك جراس فلوريست! تم تأكيد طلبك واستلام المبلغ <strong>{total_amount}</strong> بنجاح عبر <strong>{payment_method}</strong>.</p><p dir="rtl"><strong>تفاصيل التوصيل:</strong><br>التاريخ: {delivery_date}<br>الفترة: {delivery_time}<br>المستلم: {recipient_name} ({recipient_phone})</p><p dir="rtl">رسالة الإهداء: <em>"{gift_message}"</em></p><p dir="rtl">نقوم الآن بتجهيز وتنسيق باقتك بكل حب وعناية!</p>',
                'recipient_type' => 'both',
                'allowed_shortcodes' => '{customer_name}, {order_id}, {total_amount}, {payment_method}, {delivery_date}, {delivery_time}, {recipient_name}, {recipient_phone}, {gift_message}',
                'is_active' => true,
            ],
            [
                'event_key' => 'order_failed',
                'name' => 'Payment Incomplete / Failed Alert',
                'subject_en' => 'Action Required: Incomplete Payment for Order #{order_id}',
                'subject_ar' => 'تنبيه: لم يكتمل الدفع لطلبك رقم #{order_id}',
                'body_en' => '<h2>Hello {customer_name},</h2><p>We noticed your payment for order #{order_id} ({total_amount}) was not completed or faced an issue.</p><p>Your flower bouquet is saved in your cart. Please <a href="{retry_payment_url}">click here to complete your payment</a>.</p>',
                'body_ar' => '<h2 dir="rtl">مرحباً {customer_name}،</h2><p dir="rtl">لاحظنا عدم اكتمال عملية الدفع لطلبك رقم #{order_id} بقيمة ({total_amount}).</p><p dir="rtl">باقتك محفوظة في سلتك. <a href="{retry_payment_url}">اضغط هنا لإتمام الدفع بسهولة</a>.</p>',
                'recipient_type' => 'customer',
                'allowed_shortcodes' => '{customer_name}, {order_id}, {total_amount}, {retry_payment_url}',
                'is_active' => true,
            ],
            [
                'event_key' => 'order_shipped',
                'name' => 'Out for Delivery Notification',
                'subject_en' => 'Your flowers for Order #{order_id} are on the way! 🚗🌹',
                'subject_ar' => 'طلبك رقم #{order_id} خرج للتوصيل الآن! 🚗🌹',
                'body_en' => '<h2>Hello {customer_name},</h2><p>Great news! Your fresh flower arrangement for Order #{order_id} is now out for delivery to {recipient_name}.</p><p>Delivery Slot: {delivery_time}</p>',
                'body_ar' => '<h2 dir="rtl">مرحباً {customer_name}،</h2><p dir="rtl">خبر سار! باقة الورد لطلبك رقم #{order_id} خرجت الآن مع مندوب التوصيل في طريقها إلى {recipient_name}.</p><p dir="rtl">الفترة المحددة للتسليم: {delivery_time}</p>',
                'recipient_type' => 'both',
                'allowed_shortcodes' => '{customer_name}, {order_id}, {recipient_name}, {delivery_time}, {tracking_link}',
                'is_active' => true,
            ],
            [
                'event_key' => 'order_cancelled',
                'name' => 'Order Cancelled / Refunded',
                'subject_en' => 'Order #{order_id} Cancellation Update - Grass Florist',
                'subject_ar' => 'تحديث بشأن إلغاء الطلب رقم #{order_id} - جراس فلوريست',
                'body_en' => '<h2>Hello {customer_name},</h2><p>Your order #{order_id} has been cancelled. If any payment was captured, the refund of {total_amount} will be processed back to your original payment method.</p>',
                'body_ar' => '<h2 dir="rtl">مرحباً {customer_name}،</h2><p dir="rtl">تم إلغاء طلبك رقم #{order_id}. إذا تم خصم أي مبلغ مسبقاً، فسيتم استرجاع مبلغ {total_amount} إلى وسيلة الدفع الأصلية.</p>',
                'recipient_type' => 'both',
                'allowed_shortcodes' => '{customer_name}, {order_id}, {total_amount}',
                'is_active' => true,
            ],
        ];

        foreach ($emailTemplates as $et) {
            EmailTemplate::updateOrCreate(['event_key' => $et['event_key']], $et);
        }
    }
}
