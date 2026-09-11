<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_resets', function (Blueprint $table) {
            $table->id();
            $table->decimal('counted_amount', 10, 2);
            $table->decimal('left_in_drawer', 10, 2);
            $table->string('cashier_name')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_resets');
    }
};
