<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('liquidity_pools', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('wallet', ['USDT', 'MIND', 'MUSD', 'BMIND'])->index();
            $table->decimal('invested_amount', 30, 8);
            $table->decimal('reward_percentage', 8, 2);
            $table->decimal('reward_amount', 30, 8);
            $table->decimal('total_return_amount', 30, 8);
            $table->unsignedInteger('lock_days');
            $table->timestamp('invested_at');
            $table->timestamp('release_at');
            $table->enum('status', ['Active','Released','Cancelled'])->default('Active');
            $table->timestamp('released_at')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status']);
            $table->index(['status', 'release_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('liquidity_pools');
    }
};
