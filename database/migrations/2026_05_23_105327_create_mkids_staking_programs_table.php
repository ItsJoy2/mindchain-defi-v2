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
        Schema::create('mkids_staking_programs', function (Blueprint $table) {

            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('kids_name');
            $table->string('kids_username')->nullable()->unique();
            $table->string('kids_father_name');
            $table->string('kids_mother_name');
            $table->date('dob');
            $table->integer('age');
            $table->string('kids_birth_place');
            $table->string('country');
            $table->integer('count')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mkids_staking_programs');
    }
};
