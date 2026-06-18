<?php

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InventoryItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        abort_if(!in_array('Inventory', restaurant_modules()), 403);
        abort_if(!user_can('Show Inventory Item'), 403);

        return view('inventory::inventory-items.index');
    }

    public function downloadSampleCsv()
    {
        abort_if(!in_array('Inventory', restaurant_modules()), 403);
        abort_if(!user_can('Create Inventory Item'), 403);

        $path = storage_path('app/inventory-items-sample.csv');

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->download($path, 'inventory-items-sample.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
  
}
