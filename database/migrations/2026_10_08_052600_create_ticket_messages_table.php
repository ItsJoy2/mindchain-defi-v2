<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_messages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ticket_id')
                ->constrained('tickets')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->enum('sender_type', [
                'user',
                'admin',
            ]);

            $table->text('message')->nullable();

            $table->string('attachment')->nullable();

            $table->string('attachment_name')->nullable();

            $table->string('attachment_type')->nullable();

            $table->unsignedBigInteger('attachment_size')->nullable();

            $table->timestamps();

            $table->index(['ticket_id', 'created_at']);
            $table->index('sender_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_messages');
    }
};
