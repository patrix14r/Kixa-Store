# Guía del proyecto KixaStore

Resumen de los módulos agregados para la exposición. Cada sección explica el flujo de principio a fin.

## 1. CRUD 3: Proveedores (modo Admin)

**Archivos**

| Capa | Archivo |
| --- | --- |
| Modelo | `app/Models/Proveedor.php` |
| Controlador | `app/Http/Controllers/Admin/ProveedorController.php` |
| Rutas | `routes/web.php` (grupo `admin`) |
| Menú lateral | `app/Providers/AppServiceProvider.php` (View Composer) |
| Vistas | `resources/views/admin/proveedores/` (`index`, `create`, `edit`, `_formulario`) |
| Tests | `tests/Feature/AdminCrudTest.php` (sección "Mantenimiento 3: Proveedores") |

**Modelo.** `getRouteKeyName()` devuelve `'ruc'`: la URL queda `/admin/proveedores/20512345671/edit` y no expone el ID. El scope `#[Scope] activos` filtra `activo = true`. La relación `productos()` es HasMany.

**Rutas.** `Route::resource('proveedores', ProveedorController::class)->except(['show', 'destroy'])` crea index, create, store, edit y update. `parameters(['proveedores' => 'proveedor'])` corrige el nombre del parámetro (plural irregular). `PATCH proveedores/{proveedor}/estado` (`admin.proveedores.estado`) hace la baja lógica.

**Flujo**

1. **Listar** (`index`): lee `?buscar=` con `$request->query()`, filtra por razón social o RUC con `when()`, usa `withCount('productos')` y `paginate(10)->withQueryString()`. La vista no muestra el `id`.
2. **Crear** (`create` → `store`): `create` manda un `Proveedor` vacío al parcial `_formulario`; `store` valida y guarda con `Proveedor::create()` (solo campos de `#[Fillable]`).
3. **Editar** (`edit` → `update`): Laravel resuelve el proveedor por RUC. En `update` la regla `unique` ignora el propio registro.
4. **Activar/desactivar** (`estado`): alterna `activo`. No hay `destroy` porque el proveedor tiene productos y órdenes de abastecimiento (llaves foráneas).

**Validación** (`validar()` privado, compartido por store y update): razón social obligatoria (máx. 100); RUC `digits:11` y único; contacto opcional (máx. 80); teléfono opcional `digits:9`; correo opcional con formato válido; dirección opcional (máx. 200).

**Sidebar.** El enlace "Proveedores" apunta a `admin.proveedores.index` y queda activo con `routeIs('admin.proveedores.*')`. Se eliminó la vista `admin/pendientes/proveedores.blade.php`.

## 2. Guía de tallas (modo Cliente)

Aparece en la ficha del producto (`/producto/{sku}`), justo debajo de los botones de talla ("Selecciona tu talla"), como el enlace **Guía de tallas**. Al hacer clic se abre un modal con la tabla de equivalencias (talla US/Perú, EU y largo del pie en cm) y una nota de cómo medir el pie.

| Pieza | Dónde está |
| --- | --- |
| Datos de la tabla (`$guiaTallas`) | `CatalogoController@show` (la lógica va en el controlador) |
| Botón `#abrirGuiaTallas` y modal `#modalGuiaTallas` | `resources/views/cliente/producto.blade.php` |
| Estilos | Clases de Tailwind en la misma vista (el estado `abierto` usa la variante `[&.abierto]:opacity-100`) |
| Abrir/cerrar | `public/js/guia-tallas.js` |

**Flujo:** clic en el enlace → se quita `hidden` y se agrega la clase `abierto` (animación de opacidad) → se cierra con la X, haciendo clic en el fondo o con la tecla Escape. Usa atributos propios (`data-cerrar-guia`), así no interfiere con el modal del carrito (`#modalCarrito`, `data-cerrar-modal`) de `producto-dinamico.js`.

## 3. CRUD 4: Marcas (modo Admin)

| Capa | Archivo |
| --- | --- |
| Migraciones | `2026_11_22_000000_create_marcas_table.php` (nombre y slug únicos, logo nullable, activo) y `2026_11_22_000100_add_marca_id_to_productos_table.php` (`marca_id` nullable, `constrained('marcas')->restrictOnDelete()`) |
| Modelo | `app/Models/Marca.php` (`#[Fillable]`, `getRouteKeyName()` = `slug`, scope `activos`, accessor `url_logo`, `productos()` HasMany) |
| Seeder | `database/seeders/MarcaSeeder.php` (Nike, Jordan, Adidas, Puma, Vans, New Balance, DC Shoes, Lacoste, Umbro), corre antes de `ProductoSeeder` |
| Controlador | `app/Http/Controllers/Admin/MarcaController.php` |
| Vistas | `resources/views/admin/marcas/` (`index`, `create`, `edit`, `_formulario`) |
| Rutas | `Route::resource('marcas', ...)->except(['show', 'destroy'])` + `PATCH marcas/{marca}/estado` (nombres `admin.marcas.*`) |

**Marca ≠ Proveedor.** La marca es quien fabrica la zapatilla (Nike, Adidas…); el proveedor es la distribuidora mayorista local a quien se le compra (p. ej. "Distribuidora Deportiva Perú S.A.C.").

**Relación con productos.** `Producto::marca()` es un `BelongsTo` por `marca_id`. La columna de texto `productos.marca` **se conserva** porque el catálogo del cliente filtra por ella; `ProductoController` la sincroniza con el nombre de la marca elegida al guardar, y `MarcaController@update` la actualiza en todos sus productos si se renombra la marca. Por eso `$producto->marca` (sin paréntesis) devuelve el texto y `$producto->marca()` la relación.

**Flujo**
1. **Listar** (`index`): buscador por nombre (`?buscar=`), `withCount('productos')` y `paginate(10)->withQueryString()`. Columnas: logo, marca, slug, productos y estado (sin ID).
2. **Crear / editar** (`create`/`store`, `edit`/`update`): nombre único; el slug se genera con `Str::slug()`; logo opcional (imagen ≤ 2 MB) guardado en `public/images/brands/`. La URL de edición usa el slug: `/admin/marcas/new-balance/edit`.
3. **Baja lógica** (`estado`): alterna `activo`. Una marca inactiva deja de aparecer en el `<select name="marca_id">` del formulario de productos (salvo para el producto que ya la tenía).

**Stock por talla.** La tabla `producto_tallas` se mantiene intacta: el stock se edita desde el formulario de Productos y se repone con Abastecimiento. El umbral de stock crítico es `ProductoTalla::STOCK_CRITICO` (2).

## 4. Abastecimiento (proceso secundario, modo Admin)

| Capa | Archivo |
| --- | --- |
| Controlador | `app/Http/Controllers/Admin/AbastecimientoController.php` |
| Modelo | `app/Models/OrdenAbastecimiento.php` (binding por `codigo_lote`) |
| Vistas | `resources/views/admin/abastecimiento/` (`index`, `create`) |
| Migración | `2026_11_11_044423_create_orden_abastecimientos_table.php` (estados `Pendiente`, `Recibido`, `Cancelado`) |

**Flujo**
1. **Listar** (`index`): órdenes con lote, proveedor, producto + talla, cantidad, costo total y estado; arriba, el resumen de tallas con stock crítico (2 o menos).
2. **Crear** (`create` → `store`): el lote se genera solo (`LOT-0001`, `LOT-0002`...). Se elige un proveedor **activo** (`Proveedor::activos()`), luego el producto y la talla con **dos selects encadenados**: el 1.º lista los productos (value = SKU) y el 2.º se llena con JS (`resources/js/app.js`) solo con las tallas de ese producto y su stock ("Talla 43 — Stock: 0 (Agotado)"), a partir del mapa `data-tallas` que arma el controlador. El botón **+ Reponer** (`?producto={sku}&talla={n}`) llega con ambos selects preseleccionados. Después la cantidad y el costo unitario. `costo_total = cantidad × costo_unitario`. Se guarda con el `user_id` del admin y estado `Pendiente`.
3. **Recibir** (`PATCH /admin/abastecimiento/{orden}/recibir`): dentro de `DB::transaction()` (con `lockForUpdate`) cambia a `Recibido`, guarda `fecha_recepcion = now()` y hace `increment('stock', cantidad_solicitada)` en `producto_tallas`. Si la orden ya no está pendiente no suma nada (evita duplicar stock).
4. **Cancelar** (`PATCH .../cancelar`): solo órdenes pendientes; no toca el stock.

## 5. Dashboard (`/admin`)

Estructura en 3 bloques (`admin/home.blade.php`), con datos calculados en `AdminController@home`:
1. **Superior (7/5 columnas):** KPI `Clientes` y `Pedidos` (badge con pedidos pendientes), gráfico de barras **Productos por Marca** (`Marca::activos()->withCount('productos')`, variable `$productosPorMarca`) y la tarjeta **Disponibilidad de Stock** (% de tallas con stock + `Ventas (S/)` sin cancelados, `Productos` y tallas `Agotadas`).
2. **Medio (ancho completo):** área **Estadísticas de Ingresos (S/)**; `$grafico` trae los ingresos diarios de los últimos **30 días** (sin cancelados) y las pestañas `7 días | 30 días` recortan el rango en el navegador.
3. **Inferior (5/7 columnas):** **Alertas de Stock Crítico** (las 5 más urgentes, `stock <= ProductoTalla::STOCK_CRITICO`, filtro `?marca=` en el servidor y botón `+ Reponer`) y **Pedidos recientes** (`Pedido::with(['usuario', 'items.producto'])`, miniatura del primer producto).
- Gráficos: Chart.js 4 por CDN (`@push('scripts')`); dibujo, colores oscuros y pestañas en `public/js/admin-grafico.js` (`<canvas id="graficoMarcas">` y `<canvas id="graficoVentas">`).

## 6. Limpieza previa (resumen)

- Controladores Admin sin "Crud" en el nombre (`CategoriaController`, `ProductoController`, `ProveedorController`).
- `NosotrosController` invocable en `/nosotros`; la sección "Ubícanos" (mapa de la sede) está en `informativas/nosotros.blade.php`. La ruta `/contacto` ya no existe.
- `auth/_portada.blade.php` reemplaza a `_showcase`.
- `CatalogoController@show` solo muestra productos activos y busca únicamente por `sku`.

## 7. Estilos: Tailwind CSS v4 en Cliente y Admin

Todo el proyecto usa **un solo Tailwind CSS v4 compilado con Vite**: `resources/css/app.css` + `resources/js/app.js`, cargados con `@vite([...])` en `layouts/app.blade.php` (Cliente) y `layouts/admin.blade.php` (Admin). Ya no existe la carpeta `public/css/`.

| Pieza | Dónde está |
| --- | --- |
| Tema del cliente (colores `tinta`, `suave`, `perla`, `carbon`, `exito`; fuente `font-jakarta`; `--spacing-navbar` = 76px; breakpoint `escritorio` = 769px; animación `animate-sacudir`) | `@theme` en `resources/css/app.css` |
| Clases del Admin (`kx-card`, `kx-input`, `kx-btn-primary`…) | primer `@layer components` de `app.css` (sin cambios) |
| Estados que cambia JavaScript en el cliente (carrusel 3D `.kx-carrusel .slide.active…` y drawer de filtros `.kx-catalogo-cuerpo.filtros-ocultos` / `.drawer-abierto`) | segundo `@layer components` de `app.css` |
| Íconos SVG reutilizables (flecha de los `<select>`, check de los filtros) | variables `--kx-icono-flecha` y `--kx-icono-check`, usadas con `bg-(image:--kx-icono-flecha)` |
| Todo lo demás del cliente (header, footer, catálogo, ficha, carrito, checkout, perfil, login, nosotros) | utilidades de Tailwind directamente en cada vista Blade |
| HTML que arma JavaScript (filas del carrito, favoritos, formularios de pago, toasts) | utilidades de Tailwind dentro de `public/js/*.js` (Tailwind los escanea con `@source '../../public/js/*.js'`) |

**Patrones usados en las vistas del cliente:**
- **Responsive:** `max-escritorio:` (teléfonos, hasta 768px) y valores puntuales como `max-[900px]:` o `max-[1100px]:`.
- **Estados por clase de JS:** `[&.abierto]:visible`, `[&.selected]:bg-tinta`, `group-[.editando]:block`, `data-visible:opacity-100` (toasts).
- **Hijos con el mismo estilo:** variantes como `[&>a]:...` o `[&_input]:...` en el contenedor (menú móvil, footer, formularios).
- **Componentes Blade nuevos:** `<x-filtro-catalogo>` (acordeón de filtros del catálogo) y `<x-pregunta-frecuente>` (acordeón del Centro de Ayuda).
- **Imágenes de fondo locales:** Blade pasa la URL con `asset()` en una variable CSS (`style="--fondo: url(...)"`) y la clase usa `var(--fondo)`; así Vite no reescribe la ruta.
- Se conservan todos los `id`, `data-*` y clases que usan los scripts (`.slide`, `.dot`, `.size-btn`, `.btn-fav-card`, `.kx-fav-fila`, `.auth-campo`, `#kx-toasts`, etc.).

## Autenticación con Laravel Fortify

Login, registro y logout los maneja **Laravel Fortify** (backend sin vistas propias); el diseño sigue siendo el de `resources/views/auth/login.blade.php` y `registro.blade.php`.

- **Rutas (las crea Fortify):** `GET/POST /login`, `GET/POST /register`, `POST /logout`. El antiguo `/registro` redirige a `/register`. Nombres: `login`, `register`, `logout`.
- **`FortifyServiceProvider`:** conecta las vistas con `Fortify::loginView()` y `Fortify::registerView()`, registra `CreateNewUser` y cambia las respuestas por las nuestras. Está registrado en `bootstrap/providers.php`.
- **`CreateNewUser`** (`app/Actions/Fortify/`): valida (mensajes en español) y crea el usuario con el `rol_id` del rol `cliente`.
- **`LoginResponse` y `RegisterResponse`** (`app/Http/Responses/`): si `esAdmin()` va a `admin.home`; si es cliente respeta `?redirect=checkout|carrito` o vuelve a la URL prevista (`intended`) o al home. El trait `RespuestaAutenticacion` comparte esa lógica.
- **`config/fortify.php`:** solo la característica `registration`; no se usan recuperar contraseña, 2FA ni passkeys.
- **`AuthController`:** quedó solo con el perfil del cliente (`perfil`, `actualizarPerfil`, `actualizarPassword`, `actualizarDireccion`).
- El límite de intentos de login (5 por minuto) está en el `RateLimiter` del provider.
