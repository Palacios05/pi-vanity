<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->string('sku')->unique();
            $table->string('imagen')->nullable();

            $table->foreignId('product_categorie_id')
                ->constrained('product_categories')
                ->restrictOnDelete();

            $table->decimal('price_general', 12, 2)->default(0);
            $table->decimal('price_company', 12, 2)->default(0);

            $table->text('description')->nullable();

            $table->boolean('is_discount')->default(false);
            $table->decimal('max_discount', 12, 2)->default(0);

            $table->boolean('is_gift')->default(false);

            $table->boolean('disponibilidad')->default(true);
            $table->boolean('state')->default(true);
            $table->boolean('state_stock')->default(true);

            $table->integer('warranty_day')->default(0);

            $table->boolean('tax_selected')->default(false);
            $table->decimal('importe_iva', 12, 2)->default(0);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};