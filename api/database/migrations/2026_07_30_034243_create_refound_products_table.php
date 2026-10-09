<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refound_products', function (Blueprint $table) {

            $table->id();

            $table->foreignId('product_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('unit_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('warehouse_id')
                ->constrained()
                ->restrictOnDelete();

            $table->decimal('quantity',12,2);

            $table->foreignId('sale_detail_id')
                ->constrained('sale_details')
                ->cascadeOnDelete();

            $table->foreignId('client_id')
                ->constrained()
                ->restrictOnDelete();

            $table->string('type');

            $table->integer('state')->default(1);

            $table->boolean('state_clone')->default(false);

            $table->text('description')->nullable();

            $table->foreignId('user_id')
                ->constrained()
                ->restrictOnDelete();

            $table->timestamp('resolution_date')->nullable();

            $table->text('description_resolution')->nullable();

            $table->timestamps();
            $table->softDeletes();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refound_products');
    }
};