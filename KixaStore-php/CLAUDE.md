<laravel-boost-guidelines>
# KixaStore - Reglas del Proyecto y Guía Académica (Laravel 13)

## 1. Carpetas Prohibidas (Ahorro de Tokens)

- NUNCA leas ni escanees: `vendor/`, `node_modules/`, `storage/`, `public/images/`, `.git/`, ni `../Tienda-Kixa_2/`.
- NUNCA hagas `git commit` automáticamente. Todos los commits los hará el usuario manualmente.

## 2. Limpieza Visual y Arquitectura Blade

- Elimina todos los comentarios antiguos largos (`{{-- [ORIGEN]... --}}`).
- NUNCA pongas bloques `@php ... @endphp` grandes ni `<style>` dentro de archivos `.blade.php`. Toda lógica va en Controladores y el diseño del Admin usa Tailwind CSS con un único tema oscuro (fondo `#09090b`, tarjetas `#121215`, bordes `zinc-800/80`) y acento índigo `#6366f1`; el rojo `#ef4444` solo para "Agotado", alertas críticas y borrar.
- En el Modo Cliente (`resources/views/cliente/`), mejora el diseño visual pero CONSERVA intactos los IDs y clases que utilizan `public/js/movimiento.js` y `public/js/carrito.js`.

## 3. Reglas Estrictas de la Rúbrica (Laboratorio 13 - Docente Paul Vergel)

- **Regla 4.2 (Roles y Usuarios):** Separa los roles en una tabla/relación limpia (o Spatie/Fortify). Los CRUDs NUNCA deben ser sobre Usuarios ni Roles.
- **Regla 4.4 (Prohibido mostrar IDs):** JAMÁS muestres la columna `id` autogenerada en tablas de listado, vistas de detalle ni formularios de los CRUDs.
- **Regla 4.5 (Llaves Foráneas con Nombres):** En las tablas y detalles muestra siempre el nombre de la relación (`$producto->categoria->nombre`, `$producto->proveedor->razon_social`), NUNCA el número ID. En formularios de Crear/Editar usa `<select>` desplegables con esos nombres.

## 4. Comentarios Académicos por Semana (Para Estudio del Alumno)

En los Controladores, Modelos, Rutas y Vistas principales, agrega comentarios cortos de 1 línea que referencien la semana del curso donde se vio ese tema:

- `// [Sem 08 - Rutas y Vistas Blade]: Rutas nombradas, directivas @extends, @section, @foreach y componentes <x-...>`
- `// [Sem 09 - Controladores y MVC]: Controladores Resource (7 métodos CRUD) e inyección de Request`
- `// [Sem 11 - Migraciones, Seeders y Roles]: Esquema MySQL, llaves foráneas constrained() y control de acceso por Rol`
- `// [Sem 12 - Eloquent ORM]: Modelos con #[Fillable], casts(), Scopes #[Scope] y relaciones BelongsTo / HasMany`
  IMPORTANTE: Responde, explica y comunícate SIEMPRE en idioma ESPAÑOL en la terminal.
  Además, mantén actualizado en `README.md` un resumen corto de estudio explicando qué hace cada Controlador y Modelo para la exposición oral.

## 5. Estándar de Diseño Visual UI/UX (Prohibido Diseño Básico de IA)

- PROHIBIDO crear diseños planos o genéricos de IA (cajas cuadradas sin jerarquía, bordes gruesos toscos, sombras pesadas o colores chillones).
- Inspiración obligatoria para el Modo Cliente: Retail deportivo editorial de alto nivel (Nike.com / Adidas.com / Puma.com).
- Tipografía y espaciado: Jerarquía marcada (títulos display con `font-weight: 800` y `letter-spacing: -0.03em`, subtítulos limpios, aire generoso entre secciones `padding: 64px 5%`).
- Header minimalista: Prioridad visual al isotipo/logo, enlaces de categorías con indicador inferior animado al hacer hover (`transform: scaleX`), buscador tipo píldora estilizada e íconos SVG de trazo uniforme (`stroke-width: 1.75`).
- Tarjetas de calzado: Contenedor de imagen cuadrado uniforme (`aspect-ratio: 1/1`, fondo neutro `#f5f5f7`, `object-fit: contain`), micro-interacción suave al pasar el cursor (`translateY(-4px)`) y etiquetas de precio/categoría limpias.

# Laravel Application

This repository contains a Laravel application. Complete the following setup before working on the user's request.

## Prerequisites

Verify that PHP and Composer are available:

```sh
php -v
composer -V
```

If either command is unavailable, detect the user's operating system and install the prerequisites with the appropriate command:

macOS:

```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/mac/8.5)"
```

Windows PowerShell:

```powershell
Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://php.new/install/windows/8.5'))
```

Linux:

```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/linux/8.5)"
```

After installation, ask the user to restart their terminal. If the agent needs the restarted shell to continue, ask the user to reopen their terminal and rerun their original prompt.

## Agent Setup

Install Laravel Boost from the application root before making application changes:

```sh
composer require laravel/boost --dev
php artisan boost:install
```

Boost replaces these bootstrap instructions with guidelines tailored to the application. After installation, read `AGENTS.md` again and continue with the user's original request using the generated guidelines.
</laravel-boost-guidelines>
