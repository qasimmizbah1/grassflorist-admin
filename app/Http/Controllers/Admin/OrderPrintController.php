<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderPrintController extends Controller
{
    /**
     * Render Printable Florist Gift Card
     */
    public function printGiftCard(Request $request, $id): View
    {
        $order = Order::with('items')->findOrFail($id);

        if ($request->has('custom_message')) {
            $order->delivery_message = $request->query('custom_message');
        }
        if ($request->has('sender_name')) {
            $order->sender_name = $request->query('sender_name');
        }
        if ($request->has('recipient_name')) {
            $order->recipient_name = $request->query('recipient_name');
        }

        return view('prints.gift_card', compact('order'));
    }

    /**
     * Render Saudi Arabia ZATCA-compliant Tax Invoice
     */
    public function printTaxInvoice($id): View
    {
        $order = Order::with('items')->findOrFail($id);

        return view('prints.tax_invoice', compact('order'));
    }
}
