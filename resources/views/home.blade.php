@extends('adminlte::page')

@section('title', 'Panel de Administración')

@section('content_header')
<div class="content-header-modern animated-header d-flex justify-content-between align-items-center flex-wrap">
    <div class="d-flex align-items-center flex-wrap">
        <div class="logo-container">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="header-logo simple-rotating-logo">
        </div>
        <div class="ml-3">
            <h2 class="welcome-text">¡Bienvenidos al sistema de Importadora Miranda!</h2>
            <h1 class="main-title subtle-animated">
                <i class="fas fa-chart-line mr-2"></i>
                Panel de Estadísticas de Ventas
            </h1>
        </div>
    </div>
    <div class="text-right mt-3 mt-lg-0 date-time-block">
        <div class="date-time-container">
            <p class="date-time-text" id="currentDate">
                <i class="fas fa-calendar-alt mr-2"></i>
                <span id="dateText"></span>
            </p>
            <p class="date-time-text" id="currentTime">
                <i class="fas fa-clock mr-2"></i>
                <span id="timeText"></span>
            </p>
        </div>
    </div>
</div>
@stop

@section('content')
<!-- Partículas flotantes sutiles -->
<div class="floating-particles-subtle">
    <div class="particle-subtle particle-1"></div>
    <div class="particle-subtle particle-2"></div>
    <div class="particle-subtle particle-3"></div>
    <div class="particle-subtle particle-4"></div>
    <div class="particle-subtle particle-5"></div>
</div>

<div class="dashboard-container">
    <!-- Sección Vendedor Estrella con Gráfico -->
    <div class="section-row">
        <div class="star-performer-section">
            <div class="star-card star-seller-card improved-star-card">
                <div class="star-background-effect-subtle"></div>
                
                <div class="star-header">
                    <div class="crown-container">
                        <i class="fas fa-crown star-icon simple-crown"></i>
                    </div>
                    <h3>Vendedor Estrella</h3>
                    <span class="star-badge">Hoy</span>
                </div>

                @if($vendedorTopHoy)
                <div class="star-content">
                    <div class="seller-avatar improved-avatar">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <h4 class="seller-name">{{ $vendedorTopHoy->user->name }}</h4>
                    <div class="seller-stats">
                        <span class="sales-count improved-number" data-target="{{ $vendedorTopHoy->total_ventas }}">0</span>
                        <span class="sales-label">ventas hoy</span>
                    </div>
                </div>
                @else
                <div class="star-content">
                    <p class="no-sales">No hay ventas registradas hoy</p>
                </div>
                @endif
            </div>
        </div>
        
        <div class="chart-section">
            <div class="chart-card improved-chart">
                <div class="card-header-custom">
                    <h4><i class="fas fa-chart-bar mr-2"></i>Ventas por Vendedor</h4>
                    <span class="chart-period">Mes actual</span>
                </div>
                <div class="chart-container">
                    <canvas id="sellersChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección Sucursal Estrella con Gráfico -->
    <div class="section-row">
        <div class="star-performer-section">
            <div class="star-card star-branch-card improved-star-card">
                <div class="star-background-effect-subtle branch-effect"></div>
                
                <div class="star-header">
                    <div class="crown-container">
                        <i class="fas fa-trophy star-icon simple-trophy"></i>
                    </div>
                    <h3>Sucursal Estrella</h3>
                    <span class="star-badge">Mes</span>
                </div>

                @if($ventasPorSucursal->first())
                <div class="star-content">
                    <div class="branch-avatar improved-avatar">
                        <i class="fas fa-store"></i>
                    </div>
                    <h4 class="branch-name">{{ $ventasPorSucursal->first()->sucursal->nombre ?? 'Sucursal Principal' }}</h4>
                    <div class="branch-stats">
                        <span class="sales-count improved-number" data-target="{{ $ventasPorSucursal->first()->total_ventas }}">0</span>
                        <span class="sales-label">ventas este mes</span>
                    </div>
                </div>
                @else
                <div class="star-content">
                    <p class="no-sales">No hay datos de sucursales</p>
                </div>
                @endif
            </div>
        </div>
        
        <div class="chart-section">
            <div class="chart-card improved-chart">
                <div class="card-header-custom">
                    <h4><i class="fas fa-building mr-2"></i>Ventas por Sucursal</h4>
                    <span class="chart-period">Mes actual</span>
                </div>
                <div class="chart-container">
                    <canvas id="branchesChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
/* Variables CSS */
:root {
    --primary-purple: #8B5CF6;
    --secondary-purple: #A78BFA;
    --light-purple: #EDE9FE;
    --primary-blue: #3B82F6;
    --secondary-blue: #60A5FA;
    --light-blue: #DBEAFE;
    --emerald: #10B981;
    --amber: #F59E0B;
    --red: #EF4444;
    --gradient-primary: linear-gradient(135deg, #8B5CF6 0%, #3B82F6 100%);
    --gradient-secondary: linear-gradient(135deg, #A78BFA 0%, #60A5FA 100%);
    --gradient-success: linear-gradient(135deg, #10B981 0%, #059669 100%);
    --gradient-warning: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
    --gradient-branch: linear-gradient(135deg, #F59E0B 0%, #EF4444 100%);
    --gradient-mega: linear-gradient(45deg, #667eea 0%, #764ba2 25%, #f093fb 50%, #f5576c 75%, #4facfe 100%);
    --shadow-soft: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    --shadow-medium: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    --shadow-large: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

/* Fondo mejorado y más sutil */
body {
    background: var(--gradient-mega);
    background-size: 300% 300%;
    animation: subtleGradientShift 20s ease infinite;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    min-height: 100vh;
    position: relative;
    overflow-x: hidden;
}

@keyframes subtleGradientShift {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

/* Partículas flotantes más sutiles */
.floating-particles-subtle {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: 1;
}

.particle-subtle {
    position: absolute;
    width: 2px;
    height: 2px;
    background: rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    animation: floatParticleSubtle 25s linear infinite;
}

.particle-1 { left: 20%; animation-delay: 0s; }
.particle-2 { left: 40%; animation-delay: 5s; }
.particle-3 { left: 60%; animation-delay: 10s; }
.particle-4 { left: 80%; animation-delay: 15s; }
.particle-5 { left: 90%; animation-delay: 20s; }

@keyframes floatParticleSubtle {
    0% {
        transform: translateY(100vh) translateX(0px);
        opacity: 0;
    }
    10% {
        opacity: 0.6;
    }
    90% {
        opacity: 0.6;
    }
    100% {
        transform: translateY(-100px) translateX(50px);
        opacity: 0;
    }
}

/* Container principal */
.dashboard-container {
    padding: 0 1rem;
    max-width: 1400px;
    margin: 0 auto;
    position: relative;
    z-index: 2;
}

/* Header mejorado y más sutil */
.animated-header {
    opacity: 0;
    transform: translateY(-20px);
    animation: gentleSlideDown 1s ease forwards;
}

@keyframes gentleSlideDown {
    0% {
        opacity: 0;
        transform: translateY(-20px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Logo simple que solo gira */
.logo-container {
    position: relative;
    display: inline-block;
}

.simple-rotating-logo {
    width: 80px;
    height: 80px;
    object-fit: contain;
    border-radius: 15px;
    box-shadow: var(--shadow-soft);
    animation: simpleRotate 10s linear infinite;
}

@keyframes simpleRotate {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Textos con animación sutil */
.welcome-text {
    font-size: 1.2rem;
    font-weight: 600;
    color: #ffffff;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    margin-bottom: 0.5rem;
}

.main-title {
    font-size: 2.5rem;
    font-weight: 800;
    color: white;
    text-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    margin: 0;
}

.subtle-animated {
    animation: subtlePulse 4s ease-in-out infinite;
}

@keyframes subtlePulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.01); }
}

/* Fecha y hora mejoradas */
.date-time-container {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border-radius: 15px;
    padding: 1rem;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.date-time-text {
    font-size: 0.95rem;
    margin: 0.3rem 0;
    font-weight: 600;
    text-shadow: 0 1px 3px rgba(0,0,0,0.5);
    color: #ffffff;
}

/* Header moderno */
.content-header-modern {
    background: var(--gradient-primary);
    padding: 2rem;
    border-radius: 1.5rem;
    margin-bottom: 2rem;
    color: white;
    box-shadow: var(--shadow-large);
    position: relative;
    overflow: hidden;
}

.content-header-modern::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 100%;
    height: 100%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    animation: gentleShimmer 8s ease-in-out infinite;
}

@keyframes gentleShimmer {
    0%, 100% { transform: rotate(0deg) scale(1); }
    50% { transform: rotate(180deg) scale(1.05); }
}

/* Secciones por filas */
.section-row {
    display: grid;
    grid-template-columns: 350px 1fr;
    gap: 2rem;
    margin-bottom: 2rem;
}

/* Tarjetas estrella mejoradas */
.improved-star-card {
    border-radius: 1.5rem;
    padding: 2rem;
    color: white;
    text-align: center;
    box-shadow: var(--shadow-large);
    position: relative;
    overflow: hidden;
    height: 100%;
    animation: gentleCardEntrance 0.8s ease forwards;
    transition: all 0.3s ease;
}

@keyframes gentleCardEntrance {
    0% {
        opacity: 0;
        transform: translateY(20px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

.improved-star-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-large);
}

.star-seller-card {
    background: var(--gradient-primary);
}

.star-branch-card {
    background: var(--gradient-branch);
}

/* Efecto de fondo sutil */
.star-background-effect-subtle {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(45deg, rgba(255,255,255,0.05) 0%, transparent 50%, rgba(255,255,255,0.05) 100%);
    animation: subtleBackgroundShift 6s ease-in-out infinite;
}

@keyframes subtleBackgroundShift {
    0%, 100% { transform: translateX(-100%); }
    50% { transform: translateX(100%); }
}

/* Corona simple */
.simple-crown {
    font-size: 2rem;
    color: #FCD34D;
    animation: gentleCrownFloat 3s ease-in-out infinite;
}

.simple-trophy {
    font-size: 2rem;
    color: #FCD34D;
    animation: gentleTrophyFloat 3s ease-in-out infinite;
}

@keyframes gentleCrownFloat {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-5px); }
}

@keyframes gentleTrophyFloat {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

.star-header h3 {
    font-size: 1.3rem;
    font-weight: 700;
    margin: 1rem 0 0.5rem;
}

.star-badge {
    background: rgba(255, 255, 255, 0.2);
    padding: 0.25rem 1rem;
    border-radius: 1rem;
    font-size: 0.8rem;
    font-weight: 500;
}

/* Avatar mejorado */
.improved-avatar {
    width: 80px;
    height: 80px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 1rem auto;
    font-size: 2rem;
    position: relative;
    z-index: 1;
    animation: gentleAvatarFloat 4s ease-in-out infinite;
}

@keyframes gentleAvatarFloat {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-3px); }
}

.seller-name, .branch-name {
    font-size: 1.3rem;
    font-weight: 700;
    margin-bottom: 1rem;
    position: relative;
    z-index: 1;
}

/* Número mejorado */
.improved-number {
    font-size: 2.5rem;
    font-weight: 800;
    display: block;
    position: relative;
    z-index: 1;
    animation: gentleNumberPulse 3s ease-in-out infinite;
}

@keyframes gentleNumberPulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.02); }
}

.sales-label {
    font-size: 0.9rem;
    opacity: 0.9;
    position: relative;
    z-index: 1;
}

/* Gráficos mejorados */
.improved-chart {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 1.5rem;
    box-shadow: var(--shadow-medium);
    border: 1px solid rgba(255, 255, 255, 0.2);
    overflow: hidden;
    height: 100%;
    animation: gentleChartSlide 0.6s ease forwards;
    transition: all 0.3s ease;
}

@keyframes gentleChartSlide {
    0% {
        opacity: 0;
        transform: translateX(30px);
    }
    100% {
        opacity: 1;
        transform: translateX(0);
    }
}

.improved-chart:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-large);
}

.card-header-custom {
    padding: 1.5rem;
    border-bottom: 1px solid rgba(226, 232, 240, 0.5);
    background: rgba(248, 250, 252, 0.5);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-header-custom h4 {
    font-size: 1.2rem;
    font-weight: 700;
    color: #1E293B;
    margin: 0;
}

.chart-period {
    font-size: 0.9rem;
    color: #64748B;
    font-weight: 500;
}

.chart-container {
    padding: 1.5rem;
    height: 300px;
    position: relative;
}

/* Responsive Design */
@media (max-width: 1200px) {
    .section-row {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
}

@media (max-width: 992px) {
    .main-title {
        font-size: 2rem;
    }
}

@media (max-width: 768px) {
    .dashboard-container {
        padding: 0 0.5rem;
    }
    
    .content-header-modern {
        padding: 1.5rem;
        text-align: center;
    }
    
    .main-title {
        font-size: 1.8rem;
    }
    
    .welcome-text {
        font-size: 1rem;
    }
    
    .chart-container {
        height: 250px;
    }
}

@media (max-width: 576px) {
    .simple-rotating-logo {
        width: 60px;
        height: 60px;
    }
}

/* Scrollbar personalizado */
::-webkit-scrollbar {
    width: 6px;
}

::-webkit-scrollbar-track {
    background: rgba(248, 250, 252, 0.5);
    border-radius: 3px;
}

::-webkit-scrollbar-thumb {
    background: var(--gradient-secondary);
    border-radius: 3px;
}
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Animación de contadores suave
    function animateCounters() {
        const counters = document.querySelectorAll('.improved-number[data-target]');
        
        counters.forEach(counter => {
            const target = parseInt(counter.getAttribute('data-target'));
            const duration = 1500; // 1.5 segundos
            const increment = target / (duration / 16);
            let current = 0;
            
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    counter.textContent = target.toLocaleString();
                    clearInterval(timer);
                } else {
                    counter.textContent = Math.floor(current).toLocaleString();
                }
            }, 16);
        });
    }

    // Iniciar animación de contadores
    setTimeout(animateCounters, 300);

    // Configuración de colores
    const colors = {
        primary: ['#8B5CF6', '#3B82F6', '#A78BFA', '#60A5FA', '#C084FC', '#93C5FD', '#DDD6FE', '#BFDBFE'],
        branches: ['#F59E0B', '#EF4444', '#10B981', '#8B5CF6', '#3B82F6', '#A78BFA']
    };

    // Gráfico de Vendedores
    const sellersCtx = document.getElementById('sellersChart').getContext('2d');
    new Chart(sellersCtx, {
        type: 'bar',
        data: {
            labels: @json($labels),
            datasets: [{
                label: 'Ventas',
                data: @json($data),
                backgroundColor: colors.primary.map(color => color + '80'),
                borderColor: colors.primary,
                borderWidth: 2,
                borderRadius: 8,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#FFFFFF',
                    bodyColor: '#FFFFFF',
                    borderColor: '#8B5CF6',
                    borderWidth: 1,
                    cornerRadius: 8,
                    displayColors: false
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)',
                    },
                    ticks: {
                        color: '#64748B',
                        font: {
                            weight: '500'
                        }
                    }
                },
                y: {
                    grid: {
                        display: false,
                    },
                    ticks: {
                        color: '#64748B',
                        font: {
                            weight: '500'
                        }
                    }
                }
            },
            animation: {
                duration: 1500,
                easing: 'easeOutQuart'
            }
        }
    });

    // Gráfico de Sucursales
    const branchesCtx = document.getElementById('branchesChart').getContext('2d');
    new Chart(branchesCtx, {
        type: 'bar',
        data: {
            labels: @json($labelsSucursal),
            datasets: [{
                label: 'Ventas por Sucursal',
                data: @json($dataSucursal),
                backgroundColor: colors.branches.map(color => color + '80'),
                borderColor: colors.branches,
                borderWidth: 2,
                borderRadius: 8,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#FFFFFF',
                    bodyColor: '#FFFFFF',
                    borderColor: '#F59E0B',
                    borderWidth: 1,
                    cornerRadius: 8,
                    displayColors: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)',
                    },
                    ticks: {
                        color: '#64748B',
                        font: {
                            weight: '500'
                        }
                    }
                },
                x: {
                    grid: {
                        display: false,
                    },
                    ticks: {
                        color: '#64748B',
                        font: {
                            weight: '500'
                        }
                    }
                }
            },
            animation: {
                duration: 1500,
                easing: 'easeOutQuart'
            }
        }
    });

    // Fecha y hora
    function updateDateTime() {
        const now = new Date();
        const dateOptions = { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric'
        };
        const formattedDate = now.toLocaleDateString('es-ES', dateOptions);
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        const formattedTime = `${hours}:${minutes}:${seconds}`;

        document.getElementById('dateText').textContent = formattedDate;
        document.getElementById('timeText').textContent = formattedTime;
    }

    updateDateTime();
    setInterval(updateDateTime, 1000);
});
</script>

@if (session('success'))
<script>
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        title: '¡Éxito!',
        text: '{{ session('success') }}',
        icon: 'success',
        confirmButtonText: 'Aceptar',
        confirmButtonColor: '#8B5CF6',
        background: 'rgba(255, 255, 255, 0.95)',
        backdrop: 'rgba(0, 0, 0, 0.4)'
    });
});
</script>
@endif
@stop