<?php

namespace Modules\Hotel\Notifications;

use App\Notifications\BaseNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Notifications\Messages\MailMessage;
use Modules\Hotel\Entities\Quotation;

class HotelQuotationConfirmation extends BaseNotification
{
    public function __construct(protected Quotation $quotation)
    {
        $this->restaurant = $quotation->restaurant ?? $quotation->branch?->restaurant;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $build = parent::build($notifiable);

        $this->quotation->loadMissing([
            'restaurant',
            'branch',
            'primaryGuest',
            'tax',
            'taxes',
            'quotationRooms.roomType',
            'quotationExtras.extraService',
        ]);

        $roomsTotal = (float) $this->quotation->quotationRooms->sum('total_amount');
        $extrasTotal = (float) $this->quotation->quotationExtras->sum('total_amount');
        $grossSubtotal = $roomsTotal + $extrasTotal;

        $netAfterDiscount = (float) ($this->quotation->subtotal_before_tax ?? 0);
        $discountAmount = max(0, $grossSubtotal - $netAfterDiscount);

        $advancePaid = (float) ($this->quotation->advance_paid ?? 0);
        $balanceDue = max(0, (float) ($this->quotation->total_amount ?? 0) - $advancePaid);

        $pdf = Pdf::loadView('hotel::receipts.quotation-confirmation', [
            'quotation' => $this->quotation,
            'roomsTotal' => $roomsTotal,
            'extrasTotal' => $extrasTotal,
            'grossSubtotal' => $grossSubtotal,
            'discountAmount' => $discountAmount,
            'netAfterDiscount' => $netAfterDiscount,
            'advancePaid' => $advancePaid,
            'balanceDue' => $balanceDue,
        ]);

        $fileName = 'quotation-confirmation-' . $this->quotation->quotation_number . '.pdf';

        $subject = __('hotel::modules.quotation.emailInvoiceTitle') . ' - ' . $this->quotation->quotation_number;

        return $build
            ->subject($subject)
            ->view('hotel::emails.hotel-quotation-invoice', [
                'quotation' => $this->quotation,
                'restaurant' => $this->restaurant,
            ])
            ->attachData($pdf->output(), $fileName, ['mime' => 'application/pdf']);
    }
}

