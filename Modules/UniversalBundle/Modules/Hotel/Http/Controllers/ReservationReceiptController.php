<?php

namespace Modules\Hotel\Http\Controllers;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Modules\Hotel\Entities\Reservation;

class ReservationReceiptController extends Controller
{
    public function show(Reservation $reservation)
    {
        abort_if(!module_enabled('Hotel'), 403);
        abort_if(!in_array('Hotel', restaurant_modules()), 403);
        abort_if(!user_can('Show Hotel Reservations'), 403);

        $reservation->load([
            'primaryGuest',
            'tax',
            'taxes',
            'reservationRooms.roomType',
            'reservationExtras.extraService',
            'stays.folio.folioPayments',
        ]);

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

        $pdf = Pdf::loadView('hotel::receipts.reservation-receipt', [
            'reservation' => $reservation,
            'roomsTotal' => $roomsTotal,
            'extrasTotal' => $extrasTotal,
            'grossSubtotal' => $grossSubtotal,
            'discountAmount' => $discountAmount,
            'netAfterDiscount' => $netAfterDiscount,
            'totalPaid' => $totalPaid,
            'advancePaid' => $advancePaid,
            'balanceDue' => $balanceDue,
        ]);

        $fileName = 'reservation-receipt-' . $reservation->reservation_number . '.pdf';

        // If `download=1` is present, force download; otherwise render inline (good for "Print Invoice").
        if (request()->boolean('download')) {
            return $pdf->download($fileName);
        }

        return $pdf->stream($fileName);
    }
}

