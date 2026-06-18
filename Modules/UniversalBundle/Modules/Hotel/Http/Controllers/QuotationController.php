<?php

namespace Modules\Hotel\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Hotel\Entities\Quotation;

class QuotationController extends Controller
{
    public function index()
    {
        abort_if(!module_enabled('Hotel'), 403);
        abort_if(!in_array('Hotel', restaurant_modules()), 403);
        abort_if(!user_can('Show Hotel Quotations'), 403);

        return view('hotel::quotations.index');
    }

    public function create()
    {
        abort_if(!module_enabled('Hotel'), 403);
        abort_if(!in_array('Hotel', restaurant_modules()), 403);
        abort_if(!user_can('Create Hotel Quotation'), 403);

        return view('hotel::quotations.create');
    }

    public function edit(Quotation $quotation)
    {
        abort_if(!module_enabled('Hotel'), 403);
        abort_if(!in_array('Hotel', restaurant_modules()), 403);
        abort_if(!user_can('Update Hotel Quotation'), 403);

        return view('hotel::quotations.edit', ['quotation' => $quotation]);
    }
}

