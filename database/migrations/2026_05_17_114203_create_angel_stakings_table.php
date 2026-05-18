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
        Schema::create('angel_stakings', function (Blueprint $table) {

            $table->id();

            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 20, 8)->default(0);
            $table->integer('duration')->default(0);
            $table->decimal('daily_bonus', 20, 8)->default(0);
            $table->integer('received_days')->default(0);
            $table->boolean('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('angel_stakings');
    }
};
