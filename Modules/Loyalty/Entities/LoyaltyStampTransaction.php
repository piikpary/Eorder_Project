<?php

namespace Modules\Loyalty\Entities;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LoyaltyStampTransaction extends Model
{
    protected $table = 'loyalty_stamp_transactions';

    protected $guarded = ['id'];

    protected $casts = [
        'stamps' => 'integer',
        'expires_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::created(function ($transaction) {
            try {
                if (!in_array($transaction->type, ['EARN', 'REDEEM'], true)) {
                    return;
                }

                $customer = Customer::find($transaction->customer_id);

                if (!$customer || empty($customer->telegram_chat_id)) {
                    return;
                }

                if ((int) ($customer->telegram_notify_enabled ?? 0) !== 1) {
                    return;
                }

                $token = config('services.telegram_loyalty.bot_token') ?: env('TELEGRAM_LOYALTY_BOT_TOKEN');

                if (empty($token)) {
                    Log::warning('Telegram stamp alert skipped: bot token missing.');
                    return;
                }

                $rule = DB::table('loyalty_stamp_rules')
                    ->where('id', $transaction->stamp_rule_id)
                    ->first();

                $requiredStamps = max(1, (int) ($rule->stamps_required ?? 1));

                $customerStamp = DB::table('customer_stamps')
                    ->where('restaurant_id', $transaction->restaurant_id)
                    ->where('customer_id', $transaction->customer_id)
                    ->where('stamp_rule_id', $transaction->stamp_rule_id)
                    ->first();

                $availableAfter = 0;

                if ($customerStamp) {
                    $availableAfter = max(
                        0,
                        (int) $customerStamp->stamps_earned - (int) $customerStamp->stamps_redeemed
                    );
                }

                $messages = [];

                if ($transaction->type === 'EARN') {
                    $earnedStamps = abs((int) $transaction->stamps);
                    $availableBefore = max(0, $availableAfter - $earnedStamps);

                    $rewardsBefore = intdiv($availableBefore, $requiredStamps);
                    $rewardsAfter = intdiv($availableAfter, $requiredStamps);

                    $cycleStamps = $availableAfter % $requiredStamps;

                    if ($availableAfter >= $requiredStamps && $cycleStamps === 0) {
                        $cycleStamps = $requiredStamps;
                    }

                    $messages[] = "Stamp Earned\n"
                        . "Hello " . ($customer->name ?? 'Customer') . "!\n"
                        . "You earned {$earnedStamps} loyalty stamp.\n"
                        . "Current progress: {$cycleStamps}/{$requiredStamps}\n"
                        . "Order: #" . ($transaction->order_id ?? '-') . "\n"
                        . "Thank you for using eOrder.";

                    if ($rewardsAfter > $rewardsBefore) {
                        $rewardName = 'Reward';

                        if (!empty($rule->reward_type)) {
                            $rewardName = str_replace('_', ' ', ucfirst($rule->reward_type));
                        }

                        if (!empty($rule->reward_value)) {
                            $rewardName .= ' - ' . $rule->reward_value;
                        }

                        $messages[] = "Reward Available\n"
                            . "Hello " . ($customer->name ?? 'Customer') . "!\n"
                            . "Your loyalty reward is now available.\n"
                            . "Reward: {$rewardName}\n"
                            . "Stamps: {$availableAfter}/{$requiredStamps}\n"
                            . "Please show your loyalty card to cashier.\n"
                            . "Order: #" . ($transaction->order_id ?? '-');
                    }
                }

                if ($transaction->type === 'REDEEM') {
                    $messages[] = "Reward Redeemed\n"
                        . "Hello " . ($customer->name ?? 'Customer') . "!\n"
                        . "Your loyalty reward has been redeemed.\n"
                        . "Order: #" . ($transaction->order_id ?? '-') . "\n"
                        . "Thank you for using eOrder.";
                }

                foreach ($messages as $message) {
                    $response = Http::timeout(10)->post("https://api.telegram.org/bot{$token}/sendMessage", [
                        'chat_id' => $customer->telegram_chat_id,
                        'text' => $message,
                    ]);

                    if (!$response->successful()) {
                        Log::warning('Telegram stamp alert failed', [
                            'transaction_id' => $transaction->id,
                            'customer_id' => $transaction->customer_id,
                            'status' => $response->status(),
                            'body' => $response->body(),
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                Log::error('Telegram stamp alert exception', [
                    'transaction_id' => $transaction->id ?? null,
                    'customer_id' => $transaction->customer_id ?? null,
                    'error' => $e->getMessage(),
                ]);
            }
        });
    }

    /**
     * Get the restaurant.
     */
    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Restaurant::class);
    }

    /**
     * Get the customer.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Customer::class);
    }

    /**
     * Get the stamp rule.
     */
    public function stampRule(): BelongsTo
    {
        return $this->belongsTo(LoyaltyStampRule::class, 'stamp_rule_id');
    }

    /**
     * Get the order (if applicable).
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Order::class);
    }
}