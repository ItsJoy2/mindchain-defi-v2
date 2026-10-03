<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('liquidity_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('min_amount', 30, 8)->default(50);
            $table->decimal('reward_percentage', 8, 2)->default(100);
            $table->unsignedInteger('lock_days')->default(730);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        // Default configuration
        DB::table('liquidity_settings')->insert([
            'min_amount'        => 50,
            'reward_percentage'=> 100,
            'lock_days'         => 730,
            'status'            => true,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('liquidity_settings');
    }
};
