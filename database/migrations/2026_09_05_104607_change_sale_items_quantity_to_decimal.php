<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Weighted products (e.g. 0.250 kg) submit fractional quantities at
        // checkout - an integer column here would silently truncate them to 0.
        Schema::table('sale_items', function (Blueprint $table) {
            $table->decimal('quantity', 10, 3)->default(1)->change();
        });
    }

    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->integer('quantity')->default(1)->change();
        });
    }
};
