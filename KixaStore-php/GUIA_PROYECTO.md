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
- **`CreateNewUser`** (`app/Actions/Fortify/`): valida (mensajes en español), crea el usuario y le asigna el rol con `$user->assignRole('cliente')` (Spatie).
- **`LoginResponse` y `RegisterResponse`** (`app/Http/Responses/`): si `esAdmin()` va a `admin.home`; si es cliente respeta `?redirect=checkout|carrito` o vuelve a la URL prevista (`intended`) o al home. El trait `RespuestaAutenticacion` comparte esa lógica.
- **`config/fortify.php`:** solo la característica `registration`; no se usan recuperar contraseña, 2FA ni passkeys.
- **`AuthController`:** quedó solo con el perfil del cliente (`perfil`, `actualizarPerfil`, `actualizarPassword`, `actualizarDireccion`).
- El límite de intentos de login (5 por minuto) está en el `RateLimiter` del provider.

## 8. Roles con Spatie Laravel Permission

Los roles están **separados de `users`** con el paquete `spatie/laravel-permission` (configuración en `config/permission.php`).

| Pieza | Dónde está |
| --- | --- |
| Migración de Spatie | `database/migrations/2026_11_25_222712_create_permission_tables.php`: crea `roles`, `permissions`, `model_has_roles`, `model_has_permissions` y `role_has_permissions` |
| Quitar el esquema antiguo | `2026_11_25_222600_eliminar_rol_id_de_users_table.php`: corre **antes** que la de Spatie; elimina la FK/columna `users.rol_id` y nuestra tabla `roles` propia (el modelo `Rol` ya no existe) |
| Modelo | `User` usa el trait `HasRoles` → `assignRole()`, `hasRole()`, `syncRoles()`, `getRoleNames()` y el scope `User::role('cliente')` |
| Seeders | `RolSeeder`: `Role::findOrCreate('admin')` y `Role::findOrCreate('cliente')`. `UserSeeder`: `User::create([...])->assignRole('admin' / 'cliente')` |
| Registro | `CreateNewUser` (Fortify) hace `$user->assignRole('cliente')` |
| Consultas | `AdminController` (KPI de clientes) y `Admin\ClienteController@index` usan `User::role('cliente')` |
| Acceso | `User::esAdmin()` = `$this->hasRole('admin')`; `AdminMiddleware` no cambió (sigue llamando a `esAdmin()`) |

**Flujo:** al registrarse, Fortify crea el usuario y Spatie inserta una fila en `model_has_roles` (`role_id`, `model_type = App\Models\User`, `model_id`). Al entrar a `/admin`, `AdminMiddleware` pregunta `esAdmin()` → `hasRole('admin')`; si es cliente lo devuelve al home. El sidebar del admin muestra el rol con `nombreRol()` (`getRoleNames()->first()`).

## 9. Simulador de pago en el Checkout

El checkout ofrece 2 métodos: **Tarjeta de Crédito/Débito** y **Contraentrega (Efectivo)** (Yape se eliminó junto con su imagen).

**Vista (`cliente/checkout.blade.php`).** Dos botones con `data-metodo` y `aria-pressed` (la opción elegida queda resaltada) y la capa `#procesandoPago` con un spinner "Procesando pago...".

**JavaScript (`public/js/carrito.js`).**
- `mostrarTarjeta()` pinta una tarjeta visual en Tailwind que se actualiza al escribir: número agrupado de 4 en 4, titular en mayúsculas, vencimiento con `/` automático y CVV; detecta **Visa** (empieza en 4) o **Mastercard** (51-55 / 2221-2720) y cambia el ícono y el color (`data-marca` + `group-data-[marca=visa]:`). Al enfocar el CVV la tarjeta gira (`rotate-y-180`, `transform-3d`, `backface-hidden`).
- `datosTarjeta()` valida antes de enviar (Luhn, `MM/AA` vigente, CVV de 3-4 dígitos) y `confirmar()` muestra "Procesando pago..." al menos 1.4 s mientras responde el servidor.

**Servidor (`CarritoController@procesar`).**
- `metodo_pago` debe ser `Tarjeta` o `Contraentrega` (`Rule::in`).
- Los campos `tarjeta_*` usan `exclude_unless:metodo_pago,Tarjeta`: solo se validan si se paga con tarjeta.
- Número: 13-19 dígitos y **algoritmo de Luhn** (`CarritoController::pasaLuhn()`: desde la derecha se duplica un dígito sí y otro no, restando 9 si pasa de 9; la suma debe ser múltiplo de 10).
- Vencimiento `MM/AA` vigente hasta el último día del mes (`tarjetaVigente()`), CVV de 3 o 4 dígitos y titular con solo letras.
- **Nunca se guarda el número completo ni el CVV**: el pedido guarda `metodo_pago = 'Tarjeta'` y `tarjeta_ultimos4` (migración `2026_11_25_230000_add_tarjeta_ultimos4_to_pedidos_table.php`). Tarjeta → pedido `Pagado`; Contraentrega → `Pendiente`.
- El historial y el panel muestran `$pedido->metodo_pago_detalle` ("Tarjeta •••• 4242").
- Tarjetas de prueba: `4242 4242 4242 4242` (Visa) y `5555 5555 5555 4444` (Mastercard), con cualquier fecha futura y CVV `123`.

**Errores en rojo.** Si un campo de tarjeta falla, `marcarErrorCampo()` le pone `border-red-500! ring-1 ring-red-500` (`aria-invalid`) y un mensaje `text-xs text-red-600 font-medium` justo debajo; `limpiarErrorCampo()` lo quita apenas se vuelve a escribir. Si el servidor responde 422, sus `errors` se marcan en los mismos campos y el mensaje sale en el banner rojo `#errorPago` sobre "Pagar ahora".

**Métodos aceptados.** `<x-logo-tarjeta marca="visa|mastercard">` dibuja los logos en SVG (opción Tarjeta del checkout) y `<x-metodos-pago>` la franja "Visa · Mastercard · Efectivo contraentrega" al pie del Resumen (carrito y checkout) y del footer; usa `@container` para pasar "Efectivo contraentrega" a otra línea cuando el espacio es angosto.

**Toasts.** `toast.js` muestra máximo 3 avisos (el más antiguo sale), no repite un mensaje visible (reinicia su temporizador de ~3.5 s y lo sacude con `animate-sacudir`) y expone `kxToastLimpiar("error")`, que `producto-dinamico.js` llama al abrir el modal "artículo agregado al carrito".

## 10. Orden de `public/images/`

| Carpeta | Contenido |
| --- | --- |
| `avatars/` | Fotos de perfil subidas por los usuarios |
| `banners/` | Banners por sección: `home/`, `hombre/`, `mujer/`, `ninos/`, `deporte/` |
| `brands/` | Logos de marcas: `nike-logo.png`, `jordan-logo.webp`, `adidas-logo.png`, `puma-logo.png`, `umbro-logo.png` (los asigna `MarcaSeeder`) y los subidos desde el CRUD 4 |
| `products/` | Fotos de productos por categoría y `uploads/` (subidas desde el CRUD 2) |
| `promotions/` | Imágenes de ofertas del home |
| `ui/` | Favicon (`favicon-32x32.png`, enlazado en `layouts/app` y `layouts/admin`), fondos (`backgrounds/`) y recursos de interfaz |

Se eliminaron archivos sin uso: la carpeta `lanzamientos/` y `payments/yape.jpg`. El fondo de login/registro se movió de `banners/fondo-contacto.jpg` a `ui/backgrounds/portada-auth.jpg` (lo usa `auth/_portada.blade.php`).

Carrito, Checkout y Favoritos comparten un contenedor centrado de 1080px (`mx-auto max-w-[1080px]`); en Carrito y Checkout las 2 columnas (contenido + Resumen de 340px) usan `gap-10 lg:gap-12`.

En `admin/marcas/index` cada logo va dentro de un contenedor uniforme `bg-white/95 rounded-lg p-1` para que logos negros o de colores se vean limpios sobre el tema oscuro.
