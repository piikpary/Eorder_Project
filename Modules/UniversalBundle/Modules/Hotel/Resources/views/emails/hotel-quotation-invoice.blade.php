@php
    $restaurant = $restaurant ?? $quotation->restaurant ?? $quotation->branch?->restaurant;
    $branch = $quotation->branch;
    $guest = $quotation->primaryGuest;

    $companyTitle = $restaurant?->name ?? config('app.name');
    if ($branch?->name && $branch->name !== ($restaurant?->name ?? '')) {
        $companyTitle .= ' (' . $branch->name . ')';
    }

    $companyAddress = $branch?->address ?? '';
    $companyPhone = $branch?->phone ?? '';
    $companyEmail = $branch?->email ?? '';
    $companyWebsite = data_get($restaurant, 'google_business_link');

    $df = function_exists('dateFormat') ? dateFormat() : 'd-m-Y';
    $tf = function_exists('timeFormat') ? timeFormat() : 'H:i';

    $arrivalAt = optional($quotation->check_in_date)?->format($df);
    if ($quotation->check_in_time) {
        $arrivalAt .= ' ' . \Carbon\Carbon::parse($quotation->check_in_time)->format($tf);
    }

    $departureAt = optional($quotation->check_out_date)?->format($df);
    if ($quotation->check_out_time) {
        $departureAt .= ' ' . \Carbon\Carbon::parse($quotation->check_out_time)->format($tf);
    }

    $nights = 0;
    if ($quotation->check_in_date && $quotation->check_out_date) {
        $nights = max(0, $quotation->check_in_date->diffInDays($quotation->check_out_date));
        if ($nights === 0) {
            $nights = 1;
        }
    }

    $statusLabel = $quotation->status?->label() ?? (string) $quotation->status;

    $roomsTotal = (float) $quotation->quotationRooms->sum('total_amount');
    $extrasTotal = (float) $quotation->quotationExtras->sum('total_amount');
    $grossSubtotal = $roomsTotal + $extrasTotal;
    $netAfterDiscount = (float) ($quotation->subtotal_before_tax ?? 0);
    $discountAmount = max(0, $grossSubtotal - $netAfterDiscount);

    $advancePaid = (float) ($quotation->advance_paid ?? 0);
    $balanceDue = max(0, (float) ($quotation->total_amount ?? 0) - $advancePaid);

    $headerBlue = '#3E7DA3';
    $borderBlack = '#000000';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('hotel::modules.quotation.emailInvoiceTitle') }}</title>
</head>
<body style="margin:0;padding:0;background-color:#ffffff;font-family:Georgia,'Times New Roman',Times,serif;font-size:14px;line-height:1.45;color:#111827;">
<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color:#ffffff;">
    <tr>
        <td align="center" style="padding:16px 12px;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="max-width:640px;border:1px solid {{ $borderBlack }};">

                {{-- Header --}}
                <tr>
                    <td style="padding:12px;border-bottom:1px solid {{ $borderBlack }};">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td valign="top" width="28%" style="padding:4px;">
                                    @if($restaurant && $restaurant->logo_url)
                                        <img src="{{ $restaurant->logo_url }}" alt="" width="120" style="display:block;max-width:120px;height:auto;border:0;">
                                    @endif
                                </td>
                                <td valign="top" width="44%" style="padding:4px 8px;">
                                    <div style="font-weight:700;font-size:15px;">{{ $companyTitle }}</div>
                                    @if($companyAddress)
                                        <div style="margin-top:6px;font-size:12px;">{{ $companyAddress }}</div>
                                    @endif
                                </td>
                                <td valign="top" width="28%" style="padding:4px;font-size:12px;text-align:right;">
                                    @if($companyPhone)
                                        <div>{{ __('hotel::modules.reservation.emailContactPhone') }}: {{ $companyPhone }}</div>
                                    @endif
                                    @if($companyEmail)
                                        <div style="margin-top:4px;">{{ __('hotel::modules.reservation.emailContactEmail') }}: {{ $companyEmail }}</div>
                                    @endif
                                    @if($companyWebsite)
                                        <div style="margin-top:4px;word-break:break-all;">{{ __('hotel::modules.reservation.emailContactWebsite') }}: {{ $companyWebsite }}</div>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                {{-- Overview --}}
                <tr>
                    <td style="padding:0;border-bottom:1px solid {{ $borderBlack }};">
                        <table role="presentation" width="100%" cellpadding="8" cellspacing="0" border="0" style="font-size:12px;">
                            <tr>
                                <td style="width:33%;border-right:1px solid {{ $borderBlack }};border-bottom:1px solid {{ $borderBlack }};vertical-align:top;">
                                    <strong>{{ __('hotel::modules.quotation.emailQuotationId') }}</strong><br>{{ $quotation->quotation_number }}
                                </td>
                                <td style="width:33%;border-right:1px solid {{ $borderBlack }};border-bottom:1px solid {{ $borderBlack }};vertical-align:top;">
                                    <strong>{{ __('hotel::modules.reservation.emailArrivalDateTime') }}</strong><br>{{ $arrivalAt ?: '—' }}
                                </td>
                                <td style="width:33%;border-bottom:1px solid {{ $borderBlack }};vertical-align:top;">
                                    <strong>{{ __('hotel::modules.reservation.emailDepartureDateTime') }}</strong><br>{{ $departureAt ?: '—' }}
                                </td>
                            </tr>
                            <tr>
                                <td style="border-right:1px solid {{ $borderBlack }};vertical-align:top;">
                                    <strong>{{ __('hotel::modules.reservation.emailNights') }}</strong><br>{{ $nights }} {{ $nights === 1 ? __('hotel::modules.reservation.emailNightSingular') : __('hotel::modules.reservation.emailNightPlural') }}
                                </td>
                                <td style="border-right:1px solid {{ $borderBlack }};vertical-align:top;">
                                    <strong>{{ __('hotel::modules.quotation.status') }}</strong><br>{{ $statusLabel }}
                                </td>
                                <td style="vertical-align:top;">
                                    <strong>{{ __('hotel::modules.quotation.date') }}</strong><br>{{ optional($quotation->created_at)->format($df) ?? '—' }}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                {{-- Customer --}}
                <tr>
                    <td style="padding:12px;border-bottom:1px solid {{ $borderBlack }};">
                        <div style="font-weight:700;margin-bottom:8px;">{{ __('hotel::modules.reservation.emailCustomerSection') }}</div>
                        <table role="presentation" width="100%" cellpadding="4" cellspacing="0" border="0" style="font-size:13px;">
                            <tr>
                                <td style="width:140px;"><strong>{{ __('hotel::modules.reservation.emailCustomerName') }}</strong></td>
                                <td>{{ $guest?->full_name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td><strong>{{ __('hotel::modules.reservation.emailCustomerAddress') }}</strong></td>
                                <td>{{ $guest?->address ? $guest->address : '—' }}</td>
                            </tr>
                            <tr>
                                <td><strong>{{ __('hotel::modules.reservation.emailMobile') }}</strong></td>
                                <td>{{ $guest?->phone ?? '—' }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>

                {{-- Rooms --}}
                <tr>
                    <td style="padding:0;border-bottom:1px solid {{ $borderBlack }};">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td style="background-color:{{ $headerBlue }};color:#ffffff;font-weight:700;padding:8px 10px;font-size:13px;">
                                    {{ __('hotel::modules.reservation.emailRoomsSection') }}
                                </td>
                            </tr>
                        </table>
                        <table role="presentation" width="100%" cellpadding="8" cellspacing="0" border="0" style="font-size:12px;border-collapse:collapse;">
                            <thead>
                                <tr>
                                    <th align="left" style="border:1px solid {{ $borderBlack }};padding:6px;">{{ __('hotel::modules.reservation.roomType') }}</th>
                                    <th align="center" style="border:1px solid {{ $borderBlack }};padding:6px;">{{ __('hotel::modules.reservation.quantityShort') }}</th>
                                    <th align="right" style="border:1px solid {{ $borderBlack }};padding:6px;">{{ __('hotel::modules.reservation.rate') }}</th>
                                    <th align="right" style="border:1px solid {{ $borderBlack }};padding:6px;">{{ __('hotel::modules.reservation.total') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($quotation->quotationRooms as $qr)
                                    <tr>
                                        <td style="border:1px solid {{ $borderBlack }};padding:6px;">{{ $qr->roomType?->name ?? '—' }}</td>
                                        <td align="center" style="border:1px solid {{ $borderBlack }};padding:6px;">{{ $qr->quantity ?? 0 }}</td>
                                        <td align="right" style="border:1px solid {{ $borderBlack }};padding:6px;">{{ currency_format($qr->rate ?? 0) }}</td>
                                        <td align="right" style="border:1px solid {{ $borderBlack }};padding:6px;">{{ currency_format($qr->total_amount ?? 0) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" style="border:1px solid {{ $borderBlack }};padding:8px;">{{ __('hotel::modules.reservation.noRoomsSelected') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </td>
                </tr>

                {{-- Extras --}}
                <tr>
                    <td style="padding:0;border-bottom:1px solid {{ $borderBlack }};">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td style="background-color:{{ $headerBlue }};color:#ffffff;font-weight:700;padding:8px 10px;font-size:13px;">
                                    {{ __('hotel::modules.reservation.emailExtrasSection') }}
                                </td>
                            </tr>
                        </table>
                        <table role="presentation" width="100%" cellpadding="8" cellspacing="0" border="0" style="font-size:12px;">
                            @forelse($quotation->quotationExtras as $qe)
                                <tr>
                                    <td style="border:1px solid {{ $borderBlack }};padding:8px;">
                                        {{ $qe->extraService?->name ?? '—' }}
                                        @if(($qe->quantity ?? 0) > 1)
                                            / {{ __('hotel::modules.reservation.quantityShort') }} {{ $qe->quantity }}
                                        @endif
                                        / {{ currency_format($qe->total_amount ?? 0) }}
                                        @if($qe->unit_price)
                                            — {{ __('hotel::modules.reservation.unitPrice') }} {{ currency_format($qe->unit_price) }}
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td style="border:1px solid {{ $borderBlack }};padding:8px;">—</td>
                                </tr>
                            @endforelse
                        </table>
                    </td>
                </tr>

                {{-- Financial summary --}}
                <tr>
                    <td style="padding:0;border-bottom:1px solid {{ $borderBlack }};">
                        <table role="presentation" width="100%" cellpadding="8" cellspacing="0" border="0" style="font-size:12px;">
                            <tr>
                                <td style="width:33%;border-right:1px solid {{ $borderBlack }};border-bottom:1px solid {{ $borderBlack }};">
                                    <strong>{{ __('hotel::modules.reservation.roomsPrice') }}</strong><br>{{ currency_format($roomsTotal) }}
                                </td>
                                <td style="width:33%;border-right:1px solid {{ $borderBlack }};border-bottom:1px solid {{ $borderBlack }};">
                                    <strong>{{ __('hotel::modules.reservation.extrasPrice') }}</strong><br>{{ currency_format($extrasTotal) }}
                                </td>
                                <td style="width:33%;border-bottom:1px solid {{ $borderBlack }};">
                                    <strong>{{ __('hotel::modules.reservation.discount') }}</strong><br>{{ currency_format($discountAmount) }}
                                </td>
                            </tr>
                            <tr>
                                <td style="border-right:1px solid {{ $borderBlack }};border-bottom:1px solid {{ $borderBlack }};vertical-align:top;">
                                    @php $invoiceTaxLines = $quotation->invoiceTaxes(); @endphp
                                    @forelse($invoiceTaxLines as $line)
                                        <div style="margin-bottom:8px;">
                                            <strong>{{ $line['tax']->name ?? __('hotel::modules.reservation.bookingTax') }}</strong>
                                            @if($line['tax']->rate !== null && (float) $line['tax']->rate != 0)
                                                ({{ $line['tax']->rate }}%)
                                            @endif
                                            <br>{{ currency_format($line['amount']) }}
                                        </div>
                                    @empty
                                        <strong>{{ __('hotel::modules.reservation.bookingTax') }}</strong><br>{{ currency_format($quotation->tax_amount ?? 0) }}
                                    @endforelse
                                </td>
                                <td style="border-right:1px solid {{ $borderBlack }};border-bottom:1px solid {{ $borderBlack }};">
                                    <strong>{{ __('hotel::modules.reservation.total') }}</strong><br>{{ currency_format($quotation->total_amount ?? 0) }}
                                </td>
                                <td style="border-bottom:1px solid {{ $borderBlack }};">
                                    <strong>{{ __('hotel::modules.reservation.advancePaid') }}</strong><br>{{ currency_format($advancePaid) }}
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3">
                                    <strong>{{ __('hotel::modules.reservation.balanceDue') }}</strong><br>{{ currency_format($balanceDue) }}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                {{-- Footer --}}
                <tr>
                    <td style="padding:8px 12px 16px;font-size:12px;color:#6b7280;border-top:1px solid {{ $borderBlack }};">
                        {{ __('hotel::modules.quotation.emailInvoiceFooter') }}
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>

