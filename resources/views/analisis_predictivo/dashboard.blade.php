@extends('adminlte::page')

@section('title', 'Dashboard Predictivo')

@section('content_header')
<h1 class="text-center" style="font-family: 'Cinzel', serif; font-size: 2.5em; color: #8E44AD;">
    🔮 Dashboard de Predicciones
</h1>
@stop

@section('content')
<div class="container-fluid">

    <!-- Gráfica Línea de ventas históricas vs predicciones -->
    <div class="card mb-4">
        <div class="card-header">Ventas Históricas vs Predicciones</div>
        <div class="card-body">
            <canvas id="lineChart"></canvas>
        </div>
    </div>

    <!-- Gráfica barras productos más vendidos -->
    <div class="card mb-4">
        <div class="card-header">Productos Más Vendidos</div>
        <div class="card-body">
            <canvas id="barChart"></canvas>
        </div>
    </div>

    <!-- Gráfica barras predicción futura por producto -->
    <div class="card mb-4">
        <div class="card-header">Predicción de Demanda por Producto</div>
        <div class="card-body">
            <canvas id="barPredChart"></canvas>
        </div>
    </div>

</div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Línea de ventas históricas vs predicciones
    const lineCtx = document.getElementById('lineChart').getContext('2d');
    const lineChart = new Chart(lineCtx, {
        type: 'line',
        data: {
            labels: @json($fechas->concat($fechas_pred)),
            datasets: [
                {
                    label: 'Ventas Históricas',
                    data: @json($cantidades),
                    borderColor: '#3498DB',
                    backgroundColor: 'rgba(52,152,219,0.2)',
                    fill: true
                },
                {
                    label: 'Predicciones',
                    data: @json($cantidades_pred),
                    borderColor: '#E74C3C',
                    backgroundColor: 'rgba(231,76,60,0.2)',
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'top' } },
            scales: { y: { beginAtZero: true } }
        }
    });

    // Productos más vendidos
    const barCtx = document.getElementById('barChart').getContext('2d');
    const barChart = new Chart(barCtx, {
        type: 'bar',
        data: {
            labels: @json($productos_nombres),
            datasets: [{
                label: 'Cantidad Vendida',
                data: @json($productos_cantidades),
                backgroundColor: '#2ECC71'
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });

    // Predicción de demanda futura por producto
    const barPredCtx = document.getElementById('barPredChart').getContext('2d');
    const barPredChart = new Chart(barPredCtx, {
        type: 'bar',
        data: {
            labels: @json($productos_pred_nombres),
            datasets: [{
                label: 'Predicción Ventas',
                data: @json($productos_pred_cantidades),
                backgroundColor: '#F1C40F'
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });

</script>
@stop
