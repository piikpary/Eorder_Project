@php
    $restaurant = $restaurant ?? $reservation->restaurant ?? $reservation->branch?->restaurant;
    $branch = $reservation->branch;
    $guest = $reservation->primaryGuest;

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

    $arrivalAt = optional($reservation->check_in_date)?->format($df);
    if ($reservation->check_in_time) {
        $arrivalAt .= ' ' . \Carbon\Carbon::parse($reservation->check_in_time)->format($tf);
    }

    $departureAt = optional($reservation->check_out_date)?->format($df);
    if ($reservation->check_out_time) {
        $departureAt .= ' ' . \Carbon\Carbon::parse($reservation->check_out_time)->format($tf);
    }

    $nights = 0;
    if ($reservation->check_in_date && $reservation->check_out_date) {
        $nights = max(0, $reservation->check_in_date->diffInDays($reservation->check_out_date));
        if ($nights === 0) {
            $nights = 1;
        }
    }

    $actualCheckIn = $reservation->stays->first()?->check_in_at;
    $actualCheckInStr = $actualCheckIn ? $actualCheckIn->format($df . ' ' . $tf) : '—';

    $statusLabel = $reservation->status?->label() ?? (string) $reservation->status;

    $roomsTotal = (float) $reservation->reservationRooms->sum('total_amount');
    $extrasTotal = (float) $reservation->reservationExtras->sum('total_amount');
    $grossSubtotal = $roomsTotal + $extrasTotal;
    $netAfterDiscount = (float) ($reservation->subtotal_before_tax ?? 0);
    $discountAmount = max(0, $grossSubtotal - $netAfterDiscount);

    $advancePaid = (float) ($reservation->advance_paid ?? 0);
    $folioPayments = $reservation->stays?->flatMap(fn ($s) => $s->folio ? $s->folio->folioPayments : collect()) ?? collect();
    $advanceAlreadyApplied = $folioPayments->contains(fn ($p) => ($p->payment_method ?? null) === 'advance');
    $totalPaid = (float) ($advanceAlreadyApplied ? $folioPayments->sum('amount') : ($advancePaid + $folioPayments->sum('amount')));
    $balanceDue = max(0, (float) ($reservation->total_amount ?? 0) - $totalPaid);

    $headerBlue = '#3E7DA3';
    $borderBlack = '#000000';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('hotel::modules.reservation.reservationReceiptTitle') }}</title>
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

                {{-- Booking overview --}}
                <tr>
                    <td style="padding:0;border-bottom:1px solid {{ $borderBlack }};">
                        <table role="presentation" width="100%" cellpadding="8" cellspacing="0" border="0" style="font-size:12px;">
                            <tr>
                                <td style="width:33%;border-right:1px solid {{ $borderBlack }};border-bottom:1px solid {{ $borderBlack }};vertical-align:top;">
                                    <strong>{{ __('hotel::modules.reservation.emailBookingId') }}</strong><br>{{ $reservation->reservation_number }}
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
                                    <strong>{{ __('hotel::modules.reservation.emailCheckInActual') }}</strong><br>{{ $actualCheckInStr }}
                                </td>
                                <td style="vertical-align:top;">
                                    <strong>{{ __('hotel::modules.reservation.status') }}</strong><br>{{ $statusLabel }}
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
                                    <th align="left" style="border:1px solid {{ $borderBlack }};padding:6px;">{{ __('hotel::modules.reservation.emailAccommodationType') }}</th>
                                    <th align="left" style="border:1px solid {{ $borderBlack }};padding:6px;">{{ __('hotel::modules.reservation.emailRoomNumber') }}</th>
                                    <th align="center" style="border:1px solid {{ $borderBlack }};padding:6px;">{{ __('hotel::modules.reservation.emailNoOfAdults') }}</th>
                                    <th align="center" style="border:1px solid {{ $borderBlack }};padding:6px;">{{ __('hotel::modules.reservation.emailNoOfChildren') }}</th>
                                    <th align="right" style="border:1px solid {{ $borderBlack }};padding:6px;">{{ __('hotel::modules.reservation.emailPricePerNight') }}</th>
                                    <th align="right" style="border:1px solid {{ $borderBlack }};padding:6px;">{{ __('hotel::modules.reservation.total') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reservation->reservationRooms as $rr)
                                    <tr>
                                        <td style="border:1px solid {{ $borderBlack }};padding:6px;">{{ $rr->roomType?->name ?? '—' }}</td>
                                        <td style="border:1px solid {{ $borderBlack }};padding:6px;">{{ $rr->room?->room_number ?? '—' }}</td>
                                        <td align="center" style="border:1px solid {{ $borderBlack }};padding:6px;">{{ $reservation->adults ?? 0 }}</td>
                                        <td align="center" style="border:1px solid {{ $borderBlack }};padding:6px;">{{ $reservation->children ?? 0 }}</td>
                                        <td align="right" style="border:1px solid {{ $borderBlack }};padding:6px;">{{ currency_format($rr->rate ?? 0) }}</td>
                                        <td align="right" style="border:1px solid {{ $borderBlack }};padding:6px;">{{ currency_format($rr->total_amount ?? 0) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" style="border:1px solid {{ $borderBlack }};padding:8px;">{{ __('hotel::modules.reservation.noRoomsSelected') }}</td>
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
                            @forelse($reservation->reservationExtras as $re)
                                <tr>
                                    <td style="border:1px solid {{ $borderBlack }};padding:8px;">
                                        {{ $re->extraService?->name ?? '—' }}
                                        @if(($re->quantity ?? 0) > 1)
                                            / {{ __('hotel::modules.reservation.quantityShort') }} {{ $re->quantity }}
                                        @endif
                                        / {{ currency_format($re->total_amount ?? 0) }}
                                        @if($re->unit_price)
                                            — {{ __('hotel::modules.reservation.unitPrice') }} {{ currency_format($re->unit_price) }}
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
                                    @php $invoiceTaxLines = $reservation->invoiceTaxes(); @endphp
                                    @forelse($invoiceTaxLines as $line)
                                        <div style="margin-bottom:8px;">
                                            <strong>{{ $line['tax']->name ?? __('hotel::modules.reservation.bookingTax') }}</strong>
                                            @if($line['tax']->rate !== null && (float) $line['tax']->rate != 0)
                                                ({{ $line['tax']->rate }}%)
                                            @endif
                                            <br>{{ currency_format($line['amount']) }}
                                        </div>
                                    @empty
                                        <strong>{{ __('hotel::modules.reservation.bookingTax') }}</strong><br>{{ currency_format($reservation->tax_amount ?? 0) }}
                                    @endforelse
                                </td>
                                <td style="border-right:1px solid {{ $borderBlack }};border-bottom:1px solid {{ $borderBlack }};">
                                    <strong>{{ __('hotel::modules.reservation.total') }}</strong><br>{{ currency_format($reservation->total_amount ?? 0) }}
                                </td>
                                <td style="border-bottom:1px solid {{ $borderBlack }};">
                                    <strong>{{ __('hotel::modules.reservation.emailTotalPaid') }}</strong><br>{{ currency_format($totalPaid) }}
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3">
                                    <strong>{{ __('hotel::modules.reservation.emailDue') }}</strong><br>{{ currency_format($balanceDue) }}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                {{-- Payments --}}
                <tr>
                    <td style="padding:0;border-bottom:1px solid {{ $borderBlack }};">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td style="background-color:{{ $headerBlue }};color:#ffffff;font-weight:700;padding:8px 10px;font-size:13px;">
                                    {{ __('hotel::modules.reservation.emailPaymentInfo') }}
                                </td>
                            </tr>
                        </table>
                        <table role="presentation" width="100%" cellpadding="6" cellspacing="0" border="0" style="font-size:12px;border-collapse:collapse;">
                            <thead>
                                <tr>
                                    <th align="center" style="border:1px solid {{ $borderBlack }};padding:6px;">#</th>
                                    <th align="left" style="border:1px solid {{ $borderBlack }};padding:6px;">{{ __('hotel::modules.reservation.date') }}</th>
                                    <th align="left" style="border:1px solid {{ $borderBlack }};padding:6px;">{{ __('hotel::modules.reservation.emailReferenceNo') }}</th>
                                    <th align="right" style="border:1px solid {{ $borderBlack }};padding:6px;">{{ __('hotel::modules.reservation.amount') }}</th>
                                    <th align="left" style="border:1px solid {{ $borderBlack }};padding:6px;">{{ __('hotel::modules.reservation.emailPaymentMode') }}</th>
                                    <th align="left" style="border:1px solid {{ $borderBlack }};padding:6px;">{{ __('hotel::modules.reservation.emailPaymentNote') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $payIndex = 0; @endphp
                                @forelse($folioPayments->sortBy('created_at')->values() as $payment)
                                    @php $payIndex++; @endphp
                                    <tr>
                                        <td align="center" style="border:1px solid {{ $borderBlack }};padding:6px;">{{ $payIndex }}</td>
                                        <td style="border:1px solid {{ $borderBlack }};padding:6px;">{{ optional($payment->created_at)->format($df) ?? '—' }}</td>
                                        <td style="border:1px solid {{ $borderBlack }};padding:6px;">{{ $payment->transaction_reference ?? '—' }}</td>
                                        <td align="right" style="border:1px solid {{ $borderBlack }};padding:6px;">{{ currency_format($payment->amount ?? 0) }}</td>
                                        <td style="border:1px solid {{ $borderBlack }};padding:6px;">{{ ucfirst((string) ($payment->payment_method ?? '—')) }}</td>
                                        <td style="border:1px solid {{ $borderBlack }};padding:6px;">{{ $payment->notes ? $payment->notes : '—' }}</td>
                                    </tr>
                                @empty
                                    @if($advancePaid > 0 && !$advanceAlreadyApplied)
                                        @php $payIndex = 1; @endphp
                                        <tr>
                                            <td align="center" style="border:1px solid {{ $borderBlack }};padding:6px;">1</td>
                                            <td style="border:1px solid {{ $borderBlack }};padding:6px;">{{ optional($reservation->created_at)->format($df) ?? '—' }}</td>
                                            <td style="border:1px solid {{ $borderBlack }};padding:6px;">—</td>
                                            <td align="right" style="border:1px solid {{ $borderBlack }};padding:6px;">{{ currency_format($advancePaid) }}</td>
                                            <td style="border:1px solid {{ $borderBlack }};padding:6px;">{{ __('hotel::modules.reservation.advance') }}</td>
                                            <td style="border:1px solid {{ $borderBlack }};padding:6px;">—</td>
                                        </tr>
                                    @else
                                        <tr>
                                            <td colspan="6" style="border:1px solid {{ $borderBlack }};padding:8px;">{{ __('hotel::modules.reservation.noPaymentsRecorded') }}</td>
                                        </tr>
                                    @endif
                                @endforelse
                            </tbody>
                        </table>
                    </td>
                </tr>

                {{-- Footer terms --}}
                <tr>
                    <td style="padding:12px;font-size:12px;">
                        {{ __('hotel::modules.reservation.emailTermsFooter') }}
                    </td>
                </tr>

                <tr>
                    <td style="padding:8px 12px 16px;font-size:12px;color:#6b7280;border-top:1px solid {{ $borderBlack }};">
                        {{ __('hotel::modules.reservation.reservationCreatedEmailFooter') }}
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
