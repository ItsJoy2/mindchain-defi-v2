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
        Schema::create('elite_settings', function (Blueprint $table) {

            $table->id();
            $table->integer('mem_fee')->default(0);
            $table->decimal('daily_bonus', 10, 3)->default(0);
            $table->string('duration', 11)->default('30');
            $table->double('sponsor_bonus', 8, 2)->default(0);
            $table->integer('lvl1')->default(0);
            $table->integer('lvl2')->default(0);
            $table->string('status')->default('1');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('elite_settings');
    }
};
