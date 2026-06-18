<?php

namespace Modules\Hotel\Database\Seeders;

use App\Models\Restaurant;
use Illuminate\Database\Seeder;
use Modules\Hotel\Entities\Tax;
use App\Models\Branch;
use App\Models\OrderType;
use Illuminate\Support\Facades\DB;

class HotelTaxSeeder extends Seeder
{
    public function run(): void
    {
        $restaurants = Restaurant::with('branches')->get();

        foreach ($restaurants as $restaurant) {
            foreach ($restaurant->branches as $branch) {
                $exists = Tax::where('restaurant_id', $restaurant->id)
                    ->where('branch_id', $branch->id)
                    ->exists();

                if ($exists) {
                    continue;
                }

                Tax::create([
                    'restaurant_id' => $restaurant->id,
                    'branch_id' => $branch->id,
                    'name' => 'VAT 5%',
                    'rate' => 5,
                    'is_active' => true,
                ]);

                Tax::create([
                    'restaurant_id' => $restaurant->id,
                    'branch_id' => $branch->id,
                    'name' => 'Service Tax 10%',
                    'rate' => 10,
                    'is_active' => true,
                ]);
            }
        }

         // Add Room Service order type for all branches
         $branches = Branch::all();

         foreach ($branches as $branch) {
             // Check if room service order type already exists
             $exists = OrderType::where('branch_id', $branch->id)
                 ->where('slug', 'room_service')
                 ->exists();
 
             if (!$exists) {
                 DB::table('order_types')->insert([
                     'branch_id' => $branch->id,
                     'order_type_name' => 'Room Service',
                     'slug' => 'room_service',
                     'is_active' => true,
                     'is_default' => false,
                     'type' => 'room_service',
                     'created_at' => now(),
                     'updated_at' => now(),
                 ]);
             }
         }
    }
}
