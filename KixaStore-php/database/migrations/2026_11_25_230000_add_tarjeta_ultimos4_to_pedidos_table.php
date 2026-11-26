<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// [Sem 11 - Migraciones, Seeders y Roles]: Guarda solo los últimos 4 dígitos de la tarjeta (nunca el número completo ni el CVV).
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->char('tarjeta_ultimos4', 4)->nullable()->after('metodo_pago');
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropColumn('tarjeta_ultimos4');
        });
    }
};
