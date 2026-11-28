<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

// Usuario con roles de Spatie
#[Fillable(['name', 'email', 'dni', 'telefono', 'direccion', 'password', 'avatar'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    // Encripta la contraseña automáticamente
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Un usuario tiene muchos pedidos
    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class, 'user_id');
    }

    // Primera compra activa el envío gratis
    public function esPrimeraCompra(): bool
    {
        return $this->pedidos()->where('estado', '!=', 'Cancelado')->doesntExist();
    }

    // Usa el correo en la URL
    public function getRouteKeyName(): string
    {
        return 'email';
    }

    // Primer nombre y primer apellido
    protected function nombreCorto(): Attribute
    {
        return Attribute::get(function () {
            $partes = preg_split('/\s+/', trim((string) $this->name), -1, PREG_SPLIT_NO_EMPTY);

            return trim(($partes[0] ?? '') . ' ' . ($partes[count($partes) >= 4 ? 2 : 1] ?? ''));
        });
    }

    // URL pública de la foto de perfil
    public function urlAvatar(): ?string
    {
        return $this->avatar ? asset($this->avatar) : null;
    }

    // Verifica si tiene rol admin
    public function esAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    // Nombre del rol asignado
    public function nombreRol(): ?string
    {
        return $this->getRoleNames()->first();
    }
}
