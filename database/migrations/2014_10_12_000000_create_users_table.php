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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('pharmacyName');
            //$table->string('pharmacyLocation')->nullable();
            $table->string('phoneNumber')->unique();
            $table->string('password');
            $table->boolean('is_admin')->default(False);
            $table->string('image');
            $table->rememberToken();
            $table->string('FCMToken')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
