<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// [Sem 11 - Migraciones, Seeders y Roles]: un lote de abastecimiento ahora tiene varias filas (talla + cantidad + costo unitario).
// 1) Crea la tabla detalle, 2) copia la única línea de cada orden ya registrada y 3) quita esas columnas del encabezado.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalles_abastecimiento', function (Blueprint $table) {
            $table->id();
            // Al borrar el lote se borran sus filas; si un producto elimina una talla, su fila de historial también se va (igual que antes).
            $table->foreignId('orden_abastecimiento_id')->constrained('ordenes_abastecimiento')->cascadeOnDelete();
            $table->foreignId('producto_talla_id')->constrained('producto_tallas')->cascadeOnDelete();
            $table->unsignedInteger('cantidad');
            $table->decimal('costo_unitario', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->timestamps();

            // Una misma talla no se repite dentro del mismo lote (se suma en una sola fila).
            $table->unique(['orden_abastecimiento_id', 'producto_talla_id'], 'detalle_lote_talla_unico');
        });

        foreach (DB::table('ordenes_abastecimiento')->orderBy('id')->get() as $orden) {
            DB::table('detalles_abastecimiento')->insert([
                'orden_abastecimiento_id' => $orden->id,
                'producto_talla_id'       => $orden->producto_talla_id,
                'cantidad'                => $orden->cantidad_solicitada,
                'costo_unitario'          => $orden->costo_unitario,
                'subtotal'                => $orden->costo_total,
                'created_at'              => $orden->created_at,
                'updated_at'              => $orden->updated_at,
            ]);
        }

        Schema::table('ordenes_abastecimiento', function (Blueprint $table) {
            $table->dropConstrainedForeignId('producto_talla_id');
            $table->dropColumn(['cantidad_solicitada', 'costo_unitario']);
        });
    }

    public function down(): void
    {
        Schema::table('ordenes_abastecimiento', function (Blueprint $table) {
            $table->foreignId('producto_talla_id')->nullable()->after('proveedor_id')->constrained('producto_tallas')->cascadeOnDelete();
            $table->integer('cantidad_solicitada')->default(0)->after('user_id');
            $table->decimal('costo_unitario', 10, 2)->default(0)->after('cantidad_solicitada');
        });

        // Vuelve al formato de una línea por orden con la primera fila de cada lote.
        foreach (DB::table('detalles_abastecimiento')->orderBy('id')->get()->unique('orden_abastecimiento_id') as $detalle) {
            DB::table('ordenes_abastecimiento')->where('id', $detalle->orden_abastecimiento_id)->update([
                'producto_talla_id'   => $detalle->producto_talla_id,
                'cantidad_solicitada' => $detalle->cantidad,
                'costo_unitario'      => $detalle->costo_unitario,
            ]);
        }

        Schema::dropIfExists('detalles_abastecimiento');
    }
};
