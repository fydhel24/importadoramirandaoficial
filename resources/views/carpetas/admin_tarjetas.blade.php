@extends('adminlte::page')

@section('title', 'Sucursales')

@section('content_header')
    <div class="content-header-modern">
        <h1 class="page-title">
            <i class="fas fa-store-alt" style="color: #4B0082;"></i>
            Sucursales Disponibles
        </h1>
        <p class="page-subtitle">Selecciona una sucursal para gestionar sus carpetas y cuadernos</p>
    </div>
@stop

@section('content')
    <div class="sucursales-container">
        <div class="row g-4">
            @foreach ($sucursales as $sucursal)
                <div class="col-xl-4 col-lg-6 col-md-6 mb-4">
                    <div class="sucursal-card">
                        <div class="card-header-custom">
                            <div class="sucursal-image-container">
                                <img src="{{ asset('images/ol.png') }}" alt="Sucursal {{ $sucursal->nombre }}"
                                    class="sucursal-image">
                                <div class="image-overlay">
                                    <i class="fas fa-building text-white"></i>
                                </div>
                            </div>
                        </div>

                        <div class="card-body-custom">
                            <h4 class="sucursal-title">{{ $sucursal->nombre }}</h4>
                            <div class="sucursal-divider"></div>

                            <div class="action-buttons">
                                <a href="{{ route('carpetas.index', ['sucursal_id' => $sucursal->id]) }}"
                                    class="btn-custom btn-primary-custom">
                                    <div class="btn-icon">
                                        <i class="fas fa-folder-open"></i>
                                    </div>
                                    <div class="btn-content">
                                        <span class="btn-title">Ver Carpetas</span>
                                        <span class="btn-subtitle">Gestionar capturas</span>
                                    </div>
                                    <i class="fas fa-chevron-right btn-arrow"></i>
                                </a>

                                <a href="{{ url('/envioscuadernosucursal/' . $sucursal->id) }}"
                                    class="btn-custom btn-success-custom">
                                    <div class="btn-icon">
                                        <i class="fas fa-book"></i>
                                    </div>
                                    <div class="btn-content">
                                        <span class="btn-title">Cuaderno</span>
                                        <span class="btn-subtitle">Registro de sucursal</span>
                                    </div>
                                    <i class="fas fa-chevron-right btn-arrow"></i>
                                </a>
                                <a href="{{ url('/orden/pedidos/' . (156 + $sucursal->id)) }}" class="btn-orange">
                                    <div class="btn-icon">
                                        <i class="fas fa-book"></i>
                                    </div>
                                    <div class="btn-content">
                                        <span class="btn-title">Pedidos</span>
                                        <span class="btn-subtitle">Pedidos de la Sucursal</span>
                                    </div>
                                    <i class="fas fa-chevron-right btn-arrow"></i>
                                </a>


                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@stop

@section('css')
    <style>
        /* Header moderno */
        .content-header-modern {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            color: white;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .page-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 0;
            font-weight: 300;
        }

        /* Container principal */
        .sucursales-container {
            padding: 1rem;
        }

        /* Tarjetas de sucursales */
        .sucursal-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            overflow: hidden;
            position: relative;
            height: 100%;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .sucursal-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .sucursal-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }

        /* Header de la tarjeta */
        .card-header-custom {
            padding: 2rem 2rem 1rem;
            text-align: center;
            position: relative;
        }

        .sucursal-image-container {
            position: relative;
            display: inline-block;
            margin-bottom: 1rem;
        }

        .sucursal-image {
            height: 120px;
            width: auto;
            border-radius: 15px;
            transition: all 0.3s ease;
            filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.1));
        }

        .image-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.8), rgba(118, 75, 162, 0.8));
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: all 0.3s ease;
        }

        .sucursal-card:hover .image-overlay {
            opacity: 1;
        }

        .image-overlay i {
            font-size: 2rem;
        }

        /* Cuerpo de la tarjeta */
        .card-body-custom {
            padding: 0 2rem 1rem;
        }

        .sucursal-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 1rem;
            text-align: center;
        }

        .sucursal-divider {
            height: 3px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            border-radius: 2px;
            margin: 0 auto 1.5rem;
            width: 60px;
        }

        /* Botones personalizados */
        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .btn-custom {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            border: 2px solid transparent;
        }

        .btn-custom::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn-custom:hover::before {
            left: 100%;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
            color: white;
        }

        .btn-success-custom {
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(72, 187, 120, 0.3);
        }

        .btn-success-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(72, 187, 120, 0.4);
            color: white;
        }

        .btn-icon {
            background: rgba(255, 255, 255, 0.2);
            padding: 0.75rem;
            border-radius: 10px;
            margin-right: 1rem;
            font-size: 1.2rem;
        }

        .btn-content {
            flex: 1;
            text-align: left;
        }

        .btn-title {
            display: block;
            font-weight: 600;
            font-size: 1rem;
            margin-bottom: 0.2rem;
        }

        .btn-subtitle {
            display: block;
            font-size: 0.85rem;
            opacity: 0.8;
        }

        .btn-arrow {
            font-size: 0.9rem;
            transition: transform 0.3s ease;
        }

        .btn-custom:hover .btn-arrow {
            transform: translateX(5px);
        }

        /* Footer de la tarjeta */
        .card-footer-custom {
            padding: 1rem 2rem 1.5rem;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
        }

        .status-indicator {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #48bb78;
            animation: pulse 2s infinite;
        }

        .status-text {
            font-size: 0.9rem;
            color: #48bb78;
            font-weight: 500;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(72, 187, 120, 0.7);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(72, 187, 120, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(72, 187, 120, 0);
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .page-title {
                font-size: 2rem;
            }

            .content-header-modern {
                padding: 1.5rem;
            }

            .sucursal-card {
                margin-bottom: 1.5rem;
            }

            .card-header-custom,
            .card-body-custom,
            .card-footer-custom {
                padding-left: 1.5rem;
                padding-right: 1.5rem;
            }
        }

        /* Animación de entrada */
        .sucursal-card {
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Delay para animación escalonada */
        .col-xl-4:nth-child(1) .sucursal-card {
            animation-delay: 0.1s;
        }

        .col-xl-4:nth-child(2) .sucursal-card {
            animation-delay: 0.2s;
        }

        .col-xl-4:nth-child(3) .sucursal-card {
            animation-delay: 0.3s;
        }

        .col-xl-4:nth-child(4) .sucursal-card {
            animation-delay: 0.4s;
        }

        .col-xl-4:nth-child(5) .sucursal-card {
            animation-delay: 0.5s;
        }

        .col-xl-4:nth-child(6) .sucursal-card {
            animation-delay: 0.6s;
        }

        /* //naraja */
        .btn-orange {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color: #f97316;
            /* naranja */
            color: white;
            padding: 12px 18px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            transition: background-color 0.3s ease, transform 0.2s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .btn-orange:hover {
            background-color: #ea580c;
            /* naranja más oscuro */
            transform: translateY(-2px);
        }

        .btn-orange .btn-icon {
            margin-right: 12px;
            font-size: 20px;
        }

        .btn-orange .btn-content {
            flex-grow: 1;
        }

        .btn-orange .btn-title {
            font-weight: bold;
            font-size: 16px;
            display: block;
        }

        .btn-orange .btn-subtitle {
            font-size: 12px;
            opacity: 0.9;
            display: block;
        }

        .btn-orange .btn-arrow {
            font-size: 16px;
            margin-left: 12px;
        }
    </style>
@stop
