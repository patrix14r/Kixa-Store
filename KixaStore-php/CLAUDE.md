# KixaStore — Contexto del proyecto (Laravel 13 + MySQL)

Tienda de zapatillas con dos modos: **Cliente** (catálogo, carrito, checkout, perfil) y **Admin** (panel oscuro con CRUDs). Proyecto académico del curso de Laravel (Tecsup): el código debe ser claro y explicable en la exposición oral.

## 1. Reglas de trabajo

- Responde y explica SIEMPRE en español.
- NUNCA hagas `git commit` ni `git push`: los commits los hace el usuario.
- No leas ni escanees `vendor/`, `node_modules/`, `storage/`, `public/images/`, `.git/` ni `../Tienda-Kixa_2/`.
- No crees archivos `.gitignore` extra dentro de `public/images/`.
- Antes de dar algo por terminado corre `php artisan test` y `php -l` en lo tocado.
- Mantén `README.md` con un resumen corto de qué hace cada Controlador y Modelo, y actualiza `GUIA_FLUJOS_KIXASTORE.md` solo si cambian rutas, controladores o flujos principales.

## 2. Stack

- PHP ^8.3, Laravel 13, MySQL, Vite 8.
- Admin: Tailwind CSS 4 (`resources/css/app.css` + `resources/js/app.js`, cargados con `@vite`).
- Cliente: CSS modular en `public/css/*.css` (`diseño.css` es la base) y JS plano en `public/js/*.js`.
- Tests: `tests/Feature/AdminCrudTest.php` (CRUDs 1-4, abastecimiento, dashboard) y `AuthAndCatalogTest.php`.

## 3. Base de datos (migraciones en `database/migrations/`)

| Tabla                    | Campos clave                                                                                                                  | Llaves foráneas                                                   |
| ------------------------ | ----------------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------- |
| `roles`                  | nombre (unique), descripcion                                                                                                  | —                                                                 |
| `users`                  | name, email, dni, telefono, direccion, password, avatar                                                                       | `rol_id` → roles (restrictOnDelete)                               |
| `categorias`             | nombre, slug (unique), descripcion, activo                                                                                    | —                                                                 |
| `proveedores`            | razon_social, ruc (char 11, unique), contacto_nombre, telefono, correo, direccion, activo                                     | —                                                                 |
| `marcas`                 | nombre (unique), slug (unique), logo (nullable), activo                                                                       | —                                                                 |
| `productos`              | marca (texto sincronizado), nombre, subcategoria, sku (unique), precio, descripcion, imagen, galeria_imagenes (json), destacado, en_oferta, activo | `categoria_id` (restrict), `proveedor_id` nullable (nullOnDelete), `marca_id` nullable (restrictOnDelete) |
| `producto_tallas`        | talla decimal(3,1), stock                                                                                                     | `producto_id` (cascadeOnDelete)                                   |
| `pedidos`                | codigo_orden (unique), subtotal, igv, total, estado, direccion_envio, metodo_pago                                             | `user_id` (restrict)                                              |
| `order_items`            | talla, cantidad, precio_unitario, subtotal                                                                                    | `pedido_id` (cascade), `producto_id` (restrict)                   |
| `ordenes_abastecimiento` | codigo_lote, cantidad_solicitada, costo_unitario, costo_total, estado, fecha_recepcion                                        | `proveedor_id`, `producto_talla_id`, `user_id`                    |

Orden de seeders (`DatabaseSeeder`): Rol → User → Categoria → Proveedor → Marca → Producto.
Proveedores = distribuidoras mayoristas locales (p. ej. "Distribuidora Deportiva Perú S.A.C."); Marcas = fabricantes (Nike, Adidas…).
Estados de pedido: Pendiente, Pagado, Enviado, Entregado, Cancelado.

## 4. Modelos Eloquent (`app/Models/`)

Todos usan el atributo `#[Fillable([...])]`, `casts()` como método y `#[Scope]` para scopes.

- `User`: `rol()` BelongsTo, `pedidos()` HasMany, `esAdmin()`, `urlAvatar()`.
- `Rol`: `usuarios()` HasMany.
- `Categoria`: ruta por `slug`, scope `activas`, `productos()` HasMany.
- `Producto`: ruta por `sku`, scopes `activos` y `destacados`, accessors `modelo` y `tipo_calzado`, relaciones `categoria()`, `proveedor()`, `marca()`, `tallas()`. La columna de texto `marca` se conserva (catálogo y filtros) y se sincroniza con `marcas.nombre`; `$producto->marca` devuelve el texto y `$producto->marca()` la relación.
- `Marca`: ruta por `slug`, scope `activos`, accessor `url_logo`, `productos()` HasMany.
- `ProductoTalla`: `producto()` BelongsTo, constante `STOCK_CRITICO = 2`.
- `Proveedor`: ruta por `ruc`, scope `activos`, `productos()` HasMany.
- `Pedido`: accessor `paso_seguimiento`, `usuario()` BelongsTo, `items()` HasMany.
- `DetallePedido` (tabla `order_items`): `pedido()`, `producto()`.
- `OrdenAbastecimiento`: `proveedor()`, `talla()`, `usuario()`.

## 5. Rutas (`routes/web.php`) y middleware

- Públicas: `/` (home), `/catalogo/{categoria?}`, `/producto/{sku?}`, `/producto/{sku}/tallas` (JSON), `/nosotros` (`NosotrosController` invocable con `__invoke()`, incluye sección `#ubicanos` con mapa de Tecsup), `/ayuda` (redirige a `/nosotros#centro-ayuda`).
- Sin rutas de `/contacto` ni Libro de Reclamaciones.
- Autenticación: `/login`, `/registro`, `/logout` (`AuthController`).
- Cuenta del cliente (middleware `auth`): `/perfil/{seccion?}` y los PUT de perfil, password y dirección.
- Compra: `/carrito`, `/favoritos`, `/checkout`, `POST /checkout/procesar` (`auth`).
- Panel: grupo `prefix('admin')->name('admin.')->middleware(['auth', 'admin'])`. El alias `admin` (en `bootstrap/app.php`) apunta a `AdminMiddleware`, que usa `esAdmin()` para sacar a los clientes.
    - `categorias` y `productos`: `Route::resource` sin `show` ni `destroy` (baja lógica con `PATCH .../estado`); parámetro por `slug` o `sku`.
    - CRUD 3 `proveedores` (parámetro por `ruc`) y CRUD 4 `marcas` (parámetro por `slug`): mismo patrón `Route::resource` sin `show` ni `destroy` + `PATCH .../estado`.
    - `clientes` (index, show, edit, update), `pedidos` (index y `PATCH estado`), `perfil`.
    - Proceso Secundario `abastecimiento`: index, create, store, `PATCH {orden}/recibir` y `PATCH {orden}/cancelar` (parámetro por `codigo_lote`).
    - Ya no existe el módulo `tallas`: el stock por talla se gestiona desde Productos y Abastecimiento.

## 6. Controladores (`app/Http/Controllers/`)

- Cliente: `HomeController`, `NosotrosController` (invocable), `CatalogoController` (filtros por Request, buscador, `show` solo `activos()` por `sku`, `tallas`), `CarritoController` (checkout con token de un solo uso; `procesar` usa `DB::transaction` y `lockForUpdate` para descontar stock), `AuthController` (login, registro, perfil con pestañas).
- Admin: `AdminController` (dashboard), `Admin/CategoriaController` (CRUD 1), `Admin/ProductoController` (CRUD 2; `opciones()` con categorías, proveedores y marcas activas, `<select name="marca_id">`, `sincronizarTallas`, SKU automático), `Admin/ProveedorController` (CRUD 3), `Admin/MarcaController` (CRUD 4: buscador, `withCount('productos')`, logo opcional en `public/images/brands/`, baja lógica `estado`), `Admin/AbastecimientoController` (Proceso Secundario: selects encadenados Producto → Talla), `Admin/PedidoController`, `Admin/ClienteController`, `Admin/PerfilController`.

## 7. Vistas (`resources/views/`)

- `layouts/app.blade.php` (cliente: top bar limpia con `Acerca de`, `Ubícanos` -> `/nosotros#ubicanos` e `Iniciar sesión`) y `layouts/admin.blade.php` (admin con sidebar).
- `auth/_portada.blade.php` (parcial en español para login y registro).
- `admin/<modulo>/{index,create,edit}.blade.php` + parcial `_formulario.blade.php` compartido por crear y editar.
- `components/`: `alerta`, `tarjeta-producto`, `admin/estado-pedido`, `admin/seguimiento-pedido`.

## 8. Reglas de la rúbrica (Laboratorio 13 y Entrega Final)

- **Roles y usuarios:** los roles viven en su propia tabla `roles`. Los 4 CRUDs NUNCA son sobre Usuarios ni Roles.
- **Prohibido mostrar IDs:** nunca muestres la columna `id` autoincremental en listados, detalles ni formularios. Las URLs usan `slug`, `sku` o `ruc` con `getRouteKeyName()`.
- **Llaves foráneas con nombres:** en tablas y detalles muestra `$producto->categoria->nombre` o `$producto->proveedor->razon_social`, nunca el número. En Crear/Editar usa `<select>` con esos nombres.
- Toda lógica va en Controladores. No pongas `@php ... @endphp` grandes ni `<style>` dentro de `.blade.php`.

## 9. Diseño

- **Admin:** Tailwind CSS v4 con tema oscuro neutro: fondo `#09090b`, tarjetas `#121215`, bordes `zinc-800/80`, acento índigo `#6366f1`. Sin tema rojo de marca (el rojo solo se reserva para etiquetas de "Agotado" o errores críticos de validación). Clases propias `kx-*` definidas en `resources/css/app.css`.
- **Cliente:** estilo retail deportivo editorial limpio (modo claro, blanco/negro/grises neutros). Títulos con `font-weight: 800` y `letter-spacing: -0.03em`, secciones con `padding: 64px 5%`. Tarjetas de calzado con imagen `aspect-ratio: 1/1`, fondo `#f5f5f7`, `object-fit: contain`.
- En `resources/views/cliente/` conserva intactos los IDs y clases que usa `public/js/carrito.js`.

## 10. Comentarios de estudio

En los Controladores, Modelos, Rutas y Vistas principales, agrega comentarios cortos de 1 línea que referencien la semana del curso donde se vio ese tema:

- `// [Sem 08 - Rutas y Vistas Blade]: Rutas nombradas, directivas @extends, @section, @foreach y componentes <x-...>`
- `// [Sem 09 - Controladores y Middleware]: Controladores normales, invocables (__invoke), inyección de Request y Middleware`
- `// [Sem 11 - Migraciones, Seeders y Roles]: Esquema MySQL, llaves foráneas constrained() y control de acceso por Rol`
- `// [Sem 12 - Eloquent ORM]: Modelos con #[Fillable], casts(), Scopes #[Scope] y relaciones BelongsTo / HasMany`
- `// [Sem 13 - CRUDs, Query Builder y Paginación]: Route::resource, validación con validate(), paginate(), baja lógica y Query Builder / DB::transaction()`
