{{-- Fila del lote (se repite por talla): Producto (value = SKU) -> Talla (value = id interno de producto_tallas, nunca visible) + cantidad y costo.
     En la <template> el índice llega como __I__ y app.js lo reemplaza al pulsar "+ Agregar otra talla". --}}
<tr class="kx-row align-top" data-fila-lote>
    <td class="kx-td min-w-[280px]">
        <select name="items[{{ $indice }}][producto]" class="kx-input" required data-producto-abastecimiento aria-label="Producto">
            <option value="">Selecciona un producto</option>
            {{-- Texto "Marca — Modelo (SKU)" para ubicar rápido cada modelo; el accessor modelo quita la marca repetida del nombre
                 ("Adidas Campus 00s" -> "Campus 00s") y el value es el SKU, nunca el ID --}}
            @foreach ($productos as $producto)
                <option value="{{ $producto->sku }}" @selected($fila['producto'] === $producto->sku)>{{ $producto->marca ?: 'Sin marca' }} — {{ $producto->modelo }} ({{ $producto->sku }})</option>
            @endforeach
        </select>
    </td>
    <td class="kx-td min-w-[210px]">
        <select name="items[{{ $indice }}][producto_talla_id]" class="kx-input" required data-talla-abastecimiento aria-label="Talla" @disabled(count($fila['tallas']) === 0)>
            <option value="">{{ $fila['producto'] ? 'Selecciona una talla' : 'Primero elige un producto' }}</option>
            @foreach ($fila['tallas'] as $talla)
                <option value="{{ $talla['id'] }}" @selected($fila['producto_talla_id'] === $talla['id'])>{{ $talla['texto'] }}</option>
            @endforeach
        </select>
        @error("items.$indice.producto_talla_id") <p class="kx-error">{{ $message }}</p> @enderror
        {{-- Aviso de talla repetida: app.js lo muestra si esta talla ya está elegida en otra fila del lote --}}
        <p class="kx-error hidden" data-aviso-duplicado role="alert">Esta talla ya está en otra fila del lote: suma las cantidades ahí.</p>
    </td>
    <td class="kx-td w-28">
        <input type="number" name="items[{{ $indice }}][cantidad]" value="{{ $fila['cantidad'] }}" class="kx-input" required min="1" max="10000" placeholder="0" data-cantidad aria-label="Cantidad">
        @error("items.$indice.cantidad") <p class="kx-error">{{ $message }}</p> @enderror
    </td>
    <td class="kx-td w-32">
        <input type="number" name="items[{{ $indice }}][costo_unitario]" value="{{ $fila['costo_unitario'] }}" class="kx-input" required min="0.01" max="99999.99" step="0.01" placeholder="0.00" data-costo aria-label="Costo unitario">
        @error("items.$indice.costo_unitario") <p class="kx-error">{{ $message }}</p> @enderror
    </td>
    {{-- Precio de venta, subtotal y ganancia los calcula app.js en vivo con el precio que trae data-catalogo --}}
    <td class="kx-td whitespace-nowrap pt-5 kx-muted" data-precio-venta>—</td>
    <td class="kx-td whitespace-nowrap pt-5" data-subtotal>S/ 0.00</td>
    <td class="kx-td whitespace-nowrap pt-5" data-ganancia>S/ 0.00</td>
    <td class="kx-td text-right">
        <button type="button" class="kx-btn-ghost px-2.5 py-2 disabled:cursor-not-allowed disabled:opacity-40" data-quitar-fila aria-label="Quitar esta talla del lote" title="Quitar fila"><i class="fa-solid fa-trash-can"></i></button>
    </td>
</tr>
