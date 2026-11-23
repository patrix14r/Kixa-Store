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
    Schema::create('productos', function (Blueprint $table) {
        $table->id();
        $table->foreignId('categoria_id')->constrained('categorias')->restrictOnDelete();
        $table->foreignId('proveedor_id')->nullable()->constrained('proveedores')->nullOnDelete();
        $table->string('marca', 50);
        $table->string('nombre', 120);
        $table->string('subcategoria', 50)->nullable();
        $table->string('sku', 30)->unique();
        $table->decimal('precio', 10, 2);
        $table->text('descripcion')->nullable();
        $table->string('imagen', 255)->nullable(); 
        $table->boolean('destacado')->default(false);
        $table->boolean('en_oferta')->default(false);
         $table->boolean('activo')->default(true);
         $table->timestamps();
         });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
