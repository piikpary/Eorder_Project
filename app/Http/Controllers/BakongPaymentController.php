<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\BakongKhqrService;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BakongPaymentController extends Controller
{
    public function testConfig()
    {
        $token = config('services.bakong.token');

        return response()->json([
            'base_url' => config('services.bakong.base_url'),
            'token_loaded' => !empty($token),
            'token_preview' => $token ? substr($token, 0, 20) . '...' : null,
            'khqr' => [
                'account_id' => config('services.bakong_khqr.account_id'),
                'merchant_name' => config('services.bakong_khqr.merchant_name'),
                'city' => config('services.bakong_khqr.city'),
                'currency' => config('services.bakong_khqr.currency'),
                'acquiring_bank' => config('services.bakong_khqr.acquiring_bank'),
                'mobile_number' => config('services.bakong_khqr.mobile_number'),
                'merchant_id' => config('services.bakong_khqr.merchant_id'),
            ],
        ]);
    }

    public function testQr($amount = 1.05, BakongKhqrService $khqr)
    {
        $amount = (float) $amount;

        if ($amount <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Amount must be greater than 0.',
            ], 422);
        }

        try {
            $billNumber = 'TEST-' . now()->format('YmdHis') . '-' . random_int(100, 999);

            $result = $khqr->generate($amount, $billNumber);

            $qrSvg = $this->makeQrSvg($result['payload']);

            return view('payments.bakong-khqr', [
                'amount' => $amount,
                'currency' => strtoupper(config('services.bakong_khqr.currency', 'USD')),
                'billNumber' => $billNumber,
                'qrSvg' => $qrSvg,
                'payload' => $result['payload'],
                'md5' => $result['md5'],
                'checkUrl' => url('/test-bakong-check/' . $result['md5']),
            ]);
        } catch (\Throwable $e) {
            Log::error('Bakong QR test failed: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    }

    public function testCheck(?string $md5 = null, BakongKhqrService $khqr)
    {
        if (empty($md5)) {
            return response()->json([
                'success' => false,
                'message' => 'MD5 is required.',
            ], 422);
        }

        $result = $khqr->checkPaymentByMd5($md5);

        return response()->json([
            'success' => true,
            'md5' => $md5,
            'result' => $result,
        ]);
    }

    public function showOrderPayment(Order $order, BakongKhqrService $khqr)
    {
        if ($order->payment_status === 'paid') {
            return back()->with('success', 'Order already paid.');
        }

        $amount = (float) ($order->total_amount ?? $order->total ?? $order->amount ?? 0);

        if ($amount <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Order amount is invalid.',
            ], 422);
        }

        $billNumber = 'ORDER-' . $order->id . '-' . now()->format('YmdHis') . '-' . random_int(100, 999);

        $result = $khqr->generate($amount, $billNumber);

        $payment = $order->payments()
            ->where('payment_method', 'khqr')
            ->latest()
            ->first();

        if (!$payment) {
            $payment = $order->payments()->create([
                'amount' => $amount,
                'payment_method' => 'khqr',
                'transaction_id' => $billNumber,
                'khqr_payload' => $result['payload'],
                'khqr_md5' => $result['md5'],
            ]);
        } else {
            $payment->update([
                'amount' => $amount,
                'payment_method' => 'khqr',
                'transaction_id' => $billNumber,
                'khqr_payload' => $result['payload'],
                'khqr_md5' => $result['md5'],
            ]);
        }

        $qrSvg = $this->makeQrSvg($result['payload']);

        return view('payments.bakong-khqr', [
            'amount' => $amount,
            'currency' => strtoupper(config('services.bakong_khqr.currency', 'USD')),
            'billNumber' => $billNumber,
            'qrSvg' => $qrSvg,
            'payload' => $result['payload'],
            'md5' => $result['md5'],
            'checkUrl' => route('bakong.order.check', $order),
        ]);
    }

    public function checkOrderPayment(Order $order, BakongKhqrService $khqr)
    {
        $payment = $order->payments()
            ->where('payment_method', 'khqr')
            ->whereNotNull('khqr_md5')
            ->latest()
            ->first();

        if (!$payment) {
            return response()->json([
                'success' => false,
                'status' => 'waiting',
                'message' => 'No KHQR payment found.',
            ], 422);
        }

        $result = $khqr->checkPaymentByMd5($payment->khqr_md5);

        if (($result['status'] ?? null) !== 'paid') {
            return response()->json([
                'success' => true,
                'status' => 'waiting',
                'message' => $result['message'] ?? 'Waiting for payment.',
                'result' => $result,
            ]);
        }

        $payment->update([
            'payment_status' => 'paid',
        ]);

        $order->update([
            'payment_status' => 'paid',
            'status' => 'paid',
        ]);

        return response()->json([
            'success' => true,
            'status' => 'paid',
            'message' => 'Payment received successfully.',
            'result' => $result,
        ]);
    }

    private function makeQrSvg(string $payload): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle(280),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);

        return $writer->writeString($payload);
    }
}