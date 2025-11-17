@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])

@section('adminlte_css_pre')
    <!-- Animate.css for smooth animations -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

    <style>
        /* ========================================
           RESET Y CONFIGURACIÓN BASE
        ======================================== */
        
        /* Reset completo para evitar conflictos con AdminLTE */
        * {
            box-sizing: border-box !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        /* Anular estilos específicos de AdminLTE y Bootstrap */
        .login-page,
        .login-box,
        .card,
        .card-body,
        .form-group,
        .input-group,
        .btn,
        body.login-page {
            all: unset !important;
            box-sizing: border-box !important;
        }

        /* Configuración base del documento */
        html, body {
    margin: 0 !important;
    padding: 0 !important;
    height: 100vh !important;
    width: 100vw !important;
    display: flex !important;
    justify-content: center !important;
    align-items: center !important;
}


        /* ========================================
           FONDO DE VIDEO Y OVERLAY
        ======================================== */
        
        /* Video de fondo responsivo */
        .video-background {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            z-index: -2 !important;
            object-fit: cover !important;
            filter: brightness(0.6) contrast(1.2) !important;
        }

        /* Overlay con gradiente mejorado */
        .video-overlay {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            background: linear-gradient(
                135deg,
                rgba(102, 126, 234, 0.2) 0%,
                rgba(118, 75, 162, 0.3) 50%,
                rgba(0, 0, 0, 0.5) 100%
            ) !important;
            z-index: -1 !important;
        }

        /* ========================================
           CONTENEDOR PRINCIPAL
        ======================================== */
        
        /* Container principal - Mobile First */
       .login-page {
    height: 100% !important;
    width: 100% !important;
    display: flex !important;
    justify-content: center !important;
    align-items: center !important;
    padding: 20px !important;
    z-index: 10 !important;
}


        /* ========================================
           FORMULARIO DE LOGIN
        ======================================== */
        
        /* Contenedor del formulario con glassmorphism */
        .auth-page {
            background: rgba(255, 255, 255, 0.15) !important;
            backdrop-filter: blur(20px) !important;
            -webkit-backdrop-filter: blur(20px) !important;
            padding: 35px 30px !important;
            border-radius: 24px !important;
            box-shadow: 
                0 25px 50px rgba(0, 0, 0, 0.3),
                0 0 0 1px rgba(255, 255, 255, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.3) !important;
            width: 100% !important;
            max-width: 400px !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            position: relative !important;
            animation: slideInUp 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94) !important;
            overflow: hidden !important;
            margin: 0 auto !important;
        }

        /* Efecto de brillo sutil */
        .auth-page::before {
            content: '' !important;
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            background: linear-gradient(
                45deg,
                rgba(255, 255, 255, 0.1) 0%,
                transparent 50%,
                rgba(118, 75, 162, 0.05) 100%
            ) !important;
            border-radius: 24px !important;
            pointer-events: none !important;
            z-index: 1 !important;
        }

        /* Borde animado sutil */
        .auth-page::after {
            content: '' !important;
            position: absolute !important;
            top: -1px !important;
            left: -1px !important;
            right: -1px !important;
            bottom: -1px !important;
            background: linear-gradient(
                45deg,
                rgba(118, 75, 162, 0.4),
                rgba(102, 126, 234, 0.4),
                rgba(255, 255, 255, 0.2),
                rgba(118, 75, 162, 0.4)
            ) !important;
            border-radius: 25px !important;
            z-index: -1 !important;
            animation: rotateBorder 6s linear infinite !important;
        }

        /* ========================================
           TÍTULO Y HEADER
        ======================================== */
        
        .auth-header {
            text-align: center !important;
            margin-bottom: 35px !important;
            position: relative !important;
            z-index: 2 !important;
        }

        .auth-header h1 {
            color: #ffffff !important;
            font-size: 2.2rem !important;
            font-weight: 800 !important;
            margin: 0 !important;
            text-shadow: 
                0 4px 8px rgba(0, 0, 0, 0.4),
                0 0 20px rgba(118, 75, 162, 0.3) !important;
            letter-spacing: 1.5px !important;
            background: linear-gradient(135deg, #ffffff 0%, #e0e7ff 100%) !important;
            background-clip: text !important;
            -webkit-background-clip: text !important;
            -webkit-text-fill-color: transparent !important;
        }

        /* ========================================
           CAMPOS DE INPUT
        ======================================== */
        
        /* Grupos de input */
        .form-group {
            margin-bottom: 25px !important;
            position: relative !important;
            z-index: 2 !important;
        }

        .input-group {
            position: relative !important;
            margin-bottom: 25px !important;
            width: 100% !important;
            display: block !important;
        }

        /* Estilos de inputs mejorados */
        .input-group input {
            background: rgba(255, 255, 255, 0.12) !important;
            backdrop-filter: blur(10px) !important;
            -webkit-backdrop-filter: blur(10px) !important;
            border: 2px solid rgba(255, 255, 255, 0.2) !important;
            border-radius: 16px !important;
            padding: 18px 55px 18px 20px !important;
            font-size: 16px !important;
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94) !important;
            width: 100% !important;
            color: #ffffff !important;
            font-weight: 500 !important;
            min-height: 58px !important;
            box-shadow: 
                0 8px 20px rgba(0, 0, 0, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.2) !important;
            outline: none !important;
            display: block !important;
        }

        /* Estado focus de inputs */
        .input-group input:focus {
            border-color: rgba(118, 75, 162, 0.6) !important;
            background: rgba(255, 255, 255, 0.18) !important;
            box-shadow: 
                0 0 25px rgba(118, 75, 162, 0.3),
                0 15px 35px rgba(0, 0, 0, 0.15),
                inset 0 1px 0 rgba(255, 255, 255, 0.3) !important;
            transform: translateY(-2px) !important;
        }

        /* Placeholder styling */
        .input-group input::placeholder {
            color: rgba(255, 255, 255, 0.7) !important;
            font-weight: 400 !important;
            font-size: 15px !important;
        }

        /* Fix para autofill de Chrome */
        .input-group input:-webkit-autofill,
        .input-group input:-webkit-autofill:hover,
        .input-group input:-webkit-autofill:focus {
            -webkit-box-shadow: 0 0 0 30px rgba(255, 255, 255, 0.12) inset !important;
            -webkit-text-fill-color: #ffffff !important;
            transition: background-color 5000s ease-in-out 0s !important;
        }

        /* ========================================
           ICONOS DE INPUT
        ======================================== */
        
        .input-group-append {
            position: absolute !important;
            right: 20px !important;
            top: 50% !important;
            transform: translateY(-50%) !important;
            pointer-events: none !important;
            z-index: 3 !important;
        }

        .input-group-text {
            background: none !important;
            border: none !important;
            padding: 0 !important;
        }

        .input-group-text .fas {
            color: rgba(255, 255, 255, 0.7) !important;
            font-size: 18px !important;
            transition: all 0.3s ease !important;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3) !important;
        }

        .input-group:hover .input-group-text .fas {
            color: #ffffff !important;
            transform: scale(1.1) !important;
        }

        /* ========================================
           SECCIÓN REMEMBER ME
        ======================================== */
        
        .remember-section {
            display: flex !important;
            align-items: center !important;
            margin: 25px 0 !important;
            padding: 16px 20px !important;
            background: rgba(255, 255, 255, 0.08) !important;
            backdrop-filter: blur(10px) !important;
            -webkit-backdrop-filter: blur(10px) !important;
            border-radius: 14px !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            transition: all 0.3s ease !important;
            position: relative !important;
            z-index: 2 !important;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1) !important;
        }

        .remember-section:hover {
            background: rgba(255, 255, 255, 0.12) !important;
            border-color: rgba(118, 75, 162, 0.3) !important;
            transform: translateY(-1px) !important;
        }

        .remember-section input[type="checkbox"] {
            margin-right: 15px !important;
            width: 20px !important;
            height: 20px !important;
            accent-color: #764ba2 !important;
            cursor: pointer !important;
        }

        .remember-section label {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 500 !important;
            margin: 0 !important;
            cursor: pointer !important;
            font-size: 15px !important;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3) !important;
        }

        /* ========================================
           BOTÓN DE LOGIN
        ======================================== */
        
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%) !important;
            backdrop-filter: blur(15px) !important;
            -webkit-backdrop-filter: blur(15px) !important;
            border: 2px solid rgba(118, 75, 162, 0.3) !important;
            border-radius: 16px !important;
            padding: 18px 30px !important;
            color: white !important;
            font-size: 16px !important;
            font-weight: 700 !important;
            width: 100% !important;
            cursor: pointer !important;
            transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94) !important;
            box-shadow: 
                0 12px 30px rgba(118, 75, 162, 0.4),
                0 0 0 1px rgba(255, 255, 255, 0.1) !important;
            text-transform: uppercase !important;
            letter-spacing: 1.5px !important;
            margin-top: 20px !important;
            min-height: 58px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 12px !important;
            position: relative !important;
            overflow: hidden !important;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3) !important;
        }

        /* Efecto de brillo en hover */
        .btn-login::before {
            content: '' !important;
            position: absolute !important;
            top: 0 !important;
            left: -100% !important;
            width: 100% !important;
            height: 100% !important;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent) !important;
            transition: left 0.6s ease !important;
        }

        .btn-login:hover::before {
            left: 100% !important;
        }

        .btn-login:hover {
            transform: translateY(-3px) scale(1.02) !important;
            box-shadow: 
                0 20px 40px rgba(118, 75, 162, 0.6),
                0 0 0 1px rgba(255, 255, 255, 0.2) !important;
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 50%, #e084f7 100%) !important;
        }

        .btn-login:active {
            transform: translateY(-1px) scale(0.98) !important;
        }

        /* ========================================
           MENSAJES DE ERROR
        ======================================== */
        
        .invalid-feedback {
            display: block !important;
            color: #ff9999 !important;
            font-size: 14px !important;
            margin-top: 12px !important;
            padding: 12px 16px !important;
            background: rgba(255, 107, 107, 0.15) !important;
            backdrop-filter: blur(8px) !important;
            -webkit-backdrop-filter: blur(8px) !important;
            border-radius: 12px !important;
            border-left: 3px solid #ff6b6b !important;
            font-weight: 500 !important;
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.2) !important;
        }

        .is-invalid {
            border-color: rgba(255, 107, 107, 0.6) !important;
            box-shadow: 0 0 20px rgba(255, 107, 107, 0.3) !important;
            background: rgba(255, 107, 107, 0.1) !important;
        }

        /* ========================================
           ELEMENTOS DECORATIVOS
        ======================================== */
        
        .floating-elements {
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            pointer-events: none !important;
            z-index: 1 !important;
            overflow: hidden !important;
        }

        .floating-circle {
            position: absolute !important;
            border-radius: 50% !important;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, rgba(118, 75, 162, 0.05) 100%) !important;
            animation: float 12s infinite ease-in-out !important;
            backdrop-filter: blur(2px) !important;
            -webkit-backdrop-filter: blur(2px) !important;
        }

        .floating-circle:nth-child(1) {
            top: 20% !important;
            left: 10% !important;
            width: 60px !important;
            height: 60px !important;
            animation-delay: 0s !important;
        }

        .floating-circle:nth-child(2) {
            top: 60% !important;
            right: 15% !important;
            width: 40px !important;
            height: 40px !important;
            animation-delay: 4s !important;
        }

        .floating-circle:nth-child(3) {
            bottom: 30% !important;
            left: 20% !important;
            width: 80px !important;
            height: 80px !important;
            animation-delay: 8s !important;
        }

        /* ========================================
           ANIMACIONES
        ======================================== */
        
        @keyframes slideInUp {
            0% {
                opacity: 0;
                transform: translateY(50px) scale(0.95);
            }
            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes rotateBorder {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px) rotate(0deg);
                opacity: 0.3;
            }
            50% {
                transform: translateY(-30px) rotate(180deg);
                opacity: 0.6;
            }
        }

        /* ========================================
           RESPONSIVE DESIGN
        ======================================== */
        
        /* Tablets (768px - 1024px) */
        @media (min-width: 768px) and (max-width: 1024px) {
            .login-page {
                padding: 40px !important;
            }
            
            .auth-page {
                max-width: 450px !important;
                padding: 40px 35px !important;
            }
            
            .auth-header h1 {
                font-size: 2.4rem !important;
            }
        }

        /* Desktop (1025px+) */
        @media (min-width: 1025px) {
            .login-page {
                padding: 50px !important;
            }
            
            .auth-page {
                max-width: 480px !important;
                padding: 45px 40px !important;
            }
            
            .auth-header h1 {
                font-size: 2.6rem !important;
                letter-spacing: 2px !important;
            }
            
            .input-group input,
            .btn-login {
                min-height: 62px !important;
            }
        }

        /* Mobile Small (hasta 480px) */
        @media (max-width: 480px) {
            .login-page {
                padding: 15px !important;
            }
            
            .auth-page {
                padding: 25px 20px !important;
                max-width: 350px !important;
                border-radius: 20px !important;
            }
            
            .auth-header {
                margin-bottom: 25px !important;
            }
            
            .auth-header h1 {
                font-size: 1.8rem !important;
                letter-spacing: 1px !important;
            }
            
            .input-group input {
                padding: 16px 50px 16px 18px !important;
                min-height: 52px !important;
                font-size: 16px !important; /* Evita zoom en iOS */
            }
            
            .btn-login {
                padding: 16px 25px !important;
                min-height: 52px !important;
                font-size: 15px !important;
                letter-spacing: 1px !important;
            }
            
            .remember-section {
                padding: 14px 18px !important;
                margin: 20px 0 !important;
            }
            
            .floating-circle {
                display: none !important; /* Ocultar en móviles para mejor rendimiento */
            }
        }

        /* Mobile Medium (481px - 767px) */
        @media (min-width: 481px) and (max-width: 767px) {
            .login-page {
                padding: 25px !important;
            }
            
            .auth-page {
                padding: 30px 25px !important;
                max-width: 380px !important;
            }
            
            .auth-header h1 {
                font-size: 2rem !important;
            }
        }

        /* ========================================
           OPTIMIZACIONES Y ACCESIBILIDAD
        ======================================== */
        
        /* Optimización para iOS - Evitar zoom automático */
        @media (max-width: 768px) {
            .input-group input {
                -webkit-appearance: none !important;
                -webkit-border-radius: 16px !important;
            }
        }

        /* Respeto por preferencias de movimiento reducido */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }

        /* Soporte para modo oscuro del sistema */
        @media (prefers-color-scheme: dark) {
            .auth-page {
                background: rgba(0, 0, 0, 0.25) !important;
                border-color: rgba(255, 255, 255, 0.15) !important;
            }
        }

        /* Mejoras para pantallas de alta densidad */
        @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
            .auth-header h1 {
                text-shadow: 
                    0 2px 4px rgba(0, 0, 0, 0.4),
                    0 0 10px rgba(118, 75, 162, 0.3) !important;
            }
        }
    </style>
@stop

{{-- URLs de configuración --}}
@php($login_url = View::getSection('login_url') ?? config('adminlte.login_url', 'login'))
@php($register_url = View::getSection('register_url') ?? config('adminlte.register_url', 'register'))
@php($password_reset_url = View::getSection('password_reset_url') ?? config('adminlte.password_reset_url', 'password/reset'))

@if (config('adminlte.use_route_url', false))
    @php($login_url = $login_url ? route($login_url) : '')
    @php($register_url = $register_url ? route($register_url) : '')
    @php($password_reset_url = $password_reset_url ? route($password_reset_url) : '')
@else
    @php($login_url = $login_url ? url($login_url) : '')
    @php($register_url = $register_url ? url($register_url) : '')
    @php($password_reset_url = $password_reset_url ? url($password_reset_url) : '')
@endif

{{-- Header del formulario --}}
@section('auth_header')
    <div class="auth-header">
        <h1>IMPORTADORA MIRANDA</h1>
    </div>
@stop

{{-- Cuerpo del formulario --}}
@section('auth_body')
    {{-- Video de fondo --}}
    <video class="video-background" autoplay muted loop playsinline>
        <source src="{{ asset('images/impmiranda2.0.mp4') }}" type="video/mp4">
        <source src="{{ asset('images/impmiranda2.0.webm') }}" type="video/webm">
        Tu navegador no soporta el elemento video.
    </video>
    
    {{-- Overlay de gradiente --}}
    <div class="video-overlay"></div>
    
    {{-- Elementos decorativos flotantes --}}
    <div class="floating-elements">
        <div class="floating-circle"></div>
        <div class="floating-circle"></div>
        <div class="floating-circle"></div>
    </div>
    
    {{-- Contenedor principal del formulario --}}
    <div class="auth-page">
        <form action="{{ $login_url }}" method="post" novalidate>
            @csrf

            {{-- Campo de Email --}}
            <div class="input-group">
                <input type="email" 
                       name="email" 
                       class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email') }}" 
                       placeholder="Introduce tu correo electrónico"
                       autofocus
                       autocomplete="email"
                       required>
                <div class="input-group-append">
                    <div class="input-group-text">
                        <span class="fas fa-envelope"></span>
                    </div>
                </div>
                @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            {{-- Campo de Contraseña --}}
            <div class="input-group">
                <input type="password" 
                       name="password" 
                       class="form-control @error('password') is-invalid @enderror"
                       placeholder="Introduce tu contraseña"
                       autocomplete="current-password"
                       required>
                <div class="input-group-append">
                    <div class="input-group-text">
                        <span class="fas fa-lock"></span>
                    </div>
                </div>
                @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            {{-- Sección Remember Me --}}
            <div class="remember-section">
                <input type="checkbox" 
                       name="remember" 
                       id="remember" 
                       {{ old('remember') ? 'checked' : '' }}>
                <label for="remember">
                    {{ __('Recuérdame para la próxima vez') }}
                </label>
            </div>

            {{-- Botón de Login --}}
            <button type="submit" class="btn-login">
                <span class="fas fa-sign-in-alt"></span>
                {{ __('INICIAR SESIÓN') }}
            </button>
        </form>
    </div>
@stop

{{-- Footer del formulario --}}
@section('auth_footer')
@stop