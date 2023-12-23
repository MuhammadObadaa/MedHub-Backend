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
        Schema::table('medicines', function (Blueprint $table) {
            $table->string('ar_name')->after('name');
            $table->string('ar_scientificName')->after('scientificName');
            $table->text('ar_description')->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            $table->dropColumn('ar_name');
            $table->dropColumn('ar_scientificName');
            $table->dropColumn('ar_description');
        });
    }
};
