<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loyalty_reward_telegram_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('stamp_rule_id')->nullable();
            $table->string('reward_key')->index();
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('reward_name')->nullable();
            $table->integer('current_stamps')->default(0);
            $table->integer('required_stamps')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->unique(['customer_id', 'stamp_rule_id', 'reward_key'], 'loyalty_reward_unique_alert');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_reward_telegram_logs');
    }
};