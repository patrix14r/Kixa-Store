# KixaStore — Resumen de estudio (exposición oral)

## Controladores
| Controlador | Qué hace |
|---|---|
| `HomeController` | Home: consulta los productos con `destacado = true` (scopes `activos()->destacados()`) para la sección **Lo Destacado**. |
| `AuthController` | Solo el perfil del cliente. Login, registro y logout los maneja Laravel Fortify (`FortifyServiceProvider`, `CreateNewUser`, `LoginResponse`). |
| `CatalogoController` | Catálogo estilo Nike: título con contador, sidebar de filtros (categoría, marca, disciplina y talla obtenidos de los productos reales), orden por `?orden=` (destacados, precio, lo más nuevo), **paginación de 12** y ficha de producto con recomendados. Si la petición llega por `fetch` (`X-Requested-With`) devuelve solo el `@fragment('zona-catalogo')`, así los filtros no recargan header ni banner. Oculta productos de categorías inactivas. |
| `CarritoController` | Carrito, checkout y `procesar()`: crea el `Pedido` y sus detalles en una transacción y descuenta stock (suma las líneas repetidas de una misma talla antes de comparar con el stock). **Envío:** gratis en la primera compra o desde S/ 200 en productos, si no S/ 15 (`Pedido::calcularEnvio`, guardado en `costo_envio`). `validar()` (`POST /carrito/validar`) devuelve stock, precio y tallas reales del carrito del `localStorage`. **Simulador de pago:** Tarjeta (valida Luhn, vencimiento `MM/AA` y CVV; guarda solo los últimos 4 dígitos → pedido `Pagado`) o Contraentrega (pedido `Pendiente`). **Bloquea las compras del rol admin.** |
| `BoletaController` | Invocable (`GET /pedidos/{codigo_orden}/boleta`, middleware `auth`): genera la **boleta PDF** con `barryvdh/laravel-dompdf` (vista `comprobantes/boleta`, CSS plano en `resources/css/boleta.css`). Por defecto la abre en el navegador (`stream()`, vista previa) y con `?descargar=1` la descarga (`download()`) como `Boleta-PED-XXXX.pdf`. Solo el cliente dueño o un admin; cualquier otro recibe 403. |
| `AdminController` | Dashboard: KPIs de clientes (`User::role('cliente')` de Spatie) y pedidos, barras de productos por marca, arco de disponibilidad de stock, ingresos de 7/30 días, 5 alertas críticas y pedidos recientes. |
| `Admin\CategoriaController` | **Mantenimiento 1**: listar, crear, editar y activar/desactivar categorías (no se borran por la FK `restrictOnDelete`). El slug se arma solo desde el nombre (JS en vivo y `Str::slug` en el servidor). |
| `Admin\ProveedorController` | **Mantenimiento 3**: listado con buscador (razón social/RUC), paginación y `withCount('productos')`; crear/editar validando RUC de 11 dígitos único y teléfono de 9; activar/desactivar (sin borrar). La URL usa el RUC. |
| `Admin\MarcaController` | **Mantenimiento 4**: marcas (fabricantes) con buscador, `withCount('productos')`, paginación, logo opcional en `public/images/brands/` y activar/desactivar (sin borrar). La URL usa el slug; al renombrar sincroniza el texto `marca` de sus productos. |
| `Admin\AbastecimientoController` | Lotes de reposición: código automático `LOT-0001`, proveedor activo **obligatorio**, varias filas por lote (producto activo → talla, cantidad y costo) con inversión total y ganancia estimada (precio de venta − costo); `recibir` ("Confirmar recepción", `DB::transaction` + `lockForUpdate`) suma el stock de **todas** las tallas y `cancelar` la anula; `guia` genera la Guía / Orden de Compra en PDF (dompdf, `stream` o `?descargar=1`). Muestra el resumen de stock crítico (<= 2). |
| `Admin\ProductoController` | **Mantenimiento 2**: listado con buscador y filtros, crear/editar con `<select>` de categoría, proveedor y marca (sincroniza el texto `marca` para el catálogo), subida de imagen, galería de imágenes secundarias (JSON `galeria_imagenes`), stock por talla, SKU automático (`MARCA-MODELO-XXX`) si llega vacío, filtro por estado y vista rápida en modal. |
| `Admin\ClienteController` | Solo usuarios con rol cliente (`User::role('cliente')`): listado, historial de pedidos (`show`) y edición de nombre, correo, DNI y celular. No toca roles (Regla 4.2). |
| `Admin\PedidoController` | **Proceso 2 - Despacho**: lista los pedidos con cliente, fecha, pago, total y zapatillas; el admin cambia el estado (Pendiente → Entregado / Cancelado) y el cliente lo ve en su historial. |
| `Admin\PerfilController` | Perfil del admin dentro del panel oscuro: nombre, foto y contraseña (`current_password`). |

## Modelos
| Modelo | Relaciones / detalles |
|---|---|
| `User` | Trait `HasRoles` de **Spatie Laravel Permission** (roles en las tablas `roles` + `model_has_roles`, Regla 4.2); `esAdmin()` = `hasRole('admin')`; `nombreRol()`; `esPrimeraCompra()` (sin pedidos no cancelados → envío gratis); accessor `nombre_corto` (primer nombre + primer apellido, barra superior); `hasMany(Pedido)`; ruta por `email` en el panel de clientes. |
| `Categoria` | `hasMany(Producto)`; scope `activas`; URLs por `slug`. |
| `Marca` | `hasMany(Producto)`; URLs por `slug`; scope `activos`; accessor `url_logo`. |
| `Producto` | `belongsTo(Categoria)`, `belongsTo(Proveedor)`, `belongsTo(Marca)` (`marca()`), `hasMany(ProductoTalla)`; accessors `modelo` (nombre sin la marca repetida) y `tipo_calzado` ("Zapatillas urbanas", "Chimpunes de fútbol"…); URLs por `sku`. |
| `ProductoTalla` | `belongsTo(Producto)`; stock por talla; `STOCK_CRITICO = 2` y `ULTIMAS_UNIDADES = 3` (aviso "¡Últimas X unidades disponibles!"). |
| `Proveedor` | `hasMany(Producto)`; URLs por `ruc`; scope `activos`. |
| `OrdenAbastecimiento` | Encabezado del lote: `belongsTo` Proveedor y User, `hasMany(DetalleAbastecimiento)` (`detalles()`); accessors `unidades` y `ganancia_estimada`; URLs por `codigo_lote`. |
| `DetalleAbastecimiento` | Fila del lote (tabla `detalles_abastecimiento`): `belongsTo` OrdenAbastecimiento y ProductoTalla (`talla()`); cantidad, costo unitario, subtotal y accessor `ganancia_estimada`. |
| `Pedido` / `DetallePedido` | Pedido del cliente (`belongsTo(User)`, `hasMany(DetallePedido)`) y sus líneas; ruta por `codigo_orden`; `costo_envio`, constantes `COSTO_ENVIO`/`ENVIO_GRATIS_DESDE` y `calcularEnvio()`; `tarjeta_ultimos4` y accessor `metodo_pago_detalle` ("Tarjeta •••• 4242"); `DetallePedido` tiene el accessor `talla_etiqueta` (40 / 40.5). |

## Páginas informativas (Modo Cliente)
| Vista / Ruta | Qué hace |
|---|---|
| `layouts/app.blade.php` | Header oscuro: Liquid Glass translúcido en el home (`routeIs('home')`) que pasa a carbón `#121215` al hacer scroll; categorías con subrayado animado (sin submenús). Footer con anclas a `#mapa-tienda` y `#centro-ayuda`, redes con color de marca al hover y botón **Libro de Reclamaciones**. |
| `NosotrosController` + `informativas/nosotros` (`/nosotros`) | Controlador invocable (`__invoke`). **Acerca de**: historia, misión, visión, valores, **Centro de Ayuda** (acordeón `<details>`) y la sección **Ubícanos** con el mapa `<iframe>` de la sede (Av. Cascanueces 2221, Santa Anita). |
| `/ayuda` | `Route::redirect` 301 hacia `/nosotros#centro-ayuda` (la ayuda quedó unificada en Acerca de). |

## Rutas nuevas (routes/web.php)
| Ruta | Nombre | Qué hace |
|---|---|---|
| `GET /perfil/{seccion?}` (`cuenta`, `favoritos`, `pedidos`, `direcciones`) | `perfil` | Mi cuenta estilo Puma con pestañas; `whereIn()` limita las secciones válidas. |
| `PUT /perfil/password` | `perfil.password` | Cambia la contraseña pidiendo la actual (`current_password`). |
| `PUT /perfil/direccion` | `perfil.direccion` | Guarda la dirección predeterminada (pre-llena el checkout). |
| `GET /producto/{sku}/tallas` | `producto.tallas` | JSON con tallas en stock para "Seleccionar talla" en Favoritos. |
| `GET /checkout` / `POST /checkout/procesar` | `checkout` / `checkout.procesar` | Token de un solo uso + cabeceras anti-caché: el botón "Atrás" o un doble clic no duplican pedidos; cantidades enteras de 1 a 10 y stock validado. |
| `POST /carrito/validar` | `carrito.validar` | JSON con stock, precio y tallas reales de cada SKU del carrito (`throttle:60,1`); `carrito.js` deshabilita tallas agotadas y limita la cantidad. |

## Panel Administrador

- Sidebar con el enlace activo encuadrado (`mx-3`) y la sección **Clientes**; Categorías, Productos, Proveedores y Marcas (CRUDs 1-4) y, en Operaciones, Abastecimiento, Clientes y Pedidos.
- Los mensajes flash salen como toast flotante arriba a la derecha (`resources/js/app.js`).
- El menú de la cuenta admin tiene Mi Perfil, Ver Tienda Pública y Cerrar Sesión.
- `routes/web.php` está ordenado en 5 módulos: Públicas, Autenticación, Cuenta, Proceso de compra y Panel Admin.

## Cierre final

- `/admin/pedidos`: tarjetas resumen (total, por despachar, en camino, entregados, ingresos sin cancelados), pestañas por estado, buscador y la barra de 4 pasos `<x-admin.seguimiento-pedido>`, que usa el mismo `Pedido::PASOS` / `paso_seguimiento` que el perfil del cliente.
- Compra: al confirmarse, `CarritoController@procesar` marca `carrito_vaciado` en sesión y `carrito.js` borra el carrito activo y sus copias `kixa:<usuario>:carrito`; cada compra crea un pedido nuevo.
- Login y Registro: tarjeta split-screen (marca + formulario), `novalidate` y mensajes en español con `public/js/auth.js`.

## Avisos y flujo de compra
- **Toast único** (`public/js/toast.js`): `kxToast(mensaje, tipo)` muestra avisos en la esquina superior derecha; el layout le pasa los flash (`exito`, `error`) y los errores de validación (`$errors`). No se usan `alert()` nativos.
- **Login → Checkout:** el carrito envía `?redirect=checkout`; `LoginResponse`/`RegisterResponse` solo aceptan `checkout` o `carrito` (lista cerrada) y redirige ahí tras iniciar sesión o registrarse.

## Reglas de la rúbrica aplicadas
- **4.4:** ninguna tabla ni formulario muestra IDs; las URLs del admin usan `slug` y `sku`.
- **4.5:** las FK se muestran por nombre (`categoria->nombre`, `proveedor->razon_social`) y se eligen con `<select>`.

---

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
