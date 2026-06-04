<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mind_staking_settings', function (Blueprint $table) {

            $table->decimal('level_2', 10, 4)
                ->default(0)
                ->after('seller_bonus');
        });
    }

    public function down(): void
    {
        Schema::table('mind_staking_settings', function (Blueprint $table) {

            $table->dropColumn([
                'level_2',
            ]);
        });
    }
};
