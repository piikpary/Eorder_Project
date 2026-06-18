<?php

namespace Modules\Hotel\Http\Controllers;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Modules\Hotel\Entities\Quotation;

class QuotationConfirmationController extends Controller
{
    public function show(Quotation $quotation)
    {
        abort_if(!module_enabled('Hotel'), 403);
        abort_if(!in_array('Hotel', restaurant_modules()), 403);
        abort_if(!user_can('Show Hotel Quotations'), 403);

        $quotation->load([
            'restaurant',
            'branch',
            'primaryGuest',
            'tax',
            'taxes',
            'quotationRooms.roomType',
            'quotationExtras.extraService',
        ]);

        $roomsTotal = (float) $quotation->quotationRooms->sum('total_amount');
        $extrasTotal = (float) $quotation->quotationExtras->sum('total_amount');
        $grossSubtotal = $roomsTotal + $extrasTotal;

        $netAfterDiscount = (float) ($quotation->subtotal_before_tax ?? 0);
        $discountAmount = max(0, $grossSubtotal - $netAfterDiscount);

        $advancePaid = (float) ($quotation->advance_paid ?? 0);
        $balanceDue = max(0, (float) ($quotation->total_amount ?? 0) - $advancePaid);

        $pdf = Pdf::loadView('hotel::receipts.quotation-confirmation', [
            'quotation' => $quotation,
            'roomsTotal' => $roomsTotal,
            'extrasTotal' => $extrasTotal,
            'grossSubtotal' => $grossSubtotal,
            'discountAmount' => $discountAmount,
            'netAfterDiscount' => $netAfterDiscount,
            'advancePaid' => $advancePaid,
            'balanceDue' => $balanceDue,
        ]);

        $fileName = 'quotation-confirmation-' . $quotation->quotation_number . '.pdf';

        if (request()->boolean('download')) {
            return $pdf->download($fileName);
        }

        return $pdf->stream($fileName);
    }
}

