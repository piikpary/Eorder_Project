<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $agreement->type->label() }} — {{ $agreement->agreement_number }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 13px;
            color: #1a202c;
            background: #fff;
            padding: 0;
        }

        .page {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 48px;
        }

        /* ── Header ── */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 3px solid #2d3748;
            padding-bottom: 20px;
            margin-bottom: 24px;
        }

        .company-info h1 {
            font-size: 20px;
            font-weight: 700;
            color: #1a202c;
            letter-spacing: 0.5px;
        }

        .company-info p {
            font-size: 11px;
            color: #4a5568;
            margin-top: 3px;
            line-height: 1.5;
        }

        .logo-block {
            text-align: right;
        }

        .logo-block img {
            max-height: 70px;
            max-width: 160px;
            object-fit: contain;
        }

        /* ── Agreement title ── */
        .agreement-title {
            text-align: center;
            margin-bottom: 20px;
        }

        .agreement-title h2 {
            font-size: 18px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #2d3748;
        }

        .agreement-title .badge {
            display: inline-block;
            margin-top: 6px;
            background: #ebf8ff;
            color: #2b6cb0;
            border: 1px solid #bee3f8;
            border-radius: 4px;
            padding: 2px 10px;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        /* ── Meta table ── */
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
            background: #f7fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            overflow: hidden;
        }

        .meta-table td {
            padding: 7px 14px;
            font-size: 12px;
            border-bottom: 1px solid #e2e8f0;
        }

        .meta-table td:first-child {
            font-weight: 600;
            color: #4a5568;
            width: 40%;
        }

        .meta-table tr:last-child td {
            border-bottom: none;
        }

        /* ── Content area ── */
        .content-area {
            line-height: 1.8;
            font-size: 13px;
            color: #2d3748;
            white-space: pre-wrap;
            word-break: break-word;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 24px 28px;
            background: #fafafa;
            margin-bottom: 32px;
        }

        /* ── Footer ── */
        .footer {
            border-top: 1px solid #e2e8f0;
            padding-top: 12px;
            font-size: 10px;
            color: #a0aec0;
            text-align: center;
        }

        /* ── Print actions ── */
        .print-actions {
            text-align: center;
            margin-bottom: 30px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 20px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            text-decoration: none;
        }

        .btn-primary {
            background: #3182ce;
            color: #fff;
        }

        .btn-secondary {
            background: #edf2f7;
            color: #4a5568;
            margin-left: 8px;
        }

        @media print {
            .print-actions { display: none !important; }
            body { padding: 0; }
            .page { padding: 24px 32px; }
        }
    </style>
</head>
<body>
<div class="page">

    {{-- Print / Close Buttons --}}
    <div class="print-actions">
        <button class="btn btn-primary" onclick="window.print()">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            Print Agreement
        </button>
        <a href="{{ route('hotel.agreements.index') }}" class="btn btn-secondary">
            ← Back to Agreements
        </a>
    </div>

    {{-- Header --}}
    <div class="header">
        <div class="company-info">
            @php
                $restaurant = $agreement->reservation->restaurant ?? null;
                $branch     = $agreement->reservation->branch ?? null;
            @endphp
            <h1>{{ $restaurant?->restaurant_name ?? $restaurant?->name ?? config('app.name') }}</h1>
            @if($branch?->address || $restaurant?->address)
            <p>{{ $branch?->address ?? $restaurant?->address }}</p>
            @endif
            @if($restaurant?->company_phone || $restaurant?->phone)
            <p>Contact: {{ $restaurant?->company_phone ?? $restaurant?->phone }}</p>
            @endif
            @if($restaurant?->company_email || $restaurant?->email)
            <p>Email: {{ $restaurant?->company_email ?? $restaurant?->email }}</p>
            @endif
        </div>
        <div class="logo-block">
            @if($restaurant?->logo)
            <img src="{{ asset_url_local_s3('restaurant-logo/' . $restaurant->logo) }}" alt="Logo">
            @endif
        </div>
    </div>

    {{-- Agreement Title --}}
    <div class="agreement-title">
        <h2>{{ $agreement->type->label() }}</h2>
        <span class="badge">{{ $agreement->agreement_number }}</span>
    </div>

    {{-- Meta Info --}}
    <table class="meta-table">
        <tr>
            <td>Agreement Number</td>
            <td>{{ $agreement->agreement_number }}</td>
        </tr>
        <tr>
            <td>Agreement Date</td>
            <td>{{ $agreement->agreement_date->format('d M Y') }}</td>
        </tr>
        <tr>
            <td>Booking / Reservation #</td>
            <td>{{ $agreement->reservation->reservation_number ?? '—' }}</td>
        </tr>
        <tr>
            <td>Guest / Tenant</td>
            <td>{{ $agreement->reservation->primaryGuest?->full_name ?? '—' }}</td>
        </tr>
        <tr>
            <td>Check-in Date</td>
            <td>{{ $agreement->reservation->check_in_date?->format('d M Y') ?? '—' }}</td>
        </tr>
        <tr>
            <td>Check-out Date</td>
            <td>{{ $agreement->reservation->check_out_date?->format('d M Y') ?? '—' }}</td>
        </tr>
        <tr>
            <td>Total Amount</td>
            <td>{{ currency_format($agreement->reservation->total_amount ?? 0) }}</td>
        </tr>
        <tr>
            <td>Advance Paid</td>
            <td>{{ currency_format($agreement->reservation->advance_paid ?? 0) }}</td>
        </tr>
        <tr>
            <td>Security Deposit</td>
            <td>{{ currency_format($agreement->reservation->security_deposit ?? 0) }}</td>
        </tr>
        @if($agreement->notes)
        <tr>
            <td>Notes</td>
            <td>{{ $agreement->notes }}</td>
        </tr>
        @endif
        <tr>
            <td>Agreement Type</td>
            <td>{{ $agreement->type->label() }}</td>
        </tr>
        <tr>
            <td>Prepared by</td>
            <td>{{ $agreement->createdBy?->name ?? '—' }}</td>
        </tr>
    </table>

    {{-- Agreement Content --}}
    <div class="content-area">{{ $agreement->content }}</div>

    {{-- Footer --}}
    <div class="footer">
        Generated on {{ $agreement->created_at->format('d M Y, h:i A') }} &nbsp;·&nbsp; {{ config('app.name') }}
    </div>

</div>
</body>
</html>
