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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('client_id')
                ->constrained('clients')
                ->cascadeOnDelete();

            $table->string('type_client')->nullable();

            $table->foreignId('sucursale_id')
                ->constrained('sucursales')
                ->cascadeOnDelete();

            $table->decimal('subtotal',12,2)->default(0);
            $table->decimal('total',12,2)->default(0);
            $table->decimal('igv',12,2)->default(0);

            // 1 = Venta, 2 = Cotización
            $table->integer('state_sale')->default(2);

            // 1 = Pendiente, 2 = Parcial, 3 = Pagado
            $table->integer('state_payment')->default(1);

            $table->decimal('debt',12,2)->default(0);
            $table->decimal('paid_out',12,2)->default(0);

            $table->timestamp('date_validation')->nullable();
            $table->timestamp('date_pay_complete')->nullable();

            $table->text('description')->nullable();

            // 1 = Pendiente, 2 = Parcial, 3 = Entregado
            $table->integer('state_entrega')->default(1);

            $table->decimal('discount',12,2)->default(0);

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};