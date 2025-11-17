@extends('adminlte::page')

@section('title', 'Panel de Administración')

@section('content_header')
    <h1 class="text-center text-primary fw-bold mb-4">
        <i class="fas fa-cash-register"></i> Apertura de Caja por Sucursales
    </h1>
@stop

@section('content')
    {{-- Mensaje de éxito --}}
    @if (session()->has('success'))
        <div class="alert alert-success text-center shadow-sm rounded-pill py-3 fw-semibold">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    <div class="container-fluid px-4">
        {{-- Encabezado --}}
        <h2 class="text-center my-4 text-secondary">
            <i class="fas fa-map-marker-alt"></i> Sucursales
        </h2>

        {{-- Botones globales --}}
        <div class="row justify-content-center mb-5">
            <div class="col-md-4 mb-3">
                <form action="{{ route('cajas.abrir_todas') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-gradient-blue btn-lg w-100 shadow-sm">
                        <i class="fas fa-unlock"></i> Abrir Todas las Cajas
                    </button>
                </form>
            </div>
            <div class="col-md-4 mb-3">
                <form action="{{ route('cajas.cerrar_todas') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-gradient-red btn-lg w-100 shadow-sm">
                        <i class="fas fa-lock"></i> Cerrar Todas las Cajas
                    </button>
                </form>
            </div>
        </div>

        {{-- Tarjetas de sucursales --}}
        <div class="row g-4 justify-content-center">
            @foreach ($sucursales as $sucursal)
                <div class="col-sm-6 col-md-4 col-lg-3">
                    <div class="card card-modern text-center shadow-lg border-0 h-100">
                        <div class="card-header bg-primary bg-gradient text-white fw-semibold rounded-top">
                            <i class="fas fa-store-alt me-2"></i> {{ $sucursal->nombre }}
                        </div>
                        <div class="card-body d-flex flex-column justify-content-between">
                            <div>
                                <img src="{{ asset('images/logo_old_2.png') }}"
                                     class="rounded-circle shadow-sm mb-3"
                                     width="90" height="90"
                                     alt="Logo sucursal">
                                <p class="text-muted mb-3 small">
                                    <i class="fas fa-map-marker-alt"></i>
                                    {{ $sucursal->direccion ?? 'Ubicación no disponible' }}
                                </p>
                            </div>

                            @php
                                $cajaAbierta = \App\Models\Caja::whereNull('fecha_cierre')
                                    ->where('sucursal_id', $sucursal->id)
                                    ->first();
                            @endphp

                            @if ($cajaAbierta)
                                <a href="{{ route('cajas.index', $sucursal->id) }}"
                                   class="btn btn-success w-100 shadow-sm fw-semibold">
                                   <i class="fas fa-eye me-1"></i> Ver Caja
                                </a>
                            @else
                                <a href="{{ route('cajas.create', $sucursal->id) }}"
                                   class="btn btn-info w-100 shadow-sm fw-semibold">
                                   <i class="fas fa-box me-1"></i> Abrir Caja
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@stop

@section('css')
    <style>
        /* ======= Efecto general moderno ======= */
        body {
            background: linear-gradient(135deg, #f8f9fb, #eef1f6);
            font-family: 'Poppins', sans-serif;
        }

        h1, h2 {
            font-weight: 600;
        }

        /* ======= Tarjetas modernas ======= */
        .card-modern {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 1rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(5px);
        }

        .card-modern:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            font-size: 1.1rem;
            letter-spacing: 0.3px;
        }

        /* ======= Botones con gradiente ======= */
        .btn-gradient-blue {
            background: linear-gradient(45deg, #007bff, #00b4d8);
            border: none;
            color: #fff;
            font-weight: 600;
        }

        .btn-gradient-blue:hover {
            background: linear-gradient(45deg, #0062cc, #0096c7);
            transform: translateY(-2px);
        }

        .btn-gradient-red {
            background: linear-gradient(45deg, #dc3545, #ff4b2b);
            border: none;
            color: #fff;
            font-weight: 600;
        }

        .btn-gradient-red:hover {
            background: linear-gradient(45deg, #b52a3a, #ff1e00);
            transform: translateY(-2px);
        }

        /* ======= Botones generales ======= */
        .btn {
            transition: all 0.25s ease-in-out;
            border-radius: 0.5rem;
        }

        .btn i {
            margin-right: 5px;
        }

        /* ======= Responsividad ======= */
        @media (max-width: 768px) {
            h1 {
                font-size: 1.5rem;
            }
            .card-header {
                font-size: 1rem;
            }
        }
    </style>
@stop
