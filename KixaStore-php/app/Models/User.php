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

// [Sem 12 - Eloquent ORM]: Usuario con relación BelongsTo hacia Rol y helper esAdmin()
#[Fillable(['name', 'email', 'dni', 'telefono', 'direccion', 'password', 'rol_id', 'avatar'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    // [Sem 12 - Eloquent ORM]: Relación HasMany con sus pedidos (usada en el listado de Clientes del admin)
    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class, 'user_id');
    }

    // URL pública de la foto de perfil, o null si el usuario aún no subió una
    public function urlAvatar(): ?string
    {
        return $this->avatar ? asset($this->avatar) : null;
    }

    // [Sem 11 - Migraciones, Seeders y Roles]: Control de acceso por rol usado en middleware, login y vistas
    public function esAdmin(): bool
    {
        return $this->rol?->nombre === 'admin';
    }
}
