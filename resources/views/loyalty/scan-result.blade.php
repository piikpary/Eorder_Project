<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Loyalty Verification</title>

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f8fafc;
            color: #111827;
        }

        .page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .card {
            width: 100%;
            max-width: 430px;
            background: #ffffff;
            border-radius: 22px;
            box-shadow: 0 20px 60px rgba(15, 23, 42, 0.14);
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }

        .header {
            padding: 24px;
            text-align: center;
            color: #ffffff;
        }

        .header.valid {
            background: linear-gradient(135deg, #16a34a, #22c55e);
        }

        .header.invalid {
            background: linear-gradient(135deg, #dc2626, #ef4444);
        }

        .status-icon {
            width: 72px;
            height: 72px;
            border-radius: 999px;
            margin: 0 auto 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.22);
            font-size: 38px;
            font-weight: bold;
        }

        .title {
            font-size: 22px;
            font-weight: 800;
            margin: 0;
        }

        .subtitle {
            font-size: 14px;
            opacity: 0.92;
            margin-top: 6px;
        }

        .content {
            padding: 24px;
        }

        .row {
            display: flex;
            justify-content: space-between;
            gap: 14px;
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
            font-size: 14px;
        }

        .label {
            color: #64748b;
            font-weight: 600;
        }

        .value {
            text-align: right;
            font-weight: 700;
            color: #111827;
            word-break: break-word;
        }

        .progress-box {
            margin-top: 18px;
            border-radius: 18px;
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            padding: 16px;
        }

        .progress-title {
            font-weight: 800;
            margin-bottom: 10px;
            color: #4f46e5;
        }

        .stamp-line {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-top: 1px solid #e5e7eb;
        }

        .stamp-line:first-of-type {
            border-top: none;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 7px 12px;
            font-size: 13px;
            font-weight: 800;
        }

        .badge.waiting {
            background: #eef2ff;
            color: #4f46e5;
        }

        .badge.reward {
            background: #dcfce7;
            color: #15803d;
        }

        .warning {
            padding: 14px;
            background: #fff7ed;
            border: 1px solid #fed7aa;
            color: #9a3412;
            border-radius: 14px;
            font-size: 14px;
            font-weight: 600;
            line-height: 1.5;
        }

        .footer {
            padding: 0 24px 24px;
        }

        .button {
            width: 100%;
            display: block;
            text-align: center;
            padding: 13px 16px;
            border-radius: 14px;
            background: #7c3aed;
            color: #ffffff;
            text-decoration: none;
            font-weight: 800;
            box-sizing: border-box;
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="card">
            <div class="header {{ $valid ? 'valid' : 'invalid' }}">
                <div class="status-icon">
                    {{ $valid ? '✓' : '!' }}
                </div>

                <h1 class="title">
                    {{ $valid ? 'Real Customer Verified' : 'Invalid QR Code' }}
                </h1>

                <div class="subtitle">
                    {{ $message }}
                </div>
            </div>

            <div class="content">
                @if($valid && $customer)
                    <div class="row">
                        <div class="label">Customer Name</div>
                        <div class="value">{{ $customer->name ?? $customer->customer_name ?? '-' }}</div>
                    </div>

                    <div class="row">
                        <div class="label">Phone Number</div>
                        <div class="value">{{ $customer->phone ?? '-' }}</div>
                    </div>

         
                    <div class="row">
                        <div class="label">Restaurant</div>
                        <div class="value">{{ $restaurantName ?? '-' }}</div>
                    </div>

                    <div class="progress-box">
                        <div class="progress-title">Loyalty Status</div>

                        @forelse($progress as $item)
                            <div class="stamp-line">
                                <div>
                                    <div style="font-weight: 800;">{{ $item['reward'] ?? 'Reward' }}</div>
                                    <div style="font-size: 13px; color: #64748b;">
                                        {{ $item['current'] }} / {{ $item['required'] }} stamps
                                    </div>
                                </div>

                                @if($item['completed'])
                                    <span class="badge reward">Reward Available</span>
                                @else
                                    <span class="badge waiting">
                                        Need {{ $item['remaining'] }} more
                                    </span>
                                @endif
                            </div>
                        @empty
                            <div class="warning">
                                No active loyalty rule found for this customer.
                            </div>
                        @endforelse
                    </div>
                @else
                    <div class="warning">
                        This QR code is not valid in the system. Please do not accept it as a real loyalty card.
                    </div>
                @endif
            </div>

            <div class="footer">
                <a href="{{ url('/') }}" class="button">
                    Back to System
                </a>
            </div>
        </div>
    </div>
</body>
</html>