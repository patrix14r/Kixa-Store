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

// Abastecimiento: selects encadenados Producto -> Talla.
// data-tallas trae { SKU: [{ id, texto }] } desde el controlador; al cambiar el producto se reconstruyen las opciones de talla.
const selectProducto = document.querySelector('[data-producto-abastecimiento]');
const selectTalla = document.getElementById('producto_talla_id');
if (selectProducto && selectTalla?.dataset.tallas) {
    const tallasPorProducto = JSON.parse(selectTalla.dataset.tallas);
    selectProducto.addEventListener('change', () => {
        const tallas = tallasPorProducto[selectProducto.value] || [];
        selectTalla.innerHTML = '';
        selectTalla.add(new Option(selectProducto.value ? (tallas.length ? 'Selecciona una talla' : 'Este producto no tiene tallas') : 'Primero elige un producto', ''));
        tallas.forEach((t) => selectTalla.add(new Option(t.texto, t.id)));
        selectTalla.disabled = tallas.length === 0;
    });
}
