<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Hotel\Enums\QuotationStatus;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotel_quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('restaurants')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');

            $table->string('quotation_number')->unique();
            $table->foreignId('primary_guest_id')->constrained('hotel_guests')->onDelete('cascade');

            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();

            $table->integer('rooms_count')->default(1);
            $table->integer('adults')->default(1);
            $table->integer('children')->default(0);
            $table->foreignId('rate_plan_id')->nullable()->constrained('hotel_rate_plans')->onDelete('set null');

            $table->enum('status', array_column(QuotationStatus::cases(), 'value'))->default(QuotationStatus::DRAFT->value);

            $table->text('special_requests')->nullable();
            $table->text('reason_for_trip')->nullable();
            $table->string('means_of_transport')->nullable();
            $table->string('place_of_origin')->nullable();
            $table->string('vehicle_registration_number')->nullable();
            $table->string('final_destination')->nullable();

            $table->string('discount_type')->nullable(); // percentage, fixed
            $table->decimal('discount_value', 10, 2)->default(0);
            $table->decimal('subtotal_before_tax', 10, 2)->nullable(); // net after discount, before tax
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('extras_amount', 10, 2)->default(0);
            $table->foreignId('tax_id')->nullable()->constrained('hotel_taxes')->onDelete('set null');

            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('advance_paid', 10, 2)->default(0);
            $table->string('advance_payment_method')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['restaurant_id', 'branch_id']);
            $table->index('primary_guest_id');
            $table->index(['check_in_date', 'check_out_date']);
            $table->index('status');
        });

        Schema::create('hotel_quotation_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained('hotel_quotations')->onDelete('cascade');
            $table->foreignId('room_type_id')->constrained('hotel_room_types')->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->decimal('rate', 10, 2);
            $table->decimal('total_amount', 10, 2);
            $table->timestamps();

            $table->index('quotation_id');
            $table->index('room_type_id');
        });

        Schema::create('hotel_quotation_guests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained('hotel_quotations')->onDelete('cascade');
            $table->foreignId('guest_id')->constrained('hotel_guests')->onDelete('cascade');
            $table->boolean('is_primary')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['quotation_id', 'guest_id']);
            $table->index('quotation_id');
            $table->index('guest_id');
        });

        Schema::create('hotel_quotation_extras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained('hotel_quotations')->onDelete('cascade');
            $table->foreignId('extra_service_id')->constrained('hotel_extra_services')->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_amount', 10, 2);
            $table->timestamps();

            $table->index('quotation_id');
            $table->index('extra_service_id');
        });

        if (Schema::hasTable('hotel_reservation_tax')) {
            return;
        }

        Schema::create('hotel_reservation_tax', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained('hotel_reservations')->cascadeOnDelete();
            $table->foreignId('tax_id')->constrained('hotel_taxes')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['reservation_id', 'tax_id']);
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_quotations');
        Schema::dropIfExists('hotel_quotation_rooms');
        Schema::dropIfExists('hotel_quotation_guests');
        Schema::dropIfExists('hotel_quotation_extras');
        Schema::dropIfExists('hotel_reservation_tax');
    }
};

