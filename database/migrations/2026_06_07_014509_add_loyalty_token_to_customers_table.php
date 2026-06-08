<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'loyalty_token')) {
                $table->string('loyalty_token')->nullable()->unique()->after('phone');
            }
        });

        DB::table('customers')
            ->whereNull('loyalty_token')
            ->orderBy('id')
            ->chunkById(100, function ($customers) {
                foreach ($customers as $customer) {
                    DB::table('customers')
                        ->where('id', $customer->id)
                        ->update([
                            'loyalty_token' => Str::random(48),
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'loyalty_token')) {
                $table->dropColumn('loyalty_token');
            }
        });
    }
};