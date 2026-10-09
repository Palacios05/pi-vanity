<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('purchase_id')
                ->constrained('purchases');

            $table->foreignId('product_id')
                ->constrained('products');

            $table->foreignId('unit_id')
                ->constrained('units');

            $table->decimal('price_unit', 12, 2)->default(0);

            $table->decimal('total', 12, 2)->default(0);

            $table->decimal('quantity', 12, 2)->default(0);

            $table->integer('state')->default(1);

            $table->foreignId('user_entrega')
                ->nullable()
                ->constrained('users');

            $table->timestamp('date_entrega')->nullable();

            $table->text('description')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_details');
    }
};