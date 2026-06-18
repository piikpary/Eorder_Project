<?php

namespace App\Livewire;

use App\Support\CustomerDisplayPayload;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class CustomerDisplay extends Component
{
    public $orderItems = [];
    public $subTotal = 0;
    public $total = 0;
    public $discount = 0;
    public $orderNumber = null;
    public $taxes = [];
    public $extraCharges = [];
    public $tip = 0;
    public $deliveryFee = 0;
    public $orderType = null;
    public $status = 'idle';
    public $cashDue = null;
    public $qrCodeImageUrl = null;
    public $formattedOrderNumber = null;
    public $refreshKey = 0;

    public function render()
    {
        $this->loadCustomerDisplay();

        return view('livewire.customer-display');
    }

    private function loadCustomerDisplay(): void
    {
        $userId = auth()->id();

        $cacheKey =
            'customer_display_cart_user_' . $userId;

        $cachedCart = Cache::get($cacheKey);

        if (!is_array($cachedCart)) {
            $this->resetCustomerDisplay();

            return;
        }

        $cart = CustomerDisplayPayload::normalize(
            $cachedCart
        );

        $this->orderNumber =
            $cart['order_number'] ?? null;

        $this->formattedOrderNumber =
            $cart['formatted_order_number'] ?? null;

        $this->subTotal =
            $cart['sub_total'] ?? 0;

        $this->total =
            $cart['total'] ?? 0;

        $this->discount =
            $cart['discount'] ?? 0;

        $this->orderItems =
            $cart['items'] ?? [];

        $this->taxes =
            $cart['taxes'] ?? [];

        $this->extraCharges =
            $cart['extra_charges'] ?? [];

        $this->tip =
            $cart['tip'] ?? 0;

        $this->deliveryFee =
            $cart['delivery_fee'] ?? 0;

        $this->orderType =
            $cart['order_type'] ?? null;

        /*
         * Read KHQR values from the original cache.
         * This prevents the normalizer from removing them.
         */
        $this->status =
            $cachedCart['status']
            ?? $cart['status']
            ?? 'idle';

        $this->cashDue =
            $cachedCart['cash_due']
            ?? $cart['cash_due']
            ?? null;

        $this->qrCodeImageUrl =
            $cachedCart['qr_code_image_url']
            ?? $cart['qr_code_image_url']
            ?? null;
    }

    private function resetCustomerDisplay(): void
    {
        $this->orderNumber = null;
        $this->formattedOrderNumber = null;
        $this->subTotal = 0;
        $this->total = 0;
        $this->discount = 0;
        $this->orderItems = [];
        $this->taxes = [];
        $this->extraCharges = [];
        $this->tip = 0;
        $this->deliveryFee = 0;
        $this->orderType = null;
        $this->status = 'idle';
        $this->cashDue = null;
        $this->qrCodeImageUrl = null;
    }

    public function refreshCustomerDisplay(): void
    {
        $this->refreshKey++;
    }
}