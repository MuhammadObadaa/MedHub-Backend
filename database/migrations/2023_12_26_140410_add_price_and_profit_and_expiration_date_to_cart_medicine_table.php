<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cart_medicine', function (Blueprint $table) {
            $table->unsignedInteger('price')->default(0)->after('quantity');
            $table->unsignedInteger('profit')->default(0)->after('price');
            $table->date('expirationDate')->nullable()->after('profit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cart_medicine', function (Blueprint $table) {
            //
        });
    }
};
