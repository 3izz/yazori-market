<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->string('cashier_name')->nullable()->after('user_id');
            $table->timestamp('refunded_at')->nullable()->after('cashier_name');
            $table->string('refund_reason')->nullable()->after('refunded_at');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['cashier_name', 'refunded_at', 'refund_reason']);
        });
    }
};
