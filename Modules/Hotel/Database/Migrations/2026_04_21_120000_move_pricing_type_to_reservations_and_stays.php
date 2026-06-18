<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Hotel\Enums\PricingType;

return new class extends Migration
{
    public function up(): void
    {
        // Add pricing_type to hotel_reservations
        Schema::table('hotel_reservations', function (Blueprint $table) {
            if (!Schema::hasColumn('hotel_reservations', 'pricing_type')) {
                $table->enum('pricing_type', array_column(PricingType::cases(), 'value'))
                    ->default(PricingType::DAILY->value)
                    ->after('rate_plan_id')
                    ->nullable();
            }
        });

        // Add pricing_type to hotel_stays
        Schema::table('hotel_stays', function (Blueprint $table) {
            if (!Schema::hasColumn('hotel_stays', 'pricing_type')) {
                $table->enum('pricing_type', array_column(PricingType::cases(), 'value'))
                    ->default(PricingType::DAILY->value)
                    ->after('status')
                    ->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('hotel_stays', function (Blueprint $table) {
            if (Schema::hasColumn('hotel_stays', 'pricing_type')) {
                $table->dropColumn('pricing_type');
            }
        });

        Schema::table('hotel_reservations', function (Blueprint $table) {
            if (Schema::hasColumn('hotel_reservations', 'pricing_type')) {
                $table->dropColumn('pricing_type');
            }
        });
    }
};
