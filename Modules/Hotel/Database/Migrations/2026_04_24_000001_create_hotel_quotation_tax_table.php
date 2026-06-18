<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('hotel_quotation_tax')) {
            return;
        }

        Schema::create('hotel_quotation_tax', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained('hotel_quotations')->cascadeOnDelete();
            $table->foreignId('tax_id')->constrained('hotel_taxes')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['quotation_id', 'tax_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_quotation_tax');
    }
};

