<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('product_categorie_id')->constrained('product_categories')->restrictOnDelete();
            $table->foreignId('unit_id')->constrained()->restrictOnDelete();
            $table->foreignId('warehouse_id')->constrained()->restrictOnDelete();

            $table->decimal('quantity',12,2)->default(0);
            $table->decimal('price_unit',12,2)->default(0);
            $table->decimal('discount',12,2)->default(0);
            $table->decimal('subtotal',12,2)->default(0);
            $table->decimal('igv',12,2)->default(0);
            $table->decimal('total',12,2)->default(0);

            $table->text('description')->nullable();

            // 1 Pendiente
            // 2 Parcial
            // 3 Completo
            $table->integer('state_attention')->default(1);

            $table->decimal('quantity_pending',12,2)->default(0);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_details');
    }
};