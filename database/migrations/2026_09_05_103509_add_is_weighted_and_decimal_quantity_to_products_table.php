<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_weighted')->default(false)->after('unit');
        });

        // decimal(10,3) lets weighted products (e.g. 0.250 kg) store fractional
        // quantities, while whole-unit products keep working unchanged since an
        // integer quantity is just a decimal with .000.
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('quantity', 10, 3)->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->integer('quantity')->default(0)->change();
            $table->dropColumn('is_weighted');
        });
    }
};
