<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

// [Sem 09 - Controladores y MVC]: Login, registro, perfil y logout con inyección de Request
class AuthController extends Controller
{
    // [LOGIN]: Muestra el formulario de login
    public function showLogin()
    {
        return view('auth.login');
    }

    // [LOGIN POST]: Valida correo y contraseña contra la tabla users en MySQL
    public function login(Request $request)
    {
        $credenciales = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        // [Actualizado: Sesión permanente] remember = true mantiene la sesión hasta que el usuario cierre sesión
        if (Auth::attempt($credenciales, true)) {
            $request->session()->regenerate();

            // Si es administrador, lo manda directo al Panel Admin
            if (Auth::user()->esAdmin()) {
                return redirect()->route('admin.home')->with('exito', 'Bienvenido al Panel Administrador');
            }

            // [Actualizado: Login → Checkout] ?redirect=checkout (desde el carrito) o la URL protegida que intentaba abrir
            return $this->redirigirTrasAutenticar($request, '¡Bienvenido de nuevo a KixaStore!');
        }

        return back()->withErrors([
            'email' => 'El correo o la contraseña son incorrectos.',
        ])->onlyInput('email');
    }

    // [REGISTRO]: Muestra el formulario de crear cuenta
    public function showRegistro()
    {
        return view('auth.registro');
    }

    // [REGISTRO POST]: Crea un nuevo cliente real en la tabla users de MySQL
    public function registrar(Request $request)
    {
        // [VALIDACION FORMULARIO]: Reglas estrictas de validacion con contrasenia minima de 8 caracteres
        $datos = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'dni'      => ['nullable', 'string', 'size:8', 'regex:/^[0-9]{8}$/'],
            'phone'    => ['nullable', 'string', 'size:9', 'regex:/^[0-9]{9}$/'],
        ], [
            'password.min'       => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
            'dni.size'           => 'El DNI debe tener exactamente 8 dígitos numéricos.',
            'phone.size'         => 'El teléfono debe tener exactamente 9 dígitos numéricos.',
        ]);

        $usuario = User::create([
            'name'     => $datos['name'],
            'email'    => $datos['email'],
            'dni'      => $datos['dni'] ?? null,
            'telefono' => $datos['phone'] ?? null,
            'password' => Hash::make($datos['password']),
            'rol_id'   => Rol::where('nombre', 'cliente')->value('id'),
        ]);

        Auth::login($usuario, true);

        return $this->redirigirTrasAutenticar($request, 'Cuenta creada correctamente en KixaStore.');
    }

    // [Actualizado: Perfil estilo Puma] Una sola vista con pestañas; la sección llega por la ruta /perfil/{seccion?}
    public function perfil(?string $seccion = null)
    {
        $usuario = Auth::user();
        $seccion = $seccion ?? 'cuenta';
        $pedidos = Pedido::with('items.producto')->where('user_id', $usuario->id)->latest()->get();
        $pasos   = Pedido::PASOS;

        return view('auth.perfil', compact('usuario', 'pedidos', 'seccion', 'pasos'));
    }

    // [Actualizado: Perfil] Cambio de contraseña en su propia tarjeta (exige la contraseña actual)
    public function actualizarPassword(Request $request)
    {
        /** @var \App\Models\User $usuario */
        $usuario = Auth::user();

        $request->validate([
            'password_actual' => ['required', 'current_password'],
            'password'        => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password_actual.current_password' => 'La contraseña actual no es correcta.',
            'password.min'                     => 'La nueva contraseña debe tener al menos 8 caracteres.',
            'password.confirmed'               => 'La confirmación de la contraseña no coincide.',
        ]);

        $usuario->update(['password' => Hash::make($request->password)]);

        return redirect()->route('perfil')->with('exito', 'Tu contraseña se actualizó correctamente.');
    }

    // [Actualizado: Perfil] Dirección de envío predeterminada (pre-llena el checkout)
    public function actualizarDireccion(Request $request)
    {
        $datos = $request->validate([
            'direccion' => ['required', 'string', 'min:5', 'max:255'],
        ]);

        Auth::user()->update($datos);

        return redirect()->route('perfil', 'direcciones')->with('exito', 'Dirección predeterminada guardada.');
    }

    // [ACTUALIZAR PERFIL / CONTRASEÑA]: Guarda cambios en MySQL
    public function actualizarPerfil(Request $request)
    {
        /** @var \App\Models\User $usuario */
        $usuario = Auth::user();

        // [VALIDACION FORMULARIO]: Validacion con contrasenia opcional de minimo 8 caracteres
        $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'unique:users,email,' . $usuario->id],
            'password' => ['nullable', 'string', 'min:8'],
            'dni'      => ['nullable', 'regex:/^[0-9]{8}$/'],
            'telefono' => ['nullable', 'regex:/^[0-9]{9}$/'],
            'avatar'   => ['nullable', 'image', 'max:2048'],
        ], [
            'password.min' => 'La nueva contraseña debe tener al menos 8 caracteres.',
            'avatar.image' => 'La foto de perfil debe ser una imagen (JPG, PNG, WEBP...).',
            'avatar.max'   => 'La foto de perfil no debe pesar más de 2 MB.',
        ]);

        $usuario->name  = $request->name;
        $usuario->email = $request->email;
        if ($request->has('dni')) {
            $usuario->dni = $request->dni ?: null;
        }
        if ($request->has('telefono')) {
            $usuario->telefono = $request->telefono ?: null;
        }

        if ($request->filled('password')) {
            $usuario->password = Hash::make($request->password);
        }

        // Foto de perfil: se guarda en public/images/avatars y se borra la anterior para no acumular archivos
        if ($request->hasFile('avatar')) {
            if ($usuario->avatar && str_starts_with($usuario->avatar, 'images/avatars/') && File::exists(public_path($usuario->avatar))) {
                File::delete(public_path($usuario->avatar));
            }
            $archivo = $request->file('avatar');
            $nombre = 'avatar-' . $usuario->id . '-' . time() . '.' . $archivo->extension();
            $archivo->move(public_path('images/avatars'), $nombre);
            $usuario->avatar = 'images/avatars/' . $nombre;
        }

        $usuario->save();

        return back()->with('exito', 'Tus datos se actualizaron en la base de datos.');
    }

    // [Actualizado: Login → Checkout] Destinos permitidos tras autenticarse (lista cerrada: evita redirecciones abiertas)
    private function redirigirTrasAutenticar(Request $request, string $mensaje)
    {
        $destino = match ($request->input('redirect')) {
            'checkout' => route('checkout'),
            'carrito'  => route('carrito'),
            default    => null,
        };

        if ($destino) {
            $request->session()->forget('url.intended');
            return redirect()->to($destino)->with('exito', $mensaje);
        }

        return redirect()->intended(route('home'))->with('exito', $mensaje);
    }

    // [LOGOUT]: Cierra la sesión de forma segura
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
