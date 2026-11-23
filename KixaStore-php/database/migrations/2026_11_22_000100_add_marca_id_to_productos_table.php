<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// [Sem 11 - Migraciones, Seeders y Roles]: FK productos.marca_id -> marcas con constrained(); restrictOnDelete impide borrar una marca que tenga productos.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->foreignId('marca_id')->nullable()->after('proveedor_id')->constrained('marcas')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('marca_id');
        });
    }
};
