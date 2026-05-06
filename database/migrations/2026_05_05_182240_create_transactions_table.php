<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {

            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 16, 2);
            $table->enum('wallet', ['USDT', 'MIND', 'MUSD', 'BMIND'])->index();
            $table->enum('type', ['Debit', 'Credit'])->index();
            $table->string('method')->nullable();
            $table->text('description')->nullable();
            $table->string('txn_id')->unique()->nullable();
            $table->string('kids_username')->nullable()->index();
            $table->string('confirmation_code')->nullable();
            $table->enum('status', ['Pending', 'Approved', 'Reject', 'Expired'])->default('Pending')->index();
            $table->timestamps();

        });
    }

    public function down()
    {
        Schema::dropIfExists('transactions');
    }
};
