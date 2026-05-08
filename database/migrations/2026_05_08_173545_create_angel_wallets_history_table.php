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
        Schema::create('angel_wallets_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 16, 8);
            $table->enum('wallet', ['USDT', 'MIND', 'MUSD', 'BMIND'])->index();
            $table->enum('type', ['Debit', 'Credit'])->index();
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
        Schema::dropIfExists('angel_wallets_history');
    }
};
