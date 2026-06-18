<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class ShopNavigation extends Component
{
    public $restaurant;
    public $shopBranch;

    public bool $showLoyaltyButton = false;
    public bool $loyaltyIdentified = false;

    public int $loyaltyCurrent = 0;
    public int $loyaltyRequired = 0;

    public string $loyaltyUrl = '';

    public function mount(): void
    {
        $this->loadLoyaltyHeader();
    }

    private function loadLoyaltyHeader(): void
{
    $this->loyaltyUrl = route('loyalty.login');

    // Always show the loyalty button in the customer header.
    $this->showLoyaltyButton = true;
    $this->loyaltyIdentified = false;
    $this->loyaltyCurrent = 0;
    $this->loyaltyRequired = 0;

    if (
        !$this->restaurant
        || !Schema::hasTable('customers')
        || !Schema::hasTable('loyalty_stamp_rules')
        || !Schema::hasTable('customer_stamps')
    ) {
        return;
    }

    $restaurantId = (int) $this->restaurant->id;

    $ruleQuery = DB::table('loyalty_stamp_rules')
        ->where('is_active', 1);

    if (
        Schema::hasColumn(
            'loyalty_stamp_rules',
            'restaurant_id'
        )
    ) {
        $ruleQuery->where(
            'restaurant_id',
            $restaurantId
        );
    }

    $rule = $ruleQuery
        ->orderBy('id')
        ->first();

    // Button remains visible even when no active rule exists.
    if (!$rule) {
        return;
    }

    $this->loyaltyRequired = max(
        1,
        (int) ($rule->stamps_required ?? 1)
    );

    $token = session(
        'loyalty_customer_token_' . $restaurantId
    );

    if (empty($token)) {
        return;
    }

    $customer = DB::table('customers')
        ->where('restaurant_id', $restaurantId)
        ->where('loyalty_token', $token)
        ->first();

    if (!$customer) {
        session()->forget(
            'loyalty_customer_token_' . $restaurantId
        );

        return;
    }

    $this->loyaltyIdentified = true;

    $this->loyaltyUrl = route(
        'loyalty.card',
        [
            'token' => $customer->loyalty_token,
        ]
    );

    $stampQuery = DB::table('customer_stamps')
        ->where('customer_id', $customer->id)
        ->where('stamp_rule_id', $rule->id);

    if (
        Schema::hasColumn(
            'customer_stamps',
            'restaurant_id'
        )
    ) {
        $stampQuery->where(
            'restaurant_id',
            $restaurantId
        );
    }

    $customerStamp = $stampQuery->first();

    $earned = (int) (
        $customerStamp?->stamps_earned ?? 0
    );

    $redeemed = (int) (
        $customerStamp?->stamps_redeemed ?? 0
    );

    $this->loyaltyCurrent = max(
        0,
        $earned - $redeemed
    );
}
    private function getPackageModules($restaurant): array
    {
        if (!$restaurant || !$restaurant->package) {
            return [];
        }

        $modules = $restaurant
            ->package
            ->modules
            ->pluck('name')
            ->toArray();

        $additionalFeatures = json_decode(
            $restaurant->package->additional_features ?? '[]',
            true
        );

        return array_merge(
            $modules,
            $additionalFeatures
        );
    }

    public function render()
    {
        $modules = $this->getPackageModules(
            $this->restaurant
        );

        return view(
            'livewire.shop-navigation',
            [
                'modules' => $modules,
            ]
        );
    }
}