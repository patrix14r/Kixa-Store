# KixaStore

Aplicación web de venta de zapatillas desarrollada en **Laravel 13**, **MySQL** y **Tailwind CSS v4** para el curso de Desarrollo de Aplicaciones en Internet (Tecsup).

## Funcionalidades principales

- **Tienda pública:** Catálogo con filtros dinámicos, detalle de producto por talla, favoritos, carrito de compras y checkout transaccional con descuento real de stock.
- **Autenticación y roles:** Gestión de sesiones con Laravel Fortify y control de acceso por roles (`admin` y `cliente`) con Spatie Laravel Permission.
- **Panel administrativo (`/admin`):** Dashboard con métricas, CRUDs de Categorías, Marcas, Proveedores y Productos, gestión de pedidos y módulo de Abastecimiento.

## Instalación local

```bash
cd KixaStore-php
composer install
npm install
cp .env.example .env
php artisan key:generate
```

1. Configura tu conexión a MySQL en `.env` (`DB_DATABASE=tienda_kixa`).
2. Ejecuta las migraciones, seeders y compila los estilos:

```bash
php artisan migrate:fresh --seed
npm run build
php artisan serve
```

## Credenciales de prueba

- **Administrador:** `admin@kixa.com` / `admin123`
- **Cliente:** `cliente@kixa.com` / `cliente123`
