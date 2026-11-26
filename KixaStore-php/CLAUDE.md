# KixaStore — Contexto del proyecto (Laravel 13 + MySQL)

Tienda de zapatillas con dos modos: **Cliente** (catálogo, carrito, checkout, perfil) y **Admin** (panel oscuro con CRUDs). Proyecto académico del curso de Laravel (Tecsup): el código debe ser claro y explicable en la exposición oral.

## 1. Reglas de trabajo

- Responde y explica SIEMPRE en español.
- NUNCA hagas `git commit` ni `git push`: los commits los hace el usuario.
- No leas ni escanees `vendor/`, `node_modules/`, `storage/`, `public/images/`, `.git/` ni `../Tienda-Kixa_2/`.
- No crees archivos `.gitignore` extra dentro de `public/images/`.
- Antes de dar algo por terminado corre `php artisan test` y `php -l` en lo tocado.
- Mantén `README.md` con un resumen corto de qué hace cada Controlador y Modelo, y actualiza `GUIA_PROYECTO.md` solo si cambian rutas, controladores o flujos principales.

## 2. Stack

- PHP ^8.3, Laravel 13, MySQL, Vite 8, Laravel Fortify (login/registro) y Spatie Laravel Permission ^8.5 (roles).
- Cliente y Admin: Tailwind CSS 4 compilado con Vite (`resources/css/app.css` + `resources/js/app.js`, cargados con `@vite` en `layouts/app.blade.php` y `layouts/admin.blade.php`). Ya no existe `public/css/`.
- Cliente: utilidades de Tailwind directamente en las vistas; JS plano en `public/js/*.js` (Tailwind también escanea esos archivos con `@source`).
- Tests: `tests/Feature/AdminCrudTest.php` (CRUDs 1-4, abastecimiento, dashboard), `AuthAndCatalogTest.php` y `RolesYPagoTest.php` (roles Spatie, Luhn, imágenes y logos).

## 3. Base de datos (migraciones en `database/migrations/`)

| Tabla                    | Campos clave                                                                                                                  | Llaves foráneas                                                   |
| ------------------------ | ----------------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------- |
| `roles` (Spatie)         | name, guard_name — valores `admin` y `cliente`                                                                                | —                                                                 |
| `model_has_roles` (Spatie) | role_id, model_type, model_id (enlaza cada usuario con su rol)                                                              | `role_id` → roles (cascade)                                       |
| `users`                  | name, email, dni, telefono, direccion, password, avatar (ya **no** tiene `rol_id`)                                            | —                                                                 |
| `categorias`             | nombre, slug (unique), descripcion, activo                                                                                    | —                                                                 |
| `proveedores`            | razon_social, ruc (char 11, unique), contacto_nombre, telefono, correo, direccion, activo                                     | —                                                                 |
| `marcas`                 | nombre (unique), slug (unique), logo (nullable), activo                                                                       | —                                                                 |
| `productos`              | marca (texto sincronizado), nombre, subcategoria, sku (unique), precio, descripcion, imagen, galeria_imagenes (json), destacado, en_oferta, activo | `categoria_id` (restrict), `proveedor_id` nullable (nullOnDelete), `marca_id` nullable (restrictOnDelete) |
| `producto_tallas`        | talla decimal(3,1), stock                                                                                                     | `producto_id` (cascadeOnDelete)                                   |
| `pedidos`                | codigo_orden (unique), subtotal, igv, total, estado, direccion_envio, metodo_pago (`Tarjeta`/`Contraentrega`), tarjeta_ultimos4                                             | `user_id` (restrict)                                              |
| `order_items`            | talla, cantidad, precio_unitario, subtotal                                                                                    | `pedido_id` (cascade), `producto_id` (restrict)                   |
| `ordenes_abastecimiento` | codigo_lote, cantidad_solicitada, costo_unitario, costo_total, estado, fecha_recepcion                                        | `proveedor_id`, `producto_talla_id`, `user_id`                    |

Orden de seeders (`DatabaseSeeder`): Rol → User → Categoria → Proveedor → Marca → Producto.
Roles con Spatie: la migración `2026_11_25_222600_eliminar_rol_id_de_users_table` quita `users.rol_id` y la tabla `roles` antigua **antes** de `create_permission_tables` (Spatie). `RolSeeder` usa `Role::findOrCreate()` y `UserSeeder` asigna con `assignRole()`. `MarcaSeeder` asigna los 5 logos de `public/images/brands/`.
Proveedores = distribuidoras mayoristas locales (p. ej. "Distribuidora Deportiva Perú S.A.C."); Marcas = fabricantes (Nike, Adidas…).
Estados de pedido: Pendiente, Pagado, Enviado, Entregado, Cancelado.

## 4. Modelos Eloquent (`app/Models/`)

Todos usan el atributo `#[Fillable([...])]`, `casts()` como método y `#[Scope]` para scopes.

- `User`: trait `HasRoles` (Spatie), `pedidos()` HasMany, `esAdmin()` (= `hasRole('admin')`), `nombreRol()`, `urlAvatar()`; ruta por `email` (`getRouteKeyName()`, usada en `/admin/clientes/{cliente}`). Ya no existe el modelo `Rol`; para filtrar clientes usa `User::role('cliente')`.
- `Categoria`: ruta por `slug`, scope `activas`, `productos()` HasMany.
- `Producto`: ruta por `sku`, scopes `activos` y `destacados`, accessors `modelo` y `tipo_calzado`, relaciones `categoria()`, `proveedor()`, `marca()`, `tallas()`. La columna de texto `marca` se conserva (catálogo y filtros) y se sincroniza con `marcas.nombre`; `$producto->marca` devuelve el texto y `$producto->marca()` la relación.
- `Marca`: ruta por `slug`, scope `activos`, accessor `url_logo`, `productos()` HasMany.
- `ProductoTalla`: `producto()` BelongsTo, constante `STOCK_CRITICO = 2`.
- `Proveedor`: ruta por `ruc`, scope `activos`, `productos()` HasMany.
- `Pedido`: ruta por `codigo_orden`, accessors `paso_seguimiento` y `metodo_pago_detalle` ("Tarjeta •••• 4242"), `usuario()` BelongsTo, `items()` HasMany.
- `DetallePedido` (tabla `order_items`): `pedido()`, `producto()`.
- `OrdenAbastecimiento`: `proveedor()`, `talla()`, `usuario()`.

## 5. Rutas (`routes/web.php`) y middleware

- Públicas: `/` (home), `/catalogo/{categoria?}`, `/producto/{sku?}`, `/producto/{sku}/tallas` (JSON), `/nosotros` (`NosotrosController` invocable con `__invoke()`, incluye sección `#ubicanos` con mapa de Tecsup), `/ayuda` (redirige a `/nosotros#centro-ayuda`).
- Sin rutas de `/contacto` ni Libro de Reclamaciones.
- Autenticación con **Laravel Fortify** (`FortifyServiceProvider`): `GET/POST /login`, `GET/POST /register`, `POST /logout`; `/registro` redirige a `/register`. `CreateNewUser` asigna el rol `cliente` y `LoginResponse`/`RegisterResponse` redirigen por rol y aceptan solo `?redirect=checkout|carrito`.
- Cuenta del cliente (middleware `auth`): `/perfil/{seccion?}` y los PUT de perfil, password y dirección.
- Compra: `/carrito`, `/favoritos`, `/checkout`, `POST /checkout/procesar` (`auth`).
- Panel: grupo `prefix('admin')->name('admin.')->middleware(['auth', 'admin'])`. El alias `admin` (en `bootstrap/app.php`) apunta a `AdminMiddleware`, que usa `esAdmin()` para sacar a los clientes.
    - `categorias` y `productos`: `Route::resource` sin `show` ni `destroy` (baja lógica con `PATCH .../estado`); parámetro por `slug` o `sku`.
    - CRUD 3 `proveedores` (parámetro por `ruc`) y CRUD 4 `marcas` (parámetro por `slug`): mismo patrón `Route::resource` sin `show` ni `destroy` + `PATCH .../estado`.
    - `clientes` (index, show, edit, update; parámetro por `email`), `pedidos` (index y `PATCH {pedido}/estado`, parámetro por `codigo_orden`), `perfil`.
    - Proceso Secundario `abastecimiento`: index, create, store, `PATCH {orden}/recibir` y `PATCH {orden}/cancelar` (parámetro por `codigo_lote`).
    - Ya no existe el módulo `tallas`: el stock por talla se gestiona desde Productos y Abastecimiento.

## 6. Controladores (`app/Http/Controllers/`)

- Cliente: `HomeController`, `NosotrosController` (invocable), `CatalogoController` (filtros por Request, buscador, `show` solo `activos()` por `sku`, `tallas`), `CarritoController` (checkout con token de un solo uso; simulador de pago Tarjeta/Contraentrega: `pasaLuhn()`, `tarjetaVigente()`, CVV 3-4 dígitos, guarda solo `tarjeta_ultimos4`; `procesar` usa `DB::transaction` y `lockForUpdate` para descontar stock y suma las líneas repetidas de una misma talla antes de comparar con el stock), `AuthController` (solo el perfil del cliente con pestañas; login y registro son de Fortify).
- Admin: `AdminController` (dashboard), `Admin/CategoriaController` (CRUD 1), `Admin/ProductoController` (CRUD 2; `opciones()` con categorías, proveedores y marcas activas, `<select name="marca_id">`, `sincronizarTallas`, SKU automático), `Admin/ProveedorController` (CRUD 3), `Admin/MarcaController` (CRUD 4: buscador, `withCount('productos')`, logo opcional en `public/images/brands/`, baja lógica `estado`), `Admin/AbastecimientoController` (Proceso Secundario: selects encadenados Producto → Talla), `Admin/PedidoController`, `Admin/ClienteController`, `Admin/PerfilController`.

## 7. Vistas (`resources/views/`)

- `layouts/app.blade.php` (cliente: top bar limpia con `Acerca de`, `Ubícanos` -> `/nosotros#ubicanos` e `Iniciar sesión`) y `layouts/admin.blade.php` (admin con sidebar).
- `auth/_portada.blade.php` (parcial en español para login y registro).
- `admin/<modulo>/{index,create,edit}.blade.php` + parcial `_formulario.blade.php` compartido por crear y editar.
- `components/`: `alerta`, `tarjeta-producto`, `filtro-catalogo`, `pregunta-frecuente`, `logo-tarjeta` (SVG Visa/Mastercard), `metodos-pago` (franja "Métodos de pago aceptados" del Resumen y del footer), `admin/estado-pedido`, `admin/seguimiento-pedido`.
- Toasts del cliente (`public/js/toast.js`): máximo 3 visibles (el más antiguo sale), sin duplicados (un mensaje repetido reinicia su cierre de ~3.5 s y se sacude) y `kxToastLimpiar(tipo)`; `producto-dinamico.js` limpia los de error al abrir el modal del carrito.

## 8. Reglas de la rúbrica (Laboratorio 13 y Entrega Final)

- **Roles y usuarios:** los roles viven en las tablas de Spatie (`roles` + `model_has_roles`), separados de `users`. Los 4 CRUDs NUNCA son sobre Usuarios ni Roles.
- **Prohibido mostrar IDs:** nunca muestres la columna `id` autoincremental en listados, detalles ni formularios. Las URLs usan `slug`, `sku`, `ruc`, `codigo_lote`, `codigo_orden` o `email` con `getRouteKeyName()`.
- **Llaves foráneas con nombres:** en tablas y detalles muestra `$producto->categoria->nombre` o `$producto->proveedor->razon_social`, nunca el número. En Crear/Editar usa `<select>` con esos nombres.
- Toda lógica va en Controladores. No pongas `@php ... @endphp` grandes ni `<style>` dentro de `.blade.php`.

## 9. Diseño

- **Admin:** Tailwind CSS v4 con tema oscuro neutro: fondo `#09090b`, tarjetas `#121215`, bordes `zinc-800/80`, acento índigo `#6366f1`. Sin tema rojo de marca (el rojo solo se reserva para etiquetas de "Agotado" o errores críticos de validación). Clases propias `kx-*` definidas en `resources/css/app.css`.
- **Cliente:** estilo retail deportivo editorial limpio (modo claro, blanco/negro/grises neutros), hecho con utilidades de Tailwind en cada vista. Tema propio en `@theme` de `app.css`: colores `tinta` (#111), `suave` (#707072), `perla` (#f5f5f7), `carbon` (#121215), `noche` (#09090b), `exito` (#059669); fuente `font-jakarta` (Plus Jakarta Sans); `--spacing-navbar` (76px) y breakpoint `escritorio` (769px, usar `max-escritorio:` para teléfonos). Tarjetas de calzado con imagen `aspect-square`, fondo `bg-perla`, `object-contain`.
- Solo los estados que cambia JavaScript en el cliente (carrusel `.kx-carrusel .slide.*` y drawer de filtros `.kx-catalogo-cuerpo` / `.kx-filtros` / `.kx-grid`) van en `@layer components` de `app.css`; no reutilices en el cliente las clases `kx-*` del admin (`kx-card`, `kx-btn`, `kx-input`…).
- Usa tamaños de texto arbitrarios (`text-[15px]`) en el cliente: los nombres `text-sm`, `text-base`… cambian también el `line-height`.
- En `resources/views/cliente/` conserva intactos los IDs y clases que usa `public/js/carrito.js` (incluye `#contenido-pago`, `#procesandoPago`, `#tokenCompra`, `[data-metodo]` y los `#tarjeta*` del simulador de pago).
- Checkout: solo 2 métodos de pago, Tarjeta de Crédito/Débito (tarjeta visual interactiva) y Contraentrega (Efectivo). Yape fue eliminado. Nunca guardes el número completo de tarjeta ni el CVV.
- `public/images/`: `avatars/`, `banners/{home,hombre,mujer,ninos,deporte}`, `brands/` (logos), `products/` (+ `uploads/`), `promotions/`, `ui/` (favicon `favicon-32x32.png` en ambos layouts y `backgrounds/`, que incluye `portada-auth.jpg`, el fondo de login/registro). No existen `payments/` ni `lanzamientos/`.
- Carrito, Checkout y Favoritos usan el mismo contenedor centrado `mx-auto max-w-[1080px]`.

## 10. Comentarios de estudio

En los Controladores, Modelos, Rutas y Vistas principales, agrega comentarios cortos de 1 línea que referencien la semana del curso donde se vio ese tema:

- `// [Sem 08 - Rutas y Vistas Blade]: Rutas nombradas, directivas @extends, @section, @foreach y componentes <x-...>`
- `// [Sem 09 - Controladores y Middleware]: Controladores normales, invocables (__invoke), inyección de Request y Middleware`
- `// [Sem 11 - Migraciones, Seeders y Roles]: Esquema MySQL, llaves foráneas constrained() y control de acceso por Rol`
- `// [Sem 12 - Eloquent ORM]: Modelos con #[Fillable], casts(), Scopes #[Scope] y relaciones BelongsTo / HasMany`
- `// [Sem 13 - CRUDs, Query Builder y Paginación]: Route::resource, validación con validate(), paginate(), baja lógica y Query Builder / DB::transaction()`

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

## Foundational Context

This application is a Laravel application running on PHP 8.5. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If a frontend change doesn't show in the UI or you get a "Unable to locate file in Vite manifest" error, run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Use `search-docs` before changes that depend on Laravel ecosystem APIs, behavior, configuration, or version-specific syntax. Skip it for copy-only edits and other changes where package documentation is irrelevant. Reuse sufficient results already in context instead of searching again.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists, including path-scoped framework guidelines under `.ai/rules/boost`. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== tests rules ===

# Test Enforcement

- Add or update tests for behavior and logic changes when a test provides meaningful regression coverage.
- Pure copy, styling, and layout-only changes do not require new or updated tests.
- When test coverage applies, run the affected tests and ensure they pass.
- Test the changed behavior and its important failure modes, but do not add tests beyond them.
- Read the `testing-best-practices` skill before writing tests.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This project uses PHPUnit. Create tests with `php artisan make:test --phpunit {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `php artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/phpunit` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.

</laravel-boost-guidelines>
