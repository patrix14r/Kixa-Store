<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// [Semana 12-13: Eloquent ORM, Relaciones y los 7 métodos de Route::resource] Modelo de la tabla users.
// #[Fillable] lista los campos que se pueden guardar con create()/update() (asignación masiva segura); #[Hidden] los oculta al convertir a JSON.
#[Fillable(['name', 'email', 'dni', 'telefono', 'direccion', 'password', 'rol_id', 'avatar'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    // casts() convierte tipos al leer y guardar: password con "hashed" se encripta sola al asignarla.
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relación BelongsTo (N a 1): muchos usuarios pertenecen a un rol; usa la FK rol_id de la tabla users (ver Semana 11: Migraciones, FK y Seeders).
    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    // Relación HasMany (1 a N): un usuario tiene muchos pedidos; la FK user_id está en la tabla pedidos.
    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class, 'user_id');
    }

    public function urlAvatar(): ?string
    {
        return $this->avatar ? asset($this->avatar) : null;
    }

    // Helper de rol: lo usan AdminMiddleware, el login y las vistas para saber si es administrador (el rol vive en su propia tabla roles).
    public function esAdmin(): bool
    {
        return $this->rol?->nombre === 'admin';
    }
}
