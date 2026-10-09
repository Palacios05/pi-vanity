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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('surname')->nullable();
            $table->string('full_name');

            $table->string('phone')->nullable();
            $table->string('email')->nullable();

            $table->string('type_client')->nullable();
            $table->string('type_document')->nullable();
            $table->string('n_document')->unique();

            $table->date('birth_date')->nullable();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('sucursale_id')
                ->constrained('sucursales')
                ->cascadeOnDelete();

            $table->boolean('state')->default(true);

            $table->string('gender')->nullable();

            $table->string('ubigeo_region')->nullable();
            $table->string('ubigeo_provincia')->nullable();
            $table->string('ubigeo_distrito')->nullable();

            $table->string('region')->nullable();
            $table->string('provincia')->nullable();
            $table->string('distrito')->nullable();

            $table->text('address')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};