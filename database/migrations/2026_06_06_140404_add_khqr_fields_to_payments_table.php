<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->longText('khqr_payload')->nullable()->after('transaction_id');
            $table->string('khqr_md5')->nullable()->after('khqr_payload');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['khqr_payload', 'khqr_md5']);
        });
    }
};