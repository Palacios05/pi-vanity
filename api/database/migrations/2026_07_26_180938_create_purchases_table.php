<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();

            $table->foreignId('warehouse_id')
                ->constrained('warehouses');

            $table->foreignId('user_id')
                ->constrained('users');

            $table->foreignId('sucursale_id')
                ->constrained('sucursales');

            $table->date('date_emision');

            $table->integer('state')->default(1);

            $table->string('type_comprobant');

            $table->string('n_comprobant');

            $table->foreignId('provider_id')
                ->constrained('providers');

            $table->decimal('total', 12, 2)->default(0);

            $table->decimal('importe', 12, 2)->default(0);

            $table->decimal('igv', 12, 2)->default(0);

            $table->text('description')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};