<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mind_staking_settings', function (Blueprint $table) {
            $table->id();

            // staking limits
            $table->decimal('min_staking', 18, 8)->nullable();
            $table->decimal('max_staking', 18, 8)->nullable();

            // staking duration rates (% or reward multiplier)
            $table->decimal('days_90', 10, 4)->nullable();
            $table->decimal('days_180', 10, 4)->nullable();
            $table->decimal('days_365', 10, 4)->nullable();
            $table->decimal('days_730', 10, 4)->nullable();
            $table->decimal('days_1825', 10, 4)->nullable();

            // affiliate / bonus rates
            $table->decimal('days_90_af', 10, 4)->nullable();
            $table->decimal('days_180_af', 10, 4)->nullable();
            $table->decimal('days_365_af', 10, 4)->nullable();
            $table->decimal('days_730_af', 10, 4)->nullable();
            $table->decimal('days_1825_af', 10, 4)->nullable();

            // seller bonus
            $table->decimal('seller_bonus', 10, 4)->nullable();

            // status
            $table->boolean('status')->default(true)->index();

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mind_staking_settings');
    }
};
