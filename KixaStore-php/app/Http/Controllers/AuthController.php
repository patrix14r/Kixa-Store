<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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

        if (Auth::attempt($credenciales)) {
            $request->session()->regenerate();

            // Si es administrador, lo manda directo al Panel Admin
            if (Auth::user()->rol === 'admin') {
                return redirect()->route('admin.home')->with('exito', 'Bienvenido al Panel Administrador');
            }

            // Si es cliente normal, lo manda a la tienda pública
            return redirect()->intended(route('home'))->with('exito', '¡Bienvenido de nuevo a KixaStore!');
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
            'password' => Hash::make($datos['password']),
            'rol'      => 'cliente',
        ]);

        Auth::login($usuario);

        return redirect()->route('home')->with('exito', 'Cuenta creada correctamente en KixaStore.');
    }

    // [PERFIL]: Muestra los datos del usuario conectado y sus pedidos reales
    public function perfil()
    {
        $usuario = Auth::user();
        $pedidos = Pedido::with('items.producto')->where('user_id', $usuario->id)->latest()->get();
        return view('auth.perfil', compact('usuario', 'pedidos'));
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
        ], [
            'password.min' => 'La nueva contraseña debe tener al menos 8 caracteres.',
        ]);

        $usuario->name  = $request->name;
        $usuario->email = $request->email;

        if ($request->filled('password')) {
            $usuario->password = Hash::make($request->password);
        }

        $usuario->save();

        return back()->with('exito', 'Tus datos se actualizaron en la base de datos.');
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
