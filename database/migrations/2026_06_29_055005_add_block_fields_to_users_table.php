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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_block')->default(false)->after('status');
            $table->boolean('transfer_block')->default(false)->after('is_block');
            $table->boolean('withdraw_block')->default(false)->after('transfer_block');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'is_block',
                'transfer_block',
                'withdraw_block',
            ]);
        });
    }
};
