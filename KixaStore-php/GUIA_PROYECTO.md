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
| Estilos (`.guia-tallas-*`) | `public/css/producto.css` |
| Abrir/cerrar | `public/js/guia-tallas.js` |

**Flujo:** clic en el enlace → se quita `hidden` y se agrega la clase `abierto` (animación de opacidad) → se cierra con la X, haciendo clic en el fondo o con la tecla Escape. Usa atributos propios (`data-cerrar-guia`), así no interfiere con el modal del carrito (`#modalCarrito`, `data-cerrar-modal`) de `producto-dinamico.js`.

## 3. CRUD 4: Tallas y Stock (modo Admin)

| Capa | Archivo |
| --- | --- |
| Controlador | `app/Http/Controllers/Admin/TallaController.php` |
| Modelo | `app/Models/ProductoTalla.php` (accessor `talla_etiqueta`: "9" o "9.5") |
| Vistas | `resources/views/admin/tallas/` (`index`, `create`, `edit`, `_formulario`) |
| Rutas | `routes/web.php`, grupo `admin`, nombres `admin.tallas.*` |

**Rutas.** Una talla no tiene slug propio, así que la URL lleva el SKU del producto y el número de talla: `/admin/tallas/{producto}/{talla}/edit`. `{producto}` se resuelve por SKU y el controlador busca la talla con `$producto->tallas()->where('talla', ...)->firstOrFail()`. Ningún ID aparece en URLs ni tablas.

**Flujo**
1. **Listar** (`index`): filtro por producto (`?producto=SKU`) y por stock bajo (`?stock=bajo`, es decir 2 o menos), `with('producto')` y `paginate(15)->withQueryString()`. Se muestra el nombre del producto y la marca.
2. **Crear / editar**: formulario con `<select>` de productos (nombre visible, id interno), talla (20-50, de 0.5 en 0.5) y stock (>= 0). La combinación producto + talla es única (`Rule::unique(...)->where('producto_id', ...)->ignore(...)`).
3. **Eliminar** (`destroy`): borra la talla con confirmación; sus órdenes de abastecimiento se eliminan por `cascadeOnDelete`.

## 4. Abastecimiento (proceso secundario, modo Admin)

| Capa | Archivo |
| --- | --- |
| Controlador | `app/Http/Controllers/Admin/AbastecimientoController.php` |
| Modelo | `app/Models/OrdenAbastecimiento.php` (binding por `codigo_lote`) |
| Vistas | `resources/views/admin/abastecimiento/` (`index`, `create`) |
| Migración | `2026_11_11_044423_create_orden_abastecimientos_table.php` (estados `Pendiente`, `Recibido`, `Cancelado`) |

**Flujo**
1. **Listar** (`index`): órdenes con lote, proveedor, producto + talla, cantidad, costo total y estado; arriba, el resumen de tallas con stock crítico (2 o menos).
2. **Crear** (`create` → `store`): el lote se genera solo (`LOT-0001`, `LOT-0002`...). Se elige un proveedor **activo** (`Proveedor::activos()`), el producto + talla, la cantidad y el costo unitario. `costo_total = cantidad × costo_unitario`. Se guarda con el `user_id` del admin y estado `Pendiente`.
3. **Recibir** (`PATCH /admin/abastecimiento/{orden}/recibir`): dentro de `DB::transaction()` (con `lockForUpdate`) cambia a `Recibido`, guarda `fecha_recepcion = now()` y hace `increment('stock', cantidad_solicitada)` en `producto_tallas`. Si la orden ya no está pendiente no suma nada (evita duplicar stock).
4. **Cancelar** (`PATCH .../cancelar`): solo órdenes pendientes; no toca el stock.

## 5. Gráfico del dashboard

`/admin` muestra un gráfico lineal (Chart.js 4 por CDN) con los **ingresos** (eje izquierdo, línea índigo `#6366f1`) y la **cantidad de pedidos** (eje derecho, verde) de los últimos 7 días, sin contar pedidos cancelados.
- Datos: `AdminController@home` (variable `$grafico`: día, ingresos y pedidos).
- Vista: `admin/home.blade.php` (`<canvas id="graficoVentas" data-grafico='...'>` y `@push('scripts')` con el CDN).
- Dibujo y colores oscuros (tooltip `#121215`): `public/js/admin-grafico.js`.

## 6. Limpieza previa (resumen)

- Controladores Admin sin "Crud" en el nombre (`CategoriaController`, `ProductoController`, `ProveedorController`).
- `NosotrosController` invocable en `/nosotros`; la sección "Ubícanos" (mapa de la sede) está en `informativas/nosotros.blade.php`. La ruta `/contacto` ya no existe.
- `auth/_portada.blade.php` reemplaza a `_showcase`.
- `CatalogoController@show` solo muestra productos activos y busca únicamente por `sku`.

## Autenticación con Laravel Fortify

Login, registro y logout los maneja **Laravel Fortify** (backend sin vistas propias); el diseño sigue siendo el de `resources/views/auth/login.blade.php` y `registro.blade.php`.

- **Rutas (las crea Fortify):** `GET/POST /login`, `GET/POST /register`, `POST /logout`. El antiguo `/registro` redirige a `/register`. Nombres: `login`, `register`, `logout`.
- **`FortifyServiceProvider`:** conecta las vistas con `Fortify::loginView()` y `Fortify::registerView()`, registra `CreateNewUser` y cambia las respuestas por las nuestras. Está registrado en `bootstrap/providers.php`.
- **`CreateNewUser`** (`app/Actions/Fortify/`): valida (mensajes en español) y crea el usuario con el `rol_id` del rol `cliente`.
- **`LoginResponse` y `RegisterResponse`** (`app/Http/Responses/`): si `esAdmin()` va a `admin.home`; si es cliente respeta `?redirect=checkout|carrito` o vuelve a la URL prevista (`intended`) o al home. El trait `RespuestaAutenticacion` comparte esa lógica.
- **`config/fortify.php`:** solo la característica `registration`; no se usan recuperar contraseña, 2FA ni passkeys.
- **`AuthController`:** quedó solo con el perfil del cliente (`perfil`, `actualizarPerfil`, `actualizarPassword`, `actualizarDireccion`).
- El límite de intentos de login (5 por minuto) está en el `RateLimiter` del provider.
