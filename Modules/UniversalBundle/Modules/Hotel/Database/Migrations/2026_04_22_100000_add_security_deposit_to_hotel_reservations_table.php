<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hotel_reservations', function (Blueprint $table) {
            if (!Schema::hasColumn('hotel_reservations', 'security_deposit')) {
                $table->decimal('security_deposit', 10, 2)->default(0)->after('advance_paid');
            }
        });
    }

    public function down(): void
    {
        Schema::table('hotel_reservations', function (Blueprint $table) {
            if (Schema::hasColumn('hotel_reservations', 'security_deposit')) {
                $table->dropColumn('security_deposit');
            }
        });
    }
};

