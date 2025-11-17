@extends('adminlte::page')

@section('title', 'Análisis Predictivo')

@section('content_header')
    <h1 class="text-center" style="font-family: 'Cinzel', serif; font-size: 3em; color: #8E44AD;">
        Análisis Predictivo
    </h1>
@stop

@section('content')

    <div class="row mb-4">
        <div class="col-md-4">
            <form method="GET" action="{{ route('analisis.index') }}" id="form-producto-filtro">
                <div class="form-group">
                    <label for="search_input">Buscar Producto:</label>
                    <div class="input-group">
                        {{-- Campo de texto para la búsqueda en tiempo real --}}
                        {{-- El valor inicial es el nombre del producto si ya está filtrado --}}
                        <input type="text" class="form-control" id="search_input" placeholder="Escribe para buscar..."
                            autocomplete="off"
                            value="{{ $productoId && $productos->first() ? $productos->first()->nombre : '' }}">

                        {{-- Campo oculto que enviará el ID seleccionado (el filtro) --}}
                        <input type="hidden" name="producto_id" id="producto_id_hidden" value="{{ $productoId ?? '' }}">

                        <div class="input-group-append">
                            {{-- Botón para limpiar el filtro --}}
                            <button class="btn btn-default" type="button" id="clear_search"
                                style="display: {{ $productoId ? 'inline-block' : 'none' }};">X</button>
                        </div>
                    </div>
                </div>
            </form>

            {{-- Contenedor para mostrar los resultados de la búsqueda AJAX --}}
            {{-- La posición 'w-100' hace que ocupe todo el ancho de su columna padre (col-md-4) --}}
            <div id="search_results" class="list-group position-absolute w-25"
                style="z-index: 1000; min-width: 300px; display: none;">
                {{-- Aquí se insertarán los resultados dinámicamente --}}
            </div>

            @if ($productoId)
                <div class="alert alert-success mt-2">
                    Producto filtrado: **{{ $productos->first()->nombre ?? 'N/A' }}**. Presiona 'X' para limpiar.
                </div>
            @endif

        </div>
    </div>

    <div class="row mb-4">
        @foreach ($cards as $title => $data)
            <div class="col-md-4">
                <div class="card text-white bg-{{ $data['color'] }} mb-3">
                    <div class="card-header">{{ $title }}</div>
                    <div class="card-body">
                        <h5 class="card-title">{{ $data['titulo'] }}</h5>
                        <p class="card-text">{{ $data['descripcion'] }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if ($productoId)
        @php
            $productoSeleccionado = $productos->first();
            $demandaProyectada = array_sum(array_column($predicciones, 'yhat'));
        @endphp

        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card border-primary">
                    <div class="card-header bg-primary text-white">
                        Información del Producto: {{ $productoSeleccionado->nombre }}
                    </div>
                    <div class="card-body">
                        <p><strong>Stock Actual:</strong> {{ $productoSeleccionado->stock }}</p>
                        <p><strong>Total Vendido Hasta Ahora:</strong> {{ $totalVendido }}</p>
                        <p><strong>Demanda Proyectada Próximos 7 días:</strong> {{ round($demandaProyectada) }}</p>
                        @php
                            $ultimoError = \App\Models\Prediccion::where('producto_id', $productoSeleccionado->id)
                                ->orderByDesc('created_at')
                                ->value('error_promedio');
                        @endphp

                        @if ($ultimoError)
                            <p><strong>Error Promedio del Modelo (MAE):</strong> {{ $ultimoError }}</p>
                        @endif
                        <h5>Ventas Último Mes:</h5>
                        <ul>
                            @foreach ($ventasUltimoMes as $venta)
                                <li>{{ $venta->fecha }}: {{ $venta->cantidad }} unidades</li>
                            @endforeach
                            @if (count($ventasUltimoMes) == 0)
                                <li>No hay ventas registradas en el último mes.</li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    Gráfico de Ventas y Predicción
                </div>
                <div class="card-body">
                    <canvas id="graficoVentas"></canvas>
                </div>
            </div>
        </div>
    </div>

    @if (count($alertas) > 0)
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="alert alert-danger">
                    <h5>Alertas de Inventario:</h5>
                    <ul>
                        @foreach ($alertas as $alerta)
                            <li>{{ $alerta }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        $(document).ready(function() {
            let searchTimeout;
            const searchInput = $('#search_input');
            const searchResults = $('#search_results');
            const productoIdHidden = $('#producto_id_hidden');
            const clearButton = $('#clear_search');

            // 1. Ocultar resultados al hacer clic fuera
            $(document).on('click', function(e) {
                if (!$(e.target).closest('#search_input, #search_results').length) {
                    searchResults.empty().hide();
                }
            });

            // 2. Lógica de búsqueda en tiempo real (Keyup)
            searchInput.on('keyup', function() {
                const term = $(this).val();
                clearTimeout(searchTimeout);

                // Si el campo está vacío o tiene menos de 2 caracteres, ocultar resultados
                if (term.length < 2) {
                    searchResults.empty().hide();
                    return;
                }

                // Delay para no sobrecargar el servidor
                searchTimeout = setTimeout(function() {
                    $.ajax({
                        url: "{{ route('analisis.searchProductos') }}",
                        method: 'GET',
                        data: {
                            term: term
                        },
                        success: function(data) {
                            searchResults.empty();

                            if (data.length > 0) {
                                let html = '';
                                $.each(data, function(index, producto) {
                                    // Crear un elemento clickeable para cada resultado
                                    html += `<a href="#" 
                                                class="list-group-item list-group-item-action" 
                                                data-id="${producto.id}" 
                                                data-nombre="${producto.nombre}"
                                             >
                                                ${producto.nombre}
                                            </a>`;
                                });
                                searchResults.html(html).show();
                            } else {
                                searchResults.html(
                                    '<a href="#" class="list-group-item list-group-item-secondary disabled">No se encontraron productos</a>'
                                ).show();
                            }
                        }
                    });
                }, 300); // 300ms de delay
            });

            // 3. Manejar la selección de un producto (Click en el resultado)
            searchResults.on('click', 'a.list-group-item-action', function(e) {
                e.preventDefault();
                const selectedId = $(this).data('id');
                const selectedName = $(this).data('nombre');

                // A. Establecer el ID y el Nombre
                productoIdHidden.val(selectedId);
                searchInput.val(selectedName);

                // B. Ocultar resultados y mostrar botón de limpiar
                searchResults.empty().hide();
                clearButton.show();

                // C. Enviar formulario para recargar la página con el filtro aplicado
                $('#form-producto-filtro').submit();
            });

            // 4. Lógica para limpiar la búsqueda/filtro
            clearButton.on('click', function() {
                productoIdHidden.val(''); // Limpiar el ID
                searchInput.val(''); // Limpiar el campo de texto
                clearButton.hide();
                $('#form-producto-filtro').submit(); // Recargar la página sin filtro
            });

        });
    </script>

    <script>
        @if (count($alertas) > 0)
            @foreach ($alertas as $alerta)
                console.log("ALERTA INVENTARIO:", "{{ $alerta }}");
                Swal.fire({
                    icon: 'warning',
                    title: 'Alerta de Inventario',
                    text: "{{ $alerta }}",
                    timer: 5000,
                    timerProgressBar: true,
                });
            @endforeach
        @endif
    </script>
    <script>
        @if ($productoId)
            const ventasHistoricas = @json($ventasHistoricasJS);
            const predicciones = @json($prediccionesJS);

            const ctx = document.getElementById('graficoVentas').getContext('2d');

            const fechasHistoricas = ventasHistoricas.map(v => v.fecha);
            const cantidadesHistoricas = ventasHistoricas.map(v => v.cantidad);

            const fechasPredichas = predicciones.map(p => p.fecha);
            const cantidadesPredichas = predicciones.map(p => p.yhat);

            const labels = fechasHistoricas.concat(fechasPredichas);
            const dataHistorica = cantidadesHistoricas;
            const dataPrediccion = Array(cantidadesHistoricas.length).fill(null).concat(cantidadesPredichas);

            const chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                            label: "Histórico de Ventas",
                            data: dataHistorica,
                            borderColor: "#3498db",
                            backgroundColor: "rgba(52, 152, 219, 0.2)",
                            tension: 0.3
                        },
                        {
                            label: "Predicción",
                            data: dataPrediccion,
                            borderColor: "#e74c3c",
                            backgroundColor: "rgba(231, 76, 60, 0.2)",
                            borderDash: [5, 5],
                            tension: 0.3
                        }
                    ]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Cantidad'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Fecha'
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        },
                        legend: {
                            position: 'top'
                        },
                        title: {
                            display: true,
                            text: 'Ventas y Predicción del Producto'
                        }
                    }
                }
            });
        @else
            // Cuando no hay producto seleccionado: mostrar gráfico con múltiples productos
            const ventasHistoricasJS = @json($ventasHistoricasJS);
            const prediccionesJS = @json($prediccionesJS);

            const ctx = document.getElementById('graficoVentas').getContext('2d');
            const datasets = [];

            // 1️⃣ Obtener todas las fechas únicas
            let allDatesSet = new Set();
            Object.values(ventasHistoricasJS).forEach(ventas => ventas.forEach(v => allDatesSet.add(v.fecha)));
            Object.values(prediccionesJS).forEach(preds => preds.forEach(p => allDatesSet.add(p.fecha)));
            const labels = Array.from(allDatesSet).sort();

            // 2️⃣ Generar datasets alineados por fecha
            Object.keys(ventasHistoricasJS).forEach((productoNombre, i) => {
                const ventas = ventasHistoricasJS[productoNombre];
                const preds = prediccionesJS[productoNombre];

                const ventasMap = {};
                ventas.forEach(v => ventasMap[v.fecha] = v.cantidad);

                const predsMap = {};
                preds.forEach(p => predsMap[p.fecha] = p.yhat);

                const dataHistorica = labels.map(fecha => ventasMap[fecha] ?? null);
                const dataPrediccion = labels.map(fecha => predsMap[fecha] ?? null);

                datasets.push({
                    label: `${productoNombre} - Histórico`,
                    data: dataHistorica,
                    borderColor: `hsl(${i * 90}, 70%, 50%)`,
                    backgroundColor: `hsla(${i * 90}, 70%, 50%, 0.1)`,
                    tension: 0.3
                });

                datasets.push({
                    label: `${productoNombre} - Predicción`,
                    data: dataPrediccion,
                    borderColor: `hsl(${i * 90}, 70%, 30%)`,
                    backgroundColor: `hsla(${i * 90}, 70%, 30%, 0.1)`,
                    borderDash: [5, 5],
                    tension: 0.3
                });
            });

            // 3️⃣ Crear gráfico
            const chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Cantidad vendida'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Fecha'
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        },
                        legend: {
                            position: 'top'
                        },
                        title: {
                            display: true,
                            text: 'Predicción de Ventas para Productos Destacados'
                        }
                    }
                }
            });
        @endif
    </script>
@stop
