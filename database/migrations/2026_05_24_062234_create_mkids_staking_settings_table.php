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
        Schema::create('mkids_staking_settings', function (Blueprint $table) {

            $table->id();
            $table->decimal('amount', 20, 2)->default(10);
            $table->decimal('token_bonus', 10, 2)->default(1000);
            $table->decimal('level_1_bonus', 10, 2)->default(15);
            $table->decimal('level_2_bonus', 10, 2)->default(10);
            $table->decimal('level_3_bonus', 10, 2)->default(5);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mkids_staking_settings');
    }
};
