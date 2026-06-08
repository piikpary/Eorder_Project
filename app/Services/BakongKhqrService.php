<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use KHQR\BakongKHQR;
use KHQR\Helpers\KHQRData;
use KHQR\Models\IndividualInfo;
use RuntimeException;

class BakongKhqrService
{
    public function generate(float $amount, ?string $billNumber = null): array
    {
        $accountId = trim((string) config('services.bakong.account_id'));

        if ($accountId === '') {
            throw new RuntimeException('BAKONG_ACCOUNT_ID is not configured.');
        }

        if ($amount <= 0) {
            throw new RuntimeException('KHQR amount must be greater than 0.');
        }

        $currency = strtoupper((string) config('services.bakong.currency', 'USD'));

        if (!in_array($currency, ['USD', 'KHR'], true)) {
            throw new RuntimeException('BAKONG currency must be USD or KHR.');
        }

        $reference = $billNumber ?: 'INV' . now()->format('YmdHis') . random_int(100, 999);

        $reference = substr(
            preg_replace('/[^A-Z0-9]/', '', strtoupper($reference)),
            0,
            20
        );

        $lifetimeMinutes = (int) config('services.bakong.qr_lifetime_minutes', 1440);

        $expirationTimestamp = (string) floor(
            (microtime(true) + ($lifetimeMinutes * 60)) * 1000
        );

        $payment = new IndividualInfo(
            bakongAccountID: $accountId,
            merchantName: (string) config('services.bakong.merchant_name', 'VANNY MEAS'),
            merchantCity: (string) config('services.bakong.merchant_city', 'PHNOM PENH'),
            acquiringBank: config('services.bakong.acquiring_bank'),
            currency: $currency === 'KHR' ? KHQRData::CURRENCY_KHR : KHQRData::CURRENCY_USD,
            amount: $currency === 'KHR'
                ? (int) round($amount)
                : (float) number_format($amount, 2, '.', ''),
            billNumber: $reference,
            mobileNumber: config('services.bakong.mobile_number'),
            purposeOfTransaction: 'POS payment',
            expirationTimestamp: $expirationTimestamp,
            merchantCategoryCode: '5999'
        );

        $result = BakongKHQR::generateIndividual($payment);

        if (
            ($result->status['code'] ?? 1) !== 0 ||
            empty($result->data['qr']) ||
            empty($result->data['md5'])
        ) {
            Log::error('Bakong KHQR generate failed', [
                'result' => $result,
            ]);

            throw new RuntimeException($result->status['message'] ?? 'Could not generate KHQR.');
        }

        return [
            'payload' => $result->data['qr'],
            'qr' => $result->data['qr'],
            'md5' => $result->data['md5'],
            'reference' => $reference,
            'amount' => $amount,
            'currency' => $currency,
            'generated_at' => now()->toDateTimeString(),
            'expires_at' => now()->addMinutes($lifetimeMinutes)->toDateTimeString(),
        ];
    }

    public function checkPaymentByMd5(string $md5): array
    {
        $token = (string) config('services.bakong.token');

        if ($token === '') {
            return [
                'status' => 'no_token',
                'paid' => false,
                'message' => 'BAKONG_API_TOKEN is not configured.',
            ];
        }

        try {
            $bakong = new BakongKHQR($token);

            $response = $bakong->checkTransactionByMD5(
                $md5,
                (bool) config('services.bakong.test_mode', false)
            );

            $paid = ($response['responseCode'] ?? null) === 0 && !empty($response['data']);

            return [
                'status' => $paid ? 'paid' : 'waiting',
                'paid' => $paid,
                'message' => $response['responseMessage'] ?? 'Waiting for payment.',
                'transaction' => $paid ? $response['data'] : null,
                'response' => $response,
            ];
        } catch (\Throwable $e) {
            Log::error('Bakong KHQR payment check error: ' . $e->getMessage(), [
                'md5' => $md5,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return [
                'status' => 'error',
                'paid' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}