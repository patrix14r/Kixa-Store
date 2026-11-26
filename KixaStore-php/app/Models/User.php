<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

// [Sem 12 - Eloquent ORM]: Modelo de la tabla users con #[Fillable], casts() y relación HasMany con pedidos.
// [Sem 11 - Migraciones, Seeders y Roles]: Los roles viven en las tablas de Spatie (roles + model_has_roles); el trait HasRoles da assignRole(), hasRole() y el scope role().
#[Fillable(['name', 'email', 'dni', 'telefono', 'direccion', 'password', 'avatar'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    // casts() convierte tipos al leer y guardar: password con "hashed" se encripta sola al asignarla.
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relación HasMany (1 a N): un usuario tiene muchos pedidos; la FK user_id está en la tabla pedidos.
    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class, 'user_id');
    }

    // [Sem 08 - Rutas y Vistas Blade]: El panel de clientes usa el correo (único) en la URL (/admin/clientes/ana@correo.com) en vez del ID autoincremental.
    public function getRouteKeyName(): string
    {
        return 'email';
    }

    public function urlAvatar(): ?string
    {
        return $this->avatar ? asset($this->avatar) : null;
    }

    // Helper de rol: lo usan AdminMiddleware, el login y las vistas para saber si es administrador (hasRole() viene de Spatie).
    public function esAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    // Nombre del rol para mostrarlo en el panel (p. ej. "admin"); getRoleNames() devuelve una colección con los roles asignados.
    public function nombreRol(): ?string
    {
        return $this->getRoleNames()->first();
    }
}
