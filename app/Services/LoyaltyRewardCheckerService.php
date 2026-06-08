<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class LoyaltyRewardCheckerService
{
    public function checkOrder($order): void
    {
        try {
            if (function_exists('module_enabled') && !module_enabled('Loyalty')) {
                return;
            }

            $customerId = $order->customer_id ?? $order->customer?->id ?? null;

            if (!$customerId) {
                return;
            }

            $stampRuleTable = $this->findTable([
                'loyalty_stamp_rules',
                'loyalty_stamp_rule',
                'stamp_rules',
            ]);

            $customerStampTable = $this->findTable([
                'loyalty_customer_stamps',
                'customer_loyalty_stamps',
                'loyalty_stamps',
                'customer_stamps',
            ]);

            if (!$stampRuleTable || !$customerStampTable) {
                Log::warning('Loyalty stamp tables not found.', [
                    'stamp_rule_table' => $stampRuleTable,
                    'customer_stamp_table' => $customerStampTable,
                ]);

                return;
            }

            $rules = DB::table($stampRuleTable)->get();

            foreach ($rules as $rule) {
                $ruleId = $rule->id;
                $requiredStamps = (int) ($rule->stamps_required ?? $rule->required_stamps ?? 0);

                if ($requiredStamps <= 0) {
                    continue;
                }

                $customerStamp = DB::table($customerStampTable)
                    ->where('customer_id', $customerId)
                    ->where(function ($query) use ($ruleId) {
                        $query->where('stamp_rule_id', $ruleId)
                            ->orWhere('loyalty_stamp_rule_id', $ruleId);
                    })
                    ->first();

                if (!$customerStamp) {
                    continue;
                }

                $currentStamps = (int) (
                    $customerStamp->stamps
                    ?? $customerStamp->stamp_count
                    ?? $customerStamp->current_stamps
                    ?? $customerStamp->total_stamps
                    ?? 0
                );

                if ($currentStamps < $requiredStamps) {
                    continue;
                }

                $rewardName = $this->getRewardName($rule);
                $rewardKey = 'stamp_rule_' . $ruleId . '_target_' . $requiredStamps;

                $alreadySent = DB::table('loyalty_reward_telegram_logs')
                    ->where('customer_id', $customerId)
                    ->where('stamp_rule_id', $ruleId)
                    ->where('reward_key', $rewardKey)
                    ->exists();

                if ($alreadySent) {
                    continue;
                }

                $customer = $this->getCustomer($customerId);

                $data = [
                    'customer_id' => $customerId,
                    'order_id' => $order->id ?? null,
                    'stamp_rule_id' => $ruleId,
                    'reward_key' => $rewardKey,
                    'customer_name' => $customer->name ?? $customer->customer_name ?? '-',
                    'customer_phone' => $customer->phone ?? $customer->mobile ?? '-',
                    'reward_name' => $rewardName,
                    'current_stamps' => $currentStamps,
                    'required_stamps' => $requiredStamps,
                    'order_no' => $order->order_number ?? $order->order_no ?? $order->show_formatted_order_number ?? ('#' . ($order->id ?? '-')),
                ];

                app(LoyaltyTelegramService::class)->sendRewardReached($data);

                DB::table('loyalty_reward_telegram_logs')->insert([
                    'customer_id' => $data['customer_id'],
                    'order_id' => $data['order_id'],
                    'stamp_rule_id' => $data['stamp_rule_id'],
                    'reward_key' => $data['reward_key'],
                    'customer_name' => $data['customer_name'],
                    'customer_phone' => $data['customer_phone'],
                    'reward_name' => $data['reward_name'],
                    'current_stamps' => $data['current_stamps'],
                    'required_stamps' => $data['required_stamps'],
                    'sent_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Loyalty reward checker failed: ' . $e->getMessage(), [
                'order_id' => $order->id ?? null,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }
    }

    private function findTable(array $tables): ?string
    {
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                return $table;
            }
        }

        return null;
    }

    private function getCustomer(int $customerId): ?object
    {
        foreach (['customers', 'clients'] as $table) {
            if (Schema::hasTable($table)) {
                $customer = DB::table($table)->where('id', $customerId)->first();

                if ($customer) {
                    return $customer;
                }
            }
        }

        return null;
    }

    private function getRewardName(object $rule): string
    {
        $rewardType = $rule->reward_type ?? 'Reward';
        $rewardValue = $rule->reward_value ?? '';

        return trim($rewardType . ($rewardValue !== '' ? ' - ' . $rewardValue : ''));
    }
}