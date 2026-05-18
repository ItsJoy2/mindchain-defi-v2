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
        Schema::create('angel_settings', function (Blueprint $table) {

            $table->id();

            $table->decimal('membership_fee', 20, 8)->default(299);
            $table->integer('duration')->default(365);
            $table->decimal('apy', 10, 2)->default(25);
            $table->decimal('level_1_bonus', 10, 2)->default(10);
            $table->decimal('level_2_bonus', 10, 2)->default(3);
            $table->decimal('level_3_bonus', 10, 2)->default(1);
            $table->integer('total_member')->default(0);
            $table->boolean('status')->default(1);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('angel_settings');
    }
};
