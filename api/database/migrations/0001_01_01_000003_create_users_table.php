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

            $table->string('name');
            $table->string('surname');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();

            $table->unsignedBigInteger('role_id')->nullable();
            $table->unsignedBigInteger('sucursale_id')->nullable();

            $table->string('phone')->nullable();
            $table->string('avatar')->nullable();
            $table->string('type_document')->nullable();
            $table->string('n_document')->nullable();
            $table->string('gender')->nullable();

            $table->boolean('state')->default(true);

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};