<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_stakings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->enum('wallet', ['MIND', 'MUSD', 'BMIND', 'USDT'])->default('MIND');
            $table->decimal('amount', 18, 8);
            $table->integer('duration');
            $table->integer('received_days')->default(0);
            $table->decimal('apy_value', 10, 4);
            $table->decimal('total_value', 18, 8)->nullable();
            $table->decimal('daily', 18, 8);
            $table->decimal('seller_bonus_rate', 10, 4)->default(0);
            $table->boolean('status')->default(0)->index();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_stakings');
    }
};
