<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

// [Sem 11 - Migraciones, Seeders y Roles]: Usuarios de prueba; el rol se asigna con assignRole() de Spatie (tabla model_has_roles), ya no con una columna rol_id.
// Hay 2 administradores y 3 clientes para demostrar que el panel soporta varios admins y que cada cliente ve solo sus pedidos.
class UserSeeder extends Seeder
{
    // Correos en minúsculas: Fortify los compara así ('lowercase_usernames' => true en config/fortify.php).
    private const USUARIOS = [
        ['name' => 'Administrador Kixa', 'email' => 'admin@kixa.com',     'password' => 'admin123',   'rol' => 'admin'],
        ['name' => 'Chambergo',          'email' => 'chambergo@kixa.com', 'password' => 'admin123',   'rol' => 'admin'],
        ['name' => 'Cliente Kixa',       'email' => 'cliente@kixa.com',   'password' => 'cliente123', 'rol' => 'cliente'],
        ['name' => 'Junior',             'email' => 'junior@kixa.com',    'password' => 'cliente123', 'rol' => 'cliente'],
        ['name' => 'Víctor',             'email' => 'victor@kixa.com',    'password' => 'cliente123', 'rol' => 'cliente'],
    ];

    public function run(): void
    {
        foreach (self::USUARIOS as $datos) {
            // [Sem 12 - Eloquent ORM]: firstOrCreate busca por email y solo crea si no existe: el seeder se puede volver a correr
            // (php artisan db:seed --class=UserSeeder) sin duplicar usuarios ni cambiar claves ya modificadas.
            // El cast "hashed" del modelo User encripta la contraseña al guardarla; assignRole no repite un rol que ya tiene.
            User::firstOrCreate(
                ['email' => $datos['email']],
                ['name' => $datos['name'], 'password' => $datos['password']],
            )->assignRole($datos['rol']);
        }
    }
}
