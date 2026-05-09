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
        Schema::create('usdt_staking_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 16, 8);
            $table->double('daily_bonus', 8, 2)->default(0);
            $table->enum('wallet', ['USDT', 'MIND', 'MUSD', 'BMIND'])->index();
            $table->enum('type', ['Debit', 'Credit'])->index();
            $table->integer('duration')->default(0);
            $table->integer('received_days')->default(0);
            $table->string('method')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['Pending', 'Approved', 'Reject', 'Expired', 'Processing'])->default('Pending')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usdt_staking_histories');
    }
};
