// JS del panel admin y la tienda

// Menús desplegables del header
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

// Ctrl+K enfoca el buscador
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

// Agregar o quitar filas de tallas
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

// Vista previa de la imagen elegida
document.getElementById('imagen-input')?.addEventListener('change', (e) => {
    const archivo = e.target.files[0];
    const nombre = document.getElementById('imagen-nombre');
    const vista = document.getElementById('vista-imagen');
    const autoEnviar = e.target.hasAttribute('data-auto-enviar');
    if (nombre) nombre.textContent = archivo ? (autoEnviar ? `Subiendo: ${archivo.name}...` : archivo.name) : 'Ningún archivo seleccionado';
    if (archivo && vista) {
        vista.src = URL.createObjectURL(archivo);
        vista.hidden = false;
        vista.classList.remove('hidden');
        document.getElementById('vista-imagen-caja')?.classList.remove('hidden');
        document.getElementById('avatar-inicial')?.classList.add('hidden');
    }
    if (archivo && autoEnviar) e.target.form?.requestSubmit();
});

// Cierre automático del aviso flotante
document.querySelectorAll('[data-toast]').forEach((toast) => {
    const duracion = Number(toast.dataset.toastDuracion) || 4000;
    let temporizador;
    const cerrar = () => {
        toast.classList.add('opacity-0', 'translate-x-4');
        setTimeout(() => toast.remove(), 300);
    };
    toast.querySelector('[data-toast-cerrar]')?.addEventListener('click', cerrar);
    toast.addEventListener('mouseenter', () => clearTimeout(temporizador));
    toast.addEventListener('mouseleave', () => { temporizador = setTimeout(cerrar, 2000); });
    temporizador = setTimeout(cerrar, duracion);
});

// Quita tildes y mayúsculas
const normalizar = (texto) => texto.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();

// Slug automático desde el nombre
const aSlug = (texto) => normalizar(texto).replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
const campoSlug = document.querySelector('[data-slug-auto]');
if (campoSlug) {
    const origen = document.getElementById(campoSlug.dataset.slugAuto);
    let manual = campoSlug.value !== '' && campoSlug.value !== aSlug(origen.value);
    campoSlug.addEventListener('input', () => { manual = campoSlug.value !== ''; });
    origen.addEventListener('input', () => { if (!manual) campoSlug.value = aSlug(origen.value); });
}

// SKU automático: MARCA-MODELO-SUFIJO
const campoSku = document.getElementById('sku');
if (campoSku && campoSku.hasAttribute('data-sku-auto')) {
    const marca = document.getElementById('marca');
    const nombre = document.getElementById('nombre');
    const parte = (texto, n) => aSlug(texto).replace(/-/g, '').toUpperCase().slice(0, n).padEnd(n, 'X');
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

// Galería: suma fotos sin reemplazar
const galeriaInput = document.getElementById('galeria-input');
if (galeriaInput) {
    const maximo = Number(galeriaInput.dataset.max) || 6;
    const miniaturas = document.getElementById('galeria-miniaturas');
    const plantillaMiniatura = document.getElementById('plantilla-miniatura');
    const texto = document.getElementById('galeria-nombre');
    let seleccion = new DataTransfer();

    const cupo = () => maximo - miniaturas.querySelectorAll('[data-galeria-guardada] input:not(:checked)').length;
    const clave = (a) => `${a.name}-${a.size}-${a.lastModified}`;

    // Reconstruye la lista de archivos
    const actualizar = (archivos, aviso = '') => {
        const vistos = new Set();
        const unicos = archivos.filter((a) => !vistos.has(clave(a)) && vistos.add(clave(a)));
        const limite = Math.max(0, cupo());
        seleccion = new DataTransfer();
        unicos.slice(0, limite).forEach((a) => seleccion.items.add(a));
        galeriaInput.files = seleccion.files;

        miniaturas.querySelectorAll('[data-galeria-nueva]').forEach((caja) => {
            URL.revokeObjectURL(caja.querySelector('img').src);
            caja.remove();
        });
        [...seleccion.files].forEach((archivo, i) => {
            const caja = plantillaMiniatura.content.firstElementChild.cloneNode(true);
            const img = caja.querySelector('img');
            img.src = URL.createObjectURL(archivo);
            img.alt = archivo.name;
            caja.title = archivo.name;
            caja.querySelector('[data-quitar-nueva]').dataset.indice = i;
            miniaturas.append(caja);
        });

        const n = seleccion.files.length;
        if (unicos.length > limite) aviso = `Máximo ${maximo} imágenes secundarias: se agregaron solo ${n}.`;
        texto.textContent = aviso || (n ? `${n} imagen(es) nueva(s) · cupo libre: ${limite - n}` : 'Ninguna imagen secundaria');
        texto.classList.toggle('text-amber-400', aviso !== '');
    };

    galeriaInput.addEventListener('change', () => actualizar([...seleccion.files, ...galeriaInput.files]));

    miniaturas.addEventListener('click', (e) => {
        const boton = e.target.closest('[data-quitar-nueva]');
        if (!boton) return;
        const indice = Number(boton.dataset.indice);
        actualizar([...seleccion.files].filter((_, i) => i !== indice));
    });

    miniaturas.addEventListener('change', (e) => {
        if (e.target.matches('[data-galeria-guardada] input')) actualizar([...seleccion.files]);
    });
}

// Modal de vista rápida del producto
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

// Abastecimiento: filas del lote y resumen
const formLote = document.querySelector('[data-form-lote]');
if (formLote) {
    const catalogo = JSON.parse(formLote.dataset.catalogo || '{}');
    const filasLote = document.getElementById('lote-filas');
    const plantillaLote = document.getElementById('plantilla-fila-lote');
    const soles = (n) => 'S/ ' + Number(n).toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    // Selects encadenados producto y talla
    const llenarTallas = (fila) => {
        const sku = fila.querySelector('[data-producto-abastecimiento]').value;
        const selectTalla = fila.querySelector('[data-talla-abastecimiento]');
        const tallas = catalogo[sku]?.tallas || [];
        selectTalla.innerHTML = '';
        selectTalla.add(new Option(sku ? (tallas.length ? 'Selecciona una talla' : 'Este producto no tiene tallas') : 'Primero elige un producto', ''));
        tallas.forEach((t) => selectTalla.add(new Option(t.texto, t.id)));
        selectTalla.disabled = tallas.length === 0;
    };

    // Recalcula inversión y ganancia
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

    // Filtro rápido de productos
    const filtroProductos = document.querySelector('[data-filtro-productos]');
    const aplicarFiltro = () => {
        const termino = normalizar(filtroProductos?.value.trim() || '');
        filasLote.querySelectorAll('[data-producto-abastecimiento]').forEach((select) => {
            [...select.options].forEach((opcion) => {
                const oculta = termino !== '' && opcion.value !== '' && !opcion.selected && !normalizar(opcion.text).includes(termino);
                opcion.hidden = oculta;
                opcion.disabled = oculta;
            });
        });
    };
    filtroProductos?.addEventListener('input', aplicarFiltro);
    filtroProductos?.addEventListener('keydown', (e) => e.key === 'Enter' && e.preventDefault());

    // Tallas ya elegidas en otras filas
    const tallasUsadas = (excepto) => [...filasLote.querySelectorAll('[data-fila-lote]')]
        .filter((fila) => fila !== excepto)
        .map((fila) => fila.querySelector('[data-talla-abastecimiento]').value)
        .filter(Boolean);

    // Marca tallas repetidas en el lote
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

    // Agrega una fila nueva al lote
    document.querySelector('[data-agregar-fila]')?.addEventListener('click', () => {
        const anterior = filasLote.lastElementChild;
        filasLote.insertAdjacentHTML('beforeend', plantillaLote.innerHTML.replaceAll('__I__', Date.now()));
        const nueva = filasLote.lastElementChild;
        const sku = anterior?.querySelector('[data-producto-abastecimiento]').value;

        if (sku && catalogo[sku]) {
            nueva.querySelector('[data-producto-abastecimiento]').value = sku;
            llenarTallas(nueva);
            const tallas = catalogo[sku].tallas.map((t) => String(t.id));
            const usadas = tallasUsadas(nueva);
            const desde = tallas.indexOf(anterior.querySelector('[data-talla-abastecimiento]').value) + 1;
            const libre = [...tallas.slice(desde), ...tallas.slice(0, desde)].find((id) => !usadas.includes(id));
            if (libre) nueva.querySelector('[data-talla-abastecimiento]').value = libre;
            nueva.querySelector('[data-cantidad]').focus();
        } else {
            nueva.querySelector('select')?.focus();
        }
        aplicarFiltro();
        marcarDuplicados();
        recalcularLote();
    });

    filasLote.addEventListener('change', (e) => {
        if (e.target.matches('[data-producto-abastecimiento]')) {
            llenarTallas(e.target.closest('[data-fila-lote]'));
            aplicarFiltro();
        }
        marcarDuplicados();
        recalcularLote();
    });

    // No envía si hay tallas repetidas
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

// Pide confirmación antes de enviar
document.querySelectorAll('form[data-confirmar]').forEach((form) => {
    form.addEventListener('submit', (e) => {
        if (!window.confirm(form.dataset.confirmar)) e.preventDefault();
    });
});
