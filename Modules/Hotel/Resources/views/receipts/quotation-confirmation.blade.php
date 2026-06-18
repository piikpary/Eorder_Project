<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Quotation Confirmation</title>
    <style>
        body { font-family: DejaVu Sans, DejaVuSans, sans-serif; font-size: 12px; color: #111827; }
        .container { padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; }
        .logo { max-height: 48px; max-width: 160px; height: auto; width: auto; margin-bottom: 6px; }
        .title { font-size: 18px; font-weight: 700; }
        .muted { color: #6b7280; }
        .box { border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px; margin-top: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px 6px; border-bottom: 1px solid #e5e7eb; }
        th { background: #f3f4f6; text-align: left; font-weight: 700; }
        .right { text-align: right; }
        .small { font-size: 11px; }
        .totals td { border-bottom: none; padding: 6px; }
        .totals .label { color: #374151; }
        .totals .value { text-align: right; font-weight: 700; }
        .line { height: 1px; background: #e5e7eb; margin: 10px 0; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 999px; font-size: 11px; font-weight: 700; border: 1px solid #e5e7eb; }
        .mt-8 { margin-top: 8px; }
        .mt-12 { margin-top: 12px; }
        .cta { display: inline-block; padding: 10px 14px; border-radius: 10px; background: #2563eb; color: #fff; text-decoration: none; font-weight: 700; }
        @page { margin: 14px 10px; }
    </style>
</head>
<body>
    <div class="container">
        @php
            $restaurant = $quotation->restaurant ?? (function_exists('restaurant') ? restaurant() : null);
            $branch = $quotation->branch ?? (function_exists('branch') ? branch() : null);
            $statusLabel = optional($quotation->status)->label() ?? (string) ($quotation->status->value ?? $quotation->status);

            // DomPDF is most reliable with local paths or embedded data URIs.
            $logoUrl = $restaurant?->logo_url ?? $restaurant?->logoUrl ?? null;
            $logoSrc = $logoUrl;

            if (!blank($logoUrl)) {
                $parsedPath = parse_url($logoUrl, PHP_URL_PATH);
                $localPath = $parsedPath ? public_path(ltrim($parsedPath, '/')) : null;

                // Fallback to legacy upload location when we have filename only.
                if ((!$localPath || !is_file($localPath)) && !empty($restaurant?->logo)) {
                    $legacyPath = public_path('user-uploads/logo/' . $restaurant->logo);
                    $localPath = is_file($legacyPath) ? $legacyPath : $localPath;
                }

                if ($localPath && is_file($localPath)) {
                    $ext = strtolower(pathinfo($localPath, PATHINFO_EXTENSION));
                    $mime = match ($ext) {
                        'jpg', 'jpeg' => 'image/jpeg',
                        'png' => 'image/png',
                        'gif' => 'image/gif',
                        'webp' => 'image/webp',
                        default => null,
                    };

                    if ($mime) {
                        $logoSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($localPath));
                    }
                }
            }
        @endphp

        <div class="header">
            <div>
                @if(!blank($logoSrc))
                    <img src="{{ $logoSrc }}" alt="{{ $restaurant?->name ?? 'Logo' }}" class="logo">
                @endif
                <div class="title">{{ $restaurant?->name ?? 'Hotel' }}</div>
                <div class="muted small mt-8">
                    @if($branch?->name) {{ $branch->name }} @endif
                </div>
                <div class="muted small mt-8">
                    Quotation: <strong>{{ $quotation->quotation_number }}</strong>
                </div>
                <div class="muted small mt-8">
                    Status: <span class="badge">{{ $statusLabel }}</span>
                </div>
            </div>

            <div class="small right">
                <div class="muted">Date</div>
                <div>{{ optional($quotation->check_in_date)->format('d M Y') }}</div>
                <div class="muted mt-8">Guest</div>
                <div>{{ $quotation->primaryGuest?->full_name ?? '' }}</div>
            </div>
        </div>

        <div class="box mt-12">
            <div class="small muted">Stay</div>
            <div class="mt-8">
                Check-in:
                <strong>{{ optional($quotation->check_in_date)->format('D, M d, Y') }}</strong>
                @if($quotation->check_in_time) · <strong>{{ \Carbon\Carbon::parse($quotation->check_in_time)->format('H:i') }}</strong> @endif
            </div>
            <div class="mt-8">
                Check-out:
                <strong>{{ optional($quotation->check_out_date)->format('D, M d, Y') }}</strong>
                @if($quotation->check_out_time) · <strong>{{ \Carbon\Carbon::parse($quotation->check_out_time)->format('H:i') }}</strong> @endif
            </div>
        </div>

        <div class="box mt-12">
            <div class="small muted">Rooms</div>
            <div class="mt-8">
                <table>
                    <thead>
                        <tr>
                            <th>Room Type</th>
                            <th class="right">Qty</th>
                            <th class="right">Rate</th>
                            <th class="right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($quotation->quotationRooms as $qr)
                            <tr>
                                <td>{{ $qr->roomType?->name ?? '-' }}</td>
                                <td class="right">{{ $qr->quantity ?? 0 }}</td>
                                <td class="right">{{ currency_format($qr->rate ?? 0) }}</td>
                                <td class="right">{{ currency_format($qr->total_amount ?? 0) }}</td>
                            </tr>
                        @endforeach
                        @if($quotation->quotationRooms->isEmpty())
                            <tr>
                                <td colspan="4" class="muted small">No rooms selected</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        @if($quotation->quotationExtras->count())
            <div class="box mt-12">
                <div class="small muted">Extras</div>
                <div class="mt-8">
                    <table>
                        <thead>
                            <tr>
                                <th>Extra</th>
                                <th class="right">Qty</th>
                                <th class="right">Unit</th>
                                <th class="right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($quotation->quotationExtras as $qe)
                                <tr>
                                    <td>{{ $qe->extraService?->name ?? '-' }}</td>
                                    <td class="right">{{ $qe->quantity ?? 0 }}</td>
                                    <td class="right">{{ currency_format($qe->unit_price ?? 0) }}</td>
                                    <td class="right">{{ currency_format($qe->total_amount ?? 0) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <div class="box mt-12">
            <div class="small muted">Summary</div>
            <div class="line"></div>
            <table class="totals">
                <tr>
                    <td class="label">Rooms &amp; Extras</td>
                    <td class="value">{{ currency_format($grossSubtotal) }}</td>
                </tr>
                <tr>
                    <td class="label">
                        Discount
                        @if(!empty($quotation->discount_type) && ($quotation->discount_value ?? 0) > 0)
                            <span class="muted small">({{ $quotation->discount_type }}: {{ $quotation->discount_value }})</span>
                        @endif
                    </td>
                    <td class="value">- {{ currency_format($discountAmount) }}</td>
                </tr>
                <tr>
                    <td class="label">Amount after discount</td>
                    <td class="value">{{ currency_format($netAfterDiscount) }}</td>
                </tr>
                @php $receiptTaxLines = $quotation->invoiceTaxes(); @endphp
                @forelse($receiptTaxLines as $line)
                <tr>
                    <td class="label">
                        {{ $line['tax']->name ?? __('hotel::modules.reservation.bookingTax') }}
                        @if($line['tax']->rate !== null && (float) $line['tax']->rate != 0)
                            <span class="muted small">({{ $line['tax']->rate }}%)</span>
                        @endif
                    </td>
                    <td class="value">{{ currency_format($line['amount']) }}</td>
                </tr>
                @empty
                <tr>
                    <td class="label">{{ __('hotel::modules.reservation.bookingTax') }}</td>
                    <td class="value">{{ currency_format($quotation->tax_amount ?? 0) }}</td>
                </tr>
                @endforelse
                <tr>
                    <td class="label">Total</td>
                    <td class="value">{{ currency_format($quotation->total_amount ?? 0) }}</td>
                </tr>
                <tr>
                    <td class="label">Advance paid</td>
                    <td class="value">{{ currency_format($advancePaid) }}</td>
                </tr>
                <tr>
                    <td class="label">Balance due</td>
                    <td class="value">{{ currency_format($balanceDue) }}</td>
                </tr>
            </table>
        </div>

        <div class="muted small mt-12">
            Generated on {{ now()->format('d M Y H:i') }}
        </div>
    </div>
</body>
</html>

