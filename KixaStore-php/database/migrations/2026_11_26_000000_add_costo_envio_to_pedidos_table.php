<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// [Sem 11 - Migraciones, Seeders y Roles]: Guarda el costo de envío cobrado en cada pedido (0 = envío gratis) para mostrarlo en el historial y en la boleta.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->decimal('costo_envio', 10, 2)->default(0)->after('igv');
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropColumn('costo_envio');
        });
    }
};
