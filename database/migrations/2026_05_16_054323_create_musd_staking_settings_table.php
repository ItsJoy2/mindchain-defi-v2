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
        Schema::create('musd_staking_settings', function (Blueprint $table) {

            $table->id();
            $table->decimal('min_staking', 20, 8)->default(25);
            $table->decimal('max_staking', 20, 8)->default(5000);
            $table->decimal('days_365', 10, 2)->default(20.00);
            $table->decimal('days_730', 10, 2)->default(25.00);
            $table->decimal('days_1825', 10, 2)->default(30.00);
            $table->decimal('days_365_af', 10, 2)->default(7.00);
            $table->decimal('days_730_af', 10, 2)->default(9.00);
            $table->decimal('days_1825_af', 10, 2)->default(11.00);
            $table->decimal('seller_bonus', 10, 2)->default(3.00);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('musd_staking_settings');
    }
};
