@extends('layouts.app')

@push('styles')
    {{-- [ORIGEN]: Estilos de registro y login de Tienda-Kixa_2/css/login.css --}}
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endpush

@section('contenido')
    {{-- [ORIGEN]: Extraído de Tienda-Kixa_2/login.html (Líneas 226 a 485) --}}
    {{-- [SEPARADO]: Pestaña de registro independizada de login.html hacia su propia vista auth/registro.blade.php --}}
    {{-- [ADAPTADO]: Se agregó @csrf, se integró a layouts.app y se cambió enlace hacia route('login') --}}
    <div class="background">
        <div class="overlay"></div>

        <div class="login-box" id="formContainer">

            <form id="registerForm" class="form active" method="POST" action="#">
                @csrf

                <div class="form-icon">
                    <i class="fa-solid fa-user-plus"></i>
                </div>

                <h2>
                    Crea tu cuenta en <span>Kixa</span>
                </h2>

                <p class="subtitle">
                    Completa todos tus datos para registrarte
                </p>

                <div class="registro-grid">

                    <!-- NOMBRE -->
                    <div class="input-group">
                        <input type="text" id="registerName" name="name" required>
                        <label for="registerName">
                            Nombre completo
                        </label>
                    </div>

                    <!-- CORREO -->
                    <div class="input-group">
                        <input type="email" id="registerEmail" name="email" required>
                        <label for="registerEmail">
                            Correo electrónico
                        </label>
                    </div>

                    <!-- TELEFONO -->
                    <div class="input-group">
                        <input type="tel" id="registerPhone" name="phone" maxlength="9" pattern="[0-9]{9}" required>
                        <label for="registerPhone">
                            Número de teléfono
                        </label>
                    </div>

                    <!-- DNI -->
                    <div class="input-group">
                        <input type="text" id="registerDni" name="dni" maxlength="8" pattern="[0-9]{8}" required>
                        <label for="registerDni">
                            DNI
                        </label>
                    </div>

                    <!-- CIUDAD -->
                    <div class="input-group select-group">
                        <select id="registerCity" name="city" required>
                            <option value="" disabled selected></option>
                            <option value="Lima">Lima</option>
                            <option value="Arequipa">Arequipa</option>
                            <option value="Trujillo">Trujillo</option>
                            <option value="Chiclayo">Chiclayo</option>
                            <option value="Piura">Piura</option>
                            <option value="Cusco">Cusco</option>
                            <option value="Iquitos">Iquitos</option>
                            <option value="Huancayo">Huancayo</option>
                            <option value="Tacna">Tacna</option>
                            <option value="Otra">Otra ciudad</option>
                        </select>
                        <label for="registerCity">
                            Ciudad
                        </label>
                    </div>

                    <!-- DIRECCION -->
                    <div class="input-group">
                        <input type="text" id="registerAddress" name="address" required>
                        <label for="registerAddress">
                            Dirección
                        </label>
                    </div>

                    <!-- FECHA -->
                    <div class="input-group">
                        <input type="date" id="registerBirth" name="birth_date" required>
                        <label for="registerBirth"></label>
                    </div>

                    <!-- GENERO -->
                    <div class="input-group select-group">
                        <select id="registerGender" name="gender" required>
                            <option value="" disabled selected></option>
                            <option value="Masculino">Masculino</option>
                            <option value="Femenino">Femenino</option>
                        </select>
                        <label for="registerGender">
                            Género
                        </label>
                    </div>

                    <!-- CONTRASEÑA -->
                    <div class="input-group password-full">
                        <input type="password" id="registerPassword" name="password" minlength="5" required>
                        <label for="registerPassword">
                            Contraseña
                        </label>
                    </div>

                </div>

                <button type="submit" class="btn">
                    <i class="fa-solid fa-user-plus"></i>
                    Crear cuenta
                </button>

                <p class="register-text">
                    ¿Ya tienes una cuenta?
                    <a href="{{ route('login') }}">
                        Inicia sesión
                    </a>
                </p>

            </form>

        </div>
    </div>
@endsection

@push('scripts')
    {{-- [ADAPTADO]: Script de registro y login en public/js/login.js --}}
    <script src="{{ asset('js/login.js') }}"></script>
@endpush
