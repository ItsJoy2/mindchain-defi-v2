<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deposit_jobs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('invoice_id')->unique();

            $table->decimal('amount', 28, 8);

            $table->string('wallet');

            $table->string('wallet_address')->nullable();

            $table->string('tx_hash')->nullable();

            $table->enum('status', [
                'pending',
                'paid',
                'failed',
                'cancelled'
            ])->default('pending');

            $table->json('gateway_response')->nullable();

            $table->timestamp('paid_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deposit_jobs');
    }
};
