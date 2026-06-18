<?php

namespace Modules\Hotel\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Hotel\Entities\Agreement;

class AgreementController extends Controller
{
    public function index()
    {
        abort_if(!module_enabled('Hotel'), 403);
        abort_if(!in_array('Hotel', restaurant_modules()), 403);
        abort_if(!user_can('Show Hotel Reservations'), 403);

        return view('hotel::agreements.index');
    }

    public function print(Agreement $agreement)
    {
        abort_if(!module_enabled('Hotel'), 403);
        abort_if(!in_array('Hotel', restaurant_modules()), 403);

        $agreement->load(['reservation.primaryGuest', 'reservation.restaurant', 'reservation.branch', 'createdBy']);

        return view('hotel::agreements.print', compact('agreement'));
    }
}
