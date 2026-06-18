<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Hotel\Enums\AgreementType;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotel_agreements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('restaurants')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            $table->foreignId('reservation_id')->constrained('hotel_reservations')->onDelete('cascade');
            $table->foreignId('template_id')->nullable()->constrained('hotel_agreement_templates')->onDelete('set null');
            $table->string('agreement_number')->unique();
            $table->enum('type', array_column(AgreementType::cases(), 'value'));
            $table->longText('content');
            $table->date('agreement_date');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['restaurant_id', 'branch_id']);
            $table->index('reservation_id');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_agreements');
    }
};
