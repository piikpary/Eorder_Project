<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'telegram_chat_id')) {
                $table->string('telegram_chat_id')->nullable()->after('loyalty_token');
            }

            if (!Schema::hasColumn('customers', 'telegram_username')) {
                $table->string('telegram_username')->nullable()->after('telegram_chat_id');
            }

            if (!Schema::hasColumn('customers', 'telegram_connected_at')) {
                $table->timestamp('telegram_connected_at')->nullable()->after('telegram_username');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'telegram_connected_at')) {
                $table->dropColumn('telegram_connected_at');
            }

            if (Schema::hasColumn('customers', 'telegram_username')) {
                $table->dropColumn('telegram_username');
            }

            if (Schema::hasColumn('customers', 'telegram_chat_id')) {
                $table->dropColumn('telegram_chat_id');
            }
        });
    }
};