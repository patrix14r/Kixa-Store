<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// [Sem 11 - Migraciones, Seeders y Roles]: tabla marcas del CRUD 4; nombre y slug únicos, logo opcional y activo para la baja lógica.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marcas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 60)->unique();
            $table->string('slug', 70)->unique();
            $table->string('logo')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marcas');
    }
};
