<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ordenes_abastecimiento', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_lote', 20)->unique();
            $table->foreignId('proveedor_id')->constrained('proveedores')->restrictOnDelete();
            $table->foreignId('producto_talla_id')->constrained('producto_tallas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->integer('cantidad_solicitada');
            $table->decimal('costo_unitario', 10, 2);
            $table->decimal('costo_total', 10, 2);
            $table->enum('estado', ['Solicitado', 'Recibido', 'Cancelado'])->default('Solicitado');
            $table->timestamp('fecha_recepcion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ordenes_abastecimiento');
    }
};