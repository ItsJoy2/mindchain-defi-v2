<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('level_settings', function (Blueprint $table) {
            $table->id();
            $table->string('lvl_1');
            $table->string('lvl_2');
            $table->string('lvl_3');
            $table->string('lvl_4');
            $table->string('lvl_5');
            $table->boolean('status')->default(1)->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('level_settings');
    }
};
