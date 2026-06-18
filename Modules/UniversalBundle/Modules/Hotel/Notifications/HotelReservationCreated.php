<?php

namespace Modules\Hotel\Notifications;

use App\Notifications\BaseNotification;
use Illuminate\Notifications\Messages\MailMessage;
use Modules\Hotel\Entities\Reservation;

class HotelReservationCreated extends BaseNotification
{
    public function __construct(protected Reservation $reservation)
    {
        $this->restaurant = $reservation->restaurant ?? $reservation->branch?->restaurant;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $build = parent::build($notifiable);

        $this->reservation->loadMissing([
            'restaurant',
            'branch',
            'primaryGuest',
            'tax',
            'taxes',
            'reservationRooms.roomType',
            'reservationRooms.room',
            'reservationExtras.extraService',
            'stays.folio.folioPayments',
        ]);

        $restaurant = $this->restaurant
            ?? $this->reservation->restaurant
            ?? $this->reservation->branch?->restaurant;

        return $build
            ->subject(__('hotel::modules.reservation.reservationCreatedEmailSubject', [
                'site_name' => $restaurant?->name ?? config('app.name'),
                'number' => $this->reservation->reservation_number,
            ]))
            ->view('hotel::emails.hotel-reservation-invoice', [
                'reservation' => $this->reservation,
                'restaurant' => $restaurant,
            ]);
    }
}

