<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramLoyaltyWebhookController extends Controller
{
    public function handle(Request $request)
    {
        try {
            Log::info('TELEGRAM_LOYALTY_WEBHOOK_RECEIVED', [
                'payload' => $request->all(),
            ]);

            $message = $request->input('message');

            if (!$message) {
                return response()->json(['ok' => true]);
            }

            $text = $message['text'] ?? '';
            $chat = $message['chat'] ?? [];
            $from = $message['from'] ?? [];

            if (!str_starts_with($text, '/start')) {
                return response()->json(['ok' => true]);
            }

            $parts = explode(' ', $text);
            $loyaltyToken = trim($parts[1] ?? '');

            if (!$loyaltyToken) {
                $this->sendTelegramMessage($chat['id'] ?? null, 'Please open Telegram from your loyalty page.');
                return response()->json(['ok' => true]);
            }

            $customer = DB::table('customers')
                ->where('loyalty_token', $loyaltyToken)
                ->first();

            if (!$customer) {
                $this->sendTelegramMessage($chat['id'] ?? null, 'Invalid loyalty account.');
                return response()->json(['ok' => true]);
            }

            DB::table('customers')
                ->where('id', $customer->id)
                ->update([
                    'telegram_chat_id' => $chat['id'] ?? null,
                    'telegram_user_id' => $from['id'] ?? null,
                    'telegram_username' => $from['username'] ?? null,
                    'telegram_connected_at' => now(),
                    'telegram_notify_enabled' => 1,
                    'updated_at' => now(),
                ]);

            $this->sendTelegramMessage(
                $chat['id'] ?? null,
                "? Telegram connected successfully!\n\nHello " . ($customer->name ?? 'Customer') . ", you will receive reward alerts here."
            );

            return response()->json(['ok' => true]);
        } catch (\Throwable $e) {
            Log::error('Telegram loyalty webhook error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json(['ok' => true]);
        }
    }

    private function sendTelegramMessage($chatId, string $message): void
    {
        $token = config('services.telegram_loyalty.bot_token');

        if (!$token || !$chatId) {
            return;
        }

        Http::timeout(10)->post("https://api.telegram.org/bot{$token}/sendMessage", [
            'chat_id' => $chatId,
            'text' => $message,
        ]);
    }
}