<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bmind_staking_settings', function (Blueprint $table) {

            // 180 Days
            $table->decimal('days_180_af2', 10, 2)->default(1.00)->after('days_180_af');
            $table->decimal('days_180_af3', 10, 2)->default(1.00)->after('days_180_af2');

            // 365 Days
            $table->decimal('days_365_af2', 10, 2)->default(3.00)->after('days_365_af');
            $table->decimal('days_365_af3', 10, 2)->default(1.00)->after('days_365_af2');

            // 730 Days
            $table->decimal('days_730_af2', 10, 2)->default(3.00)->after('days_730_af');
            $table->decimal('days_730_af3', 10, 2)->default(1.00)->after('days_730_af2');

            // 1825 Days
            $table->decimal('days_1825', 10, 2)->default(15.00)->after('days_730');
            $table->decimal('days_1825_af', 10, 2)->default(9.00)->after('days_1825');
            $table->decimal('days_1825_af2', 10, 2)->default(5.00)->after('days_1825_af');
            $table->decimal('days_1825_af3', 10, 2)->default(1.00)->after('days_1825_af2');
        });
    }

    public function down(): void
    {
        Schema::table('bmind_staking_settings', function (Blueprint $table) {

            $table->dropColumn([
                'days_180_af2',
                'days_180_af3',

                'days_365_af2',
                'days_365_af3',

                'days_730_af2',
                'days_730_af3',

                'days_1825',
                'days_1825_af',
                'days_1825_af2',
                'days_1825_af3',
            ]);
        });
    }
};
