<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('producto_tallas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->decimal('talla', 3, 1);
            $table->integer('stock')->default(0);
            $table->unique(['producto_id', 'talla']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('producto_tallas');
    }
};