<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {

            $table->id();

            $table->foreignId('sponsor_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('user_name')->unique();
            $table->string('email')->nullable()->unique();
            $table->string('password');

            $table->string('referral_code')->nullable()->unique();

            $table->string('name')->nullable();
            $table->string('image', 100)->nullable();

            $table->date('date_of_birth')->nullable();
            $table->string('gender', 10)->nullable();

            $table->string('contact', 100)->nullable();

            $table->string('address')->nullable();
            $table->string('city', 150)->nullable();
            $table->string('country', 150)->nullable();
            $table->string('postal_code', 100)->nullable();

            $table->timestamp('email_verified_at')->nullable();

            $table->string('nid_passport')->nullable()->unique();

            $table->rememberToken();

            $table->boolean('is_admin')->default(false);
            $table->boolean('status')->default(true);

            $table->boolean('merchant_status')->default(false);
            $table->boolean('kyc')->default(false);
            $table->boolean('consultant')->default(false);

            $table->integer('ambassador')->default(0);
            $table->integer('elite_club')->default(0);
            $table->integer('angel_club')->default(0);

            $table->timestamp('last_login')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
