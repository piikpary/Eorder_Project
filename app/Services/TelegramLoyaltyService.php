<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramLoyaltyService
{
    public function sendToCustomer($customerId, string $message): bool
    {
        try {
            $customer = DB::table('customers')
                ->where('id', $customerId)
                ->first();

            if (!$customer) {
                return false;
            }

            if (empty($customer->telegram_chat_id)) {
                return false;
            }

            if ((int) ($customer->telegram_notify_enabled ?? 0) !== 1) {
                return false;
            }

            return $this->sendMessage($customer->telegram_chat_id, $message);
        } catch (\Throwable $e) {
            Log::error('Telegram loyalty customer alert error: ' . $e->getMessage(), [
                'customer_id' => $customerId,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return false;
        }
    }

    public function sendMessage($chatId, string $message): bool
    {
        $token = config('services.telegram_loyalty.bot_token')
            ?: env('TELEGRAM_LOYALTY_BOT_TOKEN');

        if (empty($token)) {
            Log::warning('Telegram loyalty bot token missing.');
            return false;
        }

        try {
            $response = Http::timeout(10)->post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML',
            ]);

            if (!$response->successful()) {
                Log::warning('Telegram loyalty send failed', [
                    'chat_id' => $chatId,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('Telegram loyalty send exception: ' . $e->getMessage(), [
                'chat_id' => $chatId,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return false;
        }
    }
}