// Panel admin. Este archivo también se carga en el Modo Cliente (layouts/app.blade.php):
// cada bloque comprueba primero que su elemento exista (?. o if), así no falla en páginas sin esos elementos.

// Menús desplegables (campanita y perfil): se cierran al hacer clic fuera
document.querySelectorAll('[data-dropdown]').forEach((caja) => {
    const menu = caja.querySelector('[data-dropdown-menu]');
    caja.querySelector('[data-dropdown-boton]').addEventListener('click', (e) => {
        e.stopPropagation();
        document.querySelectorAll('[data-dropdown-menu]').forEach((m) => m !== menu && m.classList.add('hidden'));
        menu.classList.toggle('hidden');
    });
    menu.addEventListener('click', (e) => e.stopPropagation());
});
document.addEventListener('click', () => {
    document.querySelectorAll('[data-dropdown-menu]').forEach((m) => m.classList.add('hidden'));
});

// ⌘K / Ctrl+K enfoca el buscador global
document.addEventListener('keydown', (e) => {
    if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
        const buscador = document.getElementById('buscador-global');
        if (buscador) {
            e.preventDefault();
            buscador.focus();
        }
    }
});

// Sidebar en móvil
const sidebar = document.getElementById('sidebar');
const fondo = document.getElementById('sidebar-fondo');
const alternarSidebar = () => {
    sidebar?.classList.toggle('-translate-x-full');
    fondo?.classList.toggle('hidden');
};
document.getElementById('btn-sidebar')?.addEventListener('click', alternarSidebar);
fondo?.addEventListener('click', alternarSidebar);

// Formulario de producto: agregar / quitar filas de tallas
const contenedorTallas = document.getElementById('tallas-filas');
const plantillaTalla = document.getElementById('plantilla-talla');
document.getElementById('btn-agregar-talla')?.addEventListener('click', () => {
    const indice = Date.now();
    contenedorTallas.insertAdjacentHTML('beforeend', plantillaTalla.innerHTML.replaceAll('__I__', indice));
});
contenedorTallas?.addEventListener('click', (e) => {
    const boton = e.target.closest('[data-quitar-talla]');
    if (boton) boton.closest('[data-fila-talla]').remove();
});

// [FASE 3.5]: Selector de imagen 100% en español: muestra el nombre del archivo y la vista previa
document.getElementById('imagen-input')?.addEventListener('change', (e) => {
    const archivo = e.target.files[0];
    const nombre = document.getElementById('imagen-nombre');
    const vista = document.getElementById('vista-imagen');
    if (nombre) nombre.textContent = archivo ? archivo.name : 'Ningún archivo seleccionado';
    if (archivo && vista) {
        vista.src = URL.createObjectURL(archivo);
        vista.hidden = false; // en "crear marca" la <img> arranca oculta con el atributo hidden
        document.getElementById('vista-imagen-caja')?.classList.remove('hidden');
    }
});

// El aviso flotante se oculta a los 4 segundos o con la X
document.querySelectorAll('[data-toast]').forEach((toast) => {
    const cerrar = () => {
        toast.classList.add('opacity-0', 'translate-x-4');
        setTimeout(() => toast.remove(), 300);
    };
    toast.querySelector('[data-toast-cerrar]')?.addEventListener('click', cerrar);
    setTimeout(cerrar, 4000);
});

// El slug de la categoría se arma en vivo desde el nombre
const aSlug = (texto) => texto.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase()
    .replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
const campoSlug = document.querySelector('[data-slug-auto]');
if (campoSlug) {
    const origen = document.getElementById(campoSlug.dataset.slugAuto);
    let manual = campoSlug.value !== '' && campoSlug.value !== aSlug(origen.value);
    campoSlug.addEventListener('input', () => { manual = campoSlug.value !== ''; });
    origen.addEventListener('input', () => { if (!manual) campoSlug.value = aSlug(origen.value); });
}

// MARCA(3)-MODELO(3)-SUFIJO(3), p. ej. ADI-CAM-00S
const campoSku = document.getElementById('sku');
if (campoSku && campoSku.hasAttribute('data-sku-auto')) {
    const marca = document.getElementById('marca');
    const nombre = document.getElementById('nombre');
    const parte = (texto, n) => aSlug(texto).replace(/-/g, '').toUpperCase().slice(0, n).padEnd(n, 'X');
    // La marca es un <select>: el texto sale del data-nombre de la opción elegida (el value es la FK).
    const textoMarca = () => marca.selectedOptions?.[0]?.dataset.nombre || '';
    const generar = () => {
        if (!textoMarca() || !nombre.value.trim()) return;
        const palabras = aSlug(nombre.value).split('-').filter(Boolean);
        const sufijo = (palabras.slice(1).join('') + Math.random().toString(36).slice(2)).toUpperCase().slice(-3);
        campoSku.value = `${parte(textoMarca(), 3)}-${parte(palabras[0] || nombre.value, 3)}-${sufijo}`;
    };
    let skuManual = campoSku.value !== '';
    campoSku.addEventListener('input', () => { skuManual = campoSku.value !== ''; });
    [marca, nombre].forEach((c) => c.addEventListener(c === marca ? 'change' : 'input', () => { if (!skuManual) generar(); }));
    document.getElementById('btn-autogenerar-sku')?.addEventListener('click', () => { skuManual = false; generar(); });
}

// Muestra cuántas imágenes secundarias se eligieron
document.getElementById('galeria-input')?.addEventListener('change', (e) => {
    const n = e.target.files.length;
    document.getElementById('galeria-nombre').textContent = n ? `${n} imagen(es) seleccionada(s)` : 'Ninguna imagen secundaria';
});

// Modal con los datos del producto desde la tabla del admin
const modalVista = document.getElementById('modal-vista');
document.querySelectorAll('[data-vista-rapida]').forEach((boton) => {
    boton.addEventListener('click', () => {
        const datos = JSON.parse(boton.dataset.vistaRapida);
        Object.entries(datos).forEach(([clave, valor]) => {
            const destino = modalVista.querySelector(`[data-campo="${clave}"]`);
            if (!destino) return;
            if (destino.tagName === 'IMG') destino.src = valor;
            else if (destino.tagName === 'A') destino.href = valor;
            else destino.textContent = valor;
        });
        modalVista.classList.remove('hidden');
        modalVista.classList.add('flex');
    });
});
modalVista?.addEventListener('click', (e) => {
    if (e.target === modalVista || e.target.closest('[data-cerrar-modal]')) {
        modalVista.classList.add('hidden');
        modalVista.classList.remove('flex');
    }
});

// Abastecimiento: lote con varias filas (Producto -> Talla + cantidad + costo) y resumen en vivo de inversión y ganancia estimada.
// data-catalogo trae { SKU: { precio, tallas: [{ id, texto }] } } desde AbastecimientoController@create.
// [Sem 09 - Controladores y Middleware]: flujo Entrada -> Proceso -> Salida: este JS solo ayuda en la ENTRADA (armar el lote);
// el PROCESO (suma atómica del stock con DB::transaction + lockForUpdate) y la SALIDA (Guía PDF con dompdf) ocurren en el servidor.
const formLote = document.querySelector('[data-form-lote]');
if (formLote) {
    const catalogo = JSON.parse(formLote.dataset.catalogo || '{}');
    const filasLote = document.getElementById('lote-filas');
    const plantillaLote = document.getElementById('plantilla-fila-lote');
    const soles = (n) => 'S/ ' + Number(n).toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    // Selects encadenados: al cambiar el producto de una fila se reconstruyen solo SUS opciones de talla
    const llenarTallas = (fila) => {
        const sku = fila.querySelector('[data-producto-abastecimiento]').value;
        const selectTalla = fila.querySelector('[data-talla-abastecimiento]');
        const tallas = catalogo[sku]?.tallas || [];
        selectTalla.innerHTML = '';
        selectTalla.add(new Option(sku ? (tallas.length ? 'Selecciona una talla' : 'Este producto no tiene tallas') : 'Primero elige un producto', ''));
        tallas.forEach((t) => selectTalla.add(new Option(t.texto, t.id)));
        selectTalla.disabled = tallas.length === 0;
    };

    // Subtotal = cantidad × costo; ganancia = (precio de venta − costo) × cantidad; los totales suman todas las filas
    const recalcularLote = () => {
        let unidades = 0;
        let inversion = 0;
        let ganancia = 0;
        const filas = filasLote.querySelectorAll('[data-fila-lote]');

        filas.forEach((fila) => {
            const precio = catalogo[fila.querySelector('[data-producto-abastecimiento]').value]?.precio ?? null;
            const cantidad = Math.max(0, parseInt(fila.querySelector('[data-cantidad]').value, 10) || 0);
            const costo = Math.max(0, parseFloat(fila.querySelector('[data-costo]').value) || 0);
            const gananciaFila = precio === null ? 0 : (precio - costo) * cantidad;

            fila.querySelector('[data-precio-venta]').textContent = precio === null ? '—' : soles(precio);
            fila.querySelector('[data-subtotal]').textContent = soles(cantidad * costo);
            const celda = fila.querySelector('[data-ganancia]');
            celda.textContent = soles(gananciaFila);
            celda.classList.toggle('text-emerald-400', gananciaFila > 0);
            celda.classList.toggle('text-amber-400', gananciaFila < 0);
            // Con una sola fila no se puede quitar (el lote necesita al menos una talla)
            fila.querySelector('[data-quitar-fila]').disabled = filas.length === 1;

            unidades += cantidad;
            inversion += cantidad * costo;
            ganancia += gananciaFila;
        });

        document.querySelector('[data-total-unidades]').textContent = unidades;
        document.querySelector('[data-total-inversion]').textContent = soles(inversion);
        const total = document.querySelector('[data-total-ganancia]');
        total.textContent = soles(ganancia);
        total.classList.toggle('text-emerald-400', ganancia > 0);
        total.classList.toggle('text-amber-400', ganancia < 0);
    };

    // Tallas (producto_talla_id) ya elegidas en las demás filas del lote
    const tallasUsadas = (excepto) => [...filasLote.querySelectorAll('[data-fila-lote]')]
        .filter((fila) => fila !== excepto)
        .map((fila) => fila.querySelector('[data-talla-abastecimiento]').value)
        .filter(Boolean);

    // Evita tallas duplicadas en el mismo lote: marca las filas repetidas y muestra el aviso (el servidor lo vuelve a validar con distinct)
    const marcarDuplicados = () => {
        let hayDuplicados = false;
        filasLote.querySelectorAll('[data-fila-lote]').forEach((fila) => {
            const select = fila.querySelector('[data-talla-abastecimiento]');
            const repetida = select.value !== '' && tallasUsadas(fila).includes(select.value);
            fila.querySelector('[data-aviso-duplicado]').classList.toggle('hidden', !repetida);
            select.classList.toggle('border-agotado', repetida);
            hayDuplicados ||= repetida;
        });
        return hayDuplicados;
    };

    // "+ Agregar otra talla": copia la <template> con un índice único (Date.now()) en lugar de __I__.
    // Si la última fila ya tiene producto, la nueva fila lo repite y preselecciona la siguiente talla libre del lote.
    document.querySelector('[data-agregar-fila]')?.addEventListener('click', () => {
        const anterior = filasLote.lastElementChild;
        filasLote.insertAdjacentHTML('beforeend', plantillaLote.innerHTML.replaceAll('__I__', Date.now()));
        const nueva = filasLote.lastElementChild;
        const sku = anterior?.querySelector('[data-producto-abastecimiento]').value;

        if (sku && catalogo[sku]) {
            nueva.querySelector('[data-producto-abastecimiento]').value = sku;
            llenarTallas(nueva);
            // Siguiente talla después de la elegida en la fila anterior que no esté usada; si no hay, la primera libre
            const tallas = catalogo[sku].tallas.map((t) => String(t.id));
            const usadas = tallasUsadas(nueva);
            const desde = tallas.indexOf(anterior.querySelector('[data-talla-abastecimiento]').value) + 1;
            const libre = [...tallas.slice(desde), ...tallas.slice(0, desde)].find((id) => !usadas.includes(id));
            if (libre) nueva.querySelector('[data-talla-abastecimiento]').value = libre;
            nueva.querySelector('[data-cantidad]').focus();
        } else {
            nueva.querySelector('select')?.focus();
        }
        marcarDuplicados();
        recalcularLote();
    });

    filasLote.addEventListener('change', (e) => {
        if (e.target.matches('[data-producto-abastecimiento]')) llenarTallas(e.target.closest('[data-fila-lote]'));
        marcarDuplicados();
        recalcularLote();
    });

    // Si quedan tallas repetidas no se envía el formulario y se enfoca la primera fila con problema
    formLote.addEventListener('submit', (e) => {
        if (marcarDuplicados()) {
            e.preventDefault();
            filasLote.querySelector('[data-aviso-duplicado]:not(.hidden)')?.closest('[data-fila-lote]')
                .querySelector('[data-talla-abastecimiento]').focus();
        }
    });
    filasLote.addEventListener('input', recalcularLote);
    filasLote.addEventListener('click', (e) => {
        const boton = e.target.closest('[data-quitar-fila]');
        if (boton && filasLote.querySelectorAll('[data-fila-lote]').length > 1) {
            boton.closest('[data-fila-lote]').remove();
            marcarDuplicados();
            recalcularLote();
        }
    });
    recalcularLote();
}

// Formularios con data-confirmar (p. ej. "Confirmar recepción" de un lote): piden confirmación antes de enviarse
document.querySelectorAll('form[data-confirmar]').forEach((form) => {
    form.addEventListener('submit', (e) => {
        if (!window.confirm(form.dataset.confirmar)) e.preventDefault();
    });
});
