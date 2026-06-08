<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LoyaltyTelegramService
{
    public function sendRewardReached(array $data): void
    {
        $token = config('services.telegram_loyalty.bot_token');
        $chatId = config('services.telegram_loyalty.chat_id');

        if (!$token || !$chatId) {
            Log::warning('Loyalty Telegram config missing.');
            return;
        }

        $message =
            "🎉 Loyalty Reward Reached\n\n" .
            "Customer: " . ($data['customer_name'] ?? '-') . "\n" .
            "Phone: " . ($data['customer_phone'] ?? '-') . "\n" .
            "Reward: " . ($data['reward_name'] ?? '-') . "\n" .
            "Progress: " . ($data['current_stamps'] ?? 0) . " / " . ($data['required_stamps'] ?? 0) . " stamps\n" .
            "Order: " . ($data['order_no'] ?? '-') . "\n\n" .
            "Please prepare reward for customer.";

        try {
            Http::timeout(10)->post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
            ]);
        } catch (\Throwable $e) {
            Log::error('Loyalty Telegram alert failed: ' . $e->getMessage());
        }
    }
}