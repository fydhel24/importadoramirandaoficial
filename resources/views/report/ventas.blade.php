@extends('adminlte::page')

@section('title', 'Reporte de Ventas')

@section('content')
    <div class="container">
        <div class="card shadow mb-4">
            <div class="card-header bg-primary text-white">
                <h2 class="card-title">Reporte de Ventas - Importadora Miranda</h2>
            </div>
            <div class="card-header">
                <!-- Formulario de Filtro de Fecha -->
                <form id="filter-form" class="mb-4">
                    <div class="row align-items-center">
                        <!-- Fecha Inicio -->
                        <div class="col-md-2">
                            <label for="start_date" class="font-weight-bold small">Fecha Inicio:</label>
                            <input type="datetime-local" id="start_date" name="start_date"
                                class="form-control form-control-sm"
                                value="{{ request('start_date') ? \Carbon\Carbon::parse(request('start_date'))->format('Y-m-d\TH:i') : '' }}">
                        </div>
                        <!-- Fecha Fin -->
                        <div class="col-md-2">
                            <label for="end_date" class="font-weight-bold small">Fecha Fin:</label>
                            <input type="datetime-local" id="end_date" name="end_date" class="form-control form-control-sm"
                                value="{{ request('end_date') ? \Carbon\Carbon::parse(request('end_date'))->format('Y-m-d\TH:i') : '' }}">
                        </div>
                        <!-- Sucursal -->
                        <div class="col-md-2">
                            <label for="id_sucursal" class="font-weight-bold small">Sucursal:</label>
                            <select id="id_sucursal" name="id_sucursal" class="form-control form-control-sm">
                                <option value="">Todas las sucursales</option>
                                @foreach ($sucursales as $sucursal)
                                    <option value="{{ $sucursal->id }}"
                                        {{ request('id_sucursal') == $sucursal->id ? 'selected' : '' }}>
                                        {{ $sucursal->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <!-- Vendedor -->
                        <div class="col-md-2">
                            <label for="id_user" class="font-weight-bold small">Vendedor:</label>
                            <select id="id_user" name="id_user" class="form-control form-control-sm">
                                <option value="">Todos los vendedores</option>
                                @foreach ($usuarios as $usuario)
                                    <option value="{{ $usuario->id }}"
                                        {{ request('id_user') == $usuario->id ? 'selected' : '' }}>
                                        {{ $usuario->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <!-- Tipo de Pago -->
                        <div class="col-md-2">
                            <label for="tipo_pago" class="font-weight-bold small">Tipo de Pago:</label>
                            <select id="tipo_pago" name="tipo_pago" class="form-control form-control-sm">
                                <option value="">Todos</option>
                                <option value="QR" {{ request('tipo_pago') == 'QR' ? 'selected' : '' }}>QR</option>
                                <option value="EFECTIVO" {{ request('tipo_pago') == 'EFECTIVO' ? 'selected' : '' }}>Efectivo
                                </option>
                            </select>
                        </div>
                        <!-- Estado -->
                        <div class="col-md-2">
                            <label for="estado" class="font-weight-bold small">Estado:</label>
                            <select id="estado" name="estado" class="form-control form-control-sm">
                                <option value="">Todos</option>
                                <option value="NORMAL" {{ request('estado') == 'NORMAL' ? 'selected' : '' }}>NORMAL
                                </option>
                                <option value="RECOJO" {{ request('estado') == 'RECOJO' ? 'selected' : '' }}>RECOJO
                                </option>
                            </select>
                        </div>

                        <!-- Botón Filtrar -->
                        <div class="col-md-1 text-center">
                            <button type="button" id="filter-btn" class="btn btn-primary btn-sm mt-3 w-100">
                                <i class="fas fa-filter"></i> Filtrar
                            </button>
                        </div>
                        <!-- Botón Generar PDF -->
                        <div class="col-md-1 text-center">
                            <a href="#" id="generatePdf" class="btn btn-danger btn-sm mt-3 w-100">PDF</a>
                        </div>
                    </div>
                </form>


                <div class="row">
                    <!-- Resumen de Ventas -->
                    <div class="col-md-6 mb-4">
                        <div class="p-4 bg-light rounded shadow-sm border h-100">
                            <h3 class="text-secondary font-weight-bold mb-3">Resumen de Ventas</h3>
                            <ul class="list-unstyled">
                                <li><strong>Costo Total:</strong> <span id="totalCosto">0.00</span> Bs</li>
                                <li><strong>Utilidad Bruta:</strong> <span id="totalUtilidadBruta">0.00</span> Bs</li>
                                <li><strong>Número de Ventas:</strong> <span id="totalVentas">0</span></li>
                                <li><strong>Número de Productos Vendidos:</strong> <span
                                        id="totalProductosVendidos">0</span></li>
                                <li><strong>Total de Ventas (Bs):</strong> <span id="totalVentasBs">0.00</span> Bs</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Cards de Ventas -->
                    <div class="col-md-6">
                        <!-- Card de Ventas del Día -->
                        <div class="card shadow-sm border-0 mb-3">
                            <div class="card-header bg-primary text-white text-center py-2">
                                <h6 class="card-title mb-0">Ventas del Día</h6>
                            </div>
                            <div class="card-body py-3 px-4">
                                <div class="form-group mb-3">
                                    <label for="filter_day" class="form-label">Seleccionar Día:</label>
                                    <input type="date" id="filter_day" class="form-control form-control-sm">
                                    <button id="btnDailyReport" class="btn btn-outline-primary btn-sm">📄 Reporte</button>
                                </div>
                                <p class="text-muted mb-1"><strong>Total Ventas:</strong> <span id="ventasDiaTotal"
                                        class="fw-bold">{{ $ventasDia->total_ventas_dia ?? 0 }} Bs</span></p>
                                <p class="text-muted mb-1"><strong>Número de Ventas:</strong> <span id="ventasDiaNumero"
                                        class="fw-bold">{{ $ventasDia->num_ventas_dia ?? 0 }}</span></p>
                            </div>
                        </div>

                        <!-- Card de Ventas del Mes -->
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-success text-white text-center py-2">
                                <h6 class="card-title mb-0">Ventas del Mes</h6>
                            </div>
                            <div class="card-body py-3 px-4">
                                <div class="form-group mb-3">
                                    <label for="filter_month" class="form-label">Seleccionar Mes:</label>
                                    <input type="month" id="filter_month" class="form-control form-control-sm">
                                    <button id="btnMonthlyReport" class="btn btn-outline-success btn-sm">📄
                                        Reporte</button>
                                </div>
                                <p class="text-muted mb-1"><strong>Total Ventas:</strong> <span id="ventasMesTotal"
                                        class="fw-bold">{{ $ventasMes->total_ventas_mes ?? 0 }} Bs</span></p>
                                <p class="text-muted mb-1"><strong>Número de Ventas:</strong> <span id="ventasMesNumero"
                                        class="fw-bold">{{ $ventasMes->num_ventas_mes ?? 0 }}</span></p>
                            </div>
                        </div>
                    </div>
                </div>



            </div>
            <!-- Tabla de Detalles de Ventas -->
            <div class="card-body">
                <div class="table-responsive">
                    <table id="ventas-table" class="table table-bordered table-hover table-striped">
                        <thead class="thead-dark">
                            <tr>
                                <th>Fecha</th>
                                <th>estado</th>
                                <th>Nombre del Cliente</th>
                                <th>Sucursal</th>
                                <th>Vendedor</th>
                                <th>Tipo de Pago</th>
                                <th>Costo Total</th>
                                <th>Productos</th>
                            </tr>
                        </thead>

                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('css')
        <style>
            /* Cambiar el color del texto del título en el encabezado */
            .card-header h2 {
                color: white;
                /* Color blanco para los títulos en encabezados */
            }

            /* Opcional: para títulos de los cards */
            .card-header h6 {
                color: white;
                /* Color blanco específico para h6 en encabezados */
            }



            /* Reducir el tamaño de los cards */
            .card {
                max-width: 90%;
                max-height: 45%;
                /* Ajusta el ancho del card */
                margin: 0 auto;
                /* Centrar los cards */
                font-size: 0.85rem;
                /* Reducir el tamaño de fuente */
            }

            .card .card-header {
                padding: 0.4rem 0.5rem;
                /* Reducir padding del encabezado */
                font-size: 0.9rem;
                /* Ajustar tamaño de fuente */
            }

            .card .card-body {
                padding: 0.5rem 0.8rem;
                /* Reducir padding del cuerpo */
            }

            .card .form-group {
                margin-bottom: 0.6rem;
                /* Ajustar el espaciado de los filtros */
            }


            .card .btn {
                padding: 0.3rem 0.6rem;
                /* Reducir tamaño del botón */
                font-size: 0.8rem;
                /* Ajustar fuente del botón */
            }

            /* Asegurarse de que los inputs se vean proporcionados */
            .form-control {
                font-size: 0.8rem;
                /* Reducir el tamaño del texto en inputs */
                padding: 0.4rem 0.6rem;
                /* Reducir padding interno */
            }

            .card-header {
                background: linear-gradient(90deg, rgba(75, 178, 180, 1) 0%, rgba(0, 15, 173, 1) 50%, rgba(99, 38, 190, 1) 100%);
                color: white;
            }

            .table thead th {
                background-color: #343a40;
                color: #fff;
                text-align: center;
            }

            .table-hover tbody tr:hover {
                background-color: #f2f2f2;
            }

            .form-control {
                border-radius: 10px;
            }

            .btn {
                border-radius: 20px;
            }

            .form-group .btn {
                padding: 0.2rem 0.5rem;
                /* Reducir tamaño del botón */
                font-size: 0.75rem;
                /* Reducir tamaño de fuente */
                white-space: nowrap;
                /* Evitar que el texto del botón se corte */
            }

            .form-group label {
                font-size: 14px;
            }

            /* Reducir tamaño de los botones y ajustar su posición */
            .form-group {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 0.5rem;
                /* Separación entre el filtro y el botón */
            }

            label {
                font-weight: bold;
                color: #000;
            }
        </style>
        <link href="https://unpkg.com/slim-select@latest/dist/slimselect.css" rel="stylesheet">
        <!-- DataTables JS & CSS (CDN) -->
        <link href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css" rel="stylesheet">
        <link href="https://cdn.datatables.net/responsive/2.4.0/css/responsive.dataTables.min.css" rel="stylesheet">
    @endpush

    @push('js')
        <!-- jQuery and DataTables JS -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/responsive/2.4.0/js/dataTables.responsive.min.js"></script>

        <!-- Script for DataTable initialization -->
        <script>
            $(document).ready(function() {
                const table = $('#ventas-table').DataTable({
                    processing: true,
                    serverSide: true,
                    order: [
                        [0, 'desc']
                    ],
                    ajax: {
                        url: "{{ route('report.ventas') }}",
                        data: function(d) {
                            d.start_date = $('#start_date').val();
                            d.end_date = $('#end_date').val();
                            d.id_sucursal = $('#id_sucursal').val();
                            d.id_user = $('#id_user').val();
                            d.tipo_pago = $('#tipo_pago').val();
                            d.estado = $('#estado').val(); // Nuevo filtro
                        }
                    },
                    columns: [{
                            data: 'fecha',
                            name: 'fecha',
                            searchable: true
                        },
                        {
                            data: 'estado',
                            name: 'estado',
                            searchable: true
                        },

                        {
                            data: 'nombre_cliente',
                            name: 'nombre_cliente',
                            searchable: true
                        },
                        {
                            data: 'sucursal',
                            name: 'sucursal',
                            searchable: true
                        },
                        {
                            data: 'usuario',
                            name: 'usuario',
                            searchable: true
                        },
                        {
                            data: 'tipo_pago',
                            name: 'tipo_pago',
                            searchable: true
                        },
                        {
                            data: 'costo_total',
                            name: 'costo_total',
                            searchable: false
                        },
                        {
                            data: 'productos',
                            name: 'productos',
                            searchable: true
                        },
                    ],
                    responsive: true,
                    language: {
                        url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/Spanish.json"
                    },
                    drawCallback: function(settings) {
                        const resumen = settings.json || {};
                        $('#totalCosto').text(resumen.totalCosto || '0.00');
                        $('#totalUtilidadBruta').text(resumen.totalUtilidadBruta || '0.00');
                        $('#totalVentas').text(resumen.totalVentas || '0');
                        $('#totalProductosVendidos').text(resumen.totalProductosVendidos || '0');
                        $('#totalVentasBs').text(resumen.totalVentasBs || '0.00');
                    }
                });

                $('#filter-btn').on('click', function() {
                    table.ajax.reload();
                });
                // Generar PDF con filtros aplicados
                $('#generatePdf').click(function(e) {
                    e.preventDefault();

                    let query = $.param({
                        start_date: $('#start_date').val(),
                        end_date: $('#end_date').val(),
                        id_sucursal: $('#id_sucursal').val(),
                        id_user: $('#id_user').val(),
                        tipo_pago: $('#tipo_pago').val(),
                        estado: $('#estado').val(),
                    });

                    window.open("{{ route('ventas.pdf') }}?" + query, "_blank");
                });
                // Filtrar ventas del Día
                $('#filter_day').on('change', function() {
                    let selectedDay = $(this).val();
                    console.log(selectedDay); // Verificar el valor de la fecha seleccionada

                    if (selectedDay) {
                        $.ajax({
                            url: "{{ route('report.dias') }}", // Ruta del reporte del día
                            method: "GET",
                            data: {
                                day: selectedDay,
                            },
                            success: function(response) {
                                // Actualizar los datos de ventas del día en el card
                                $('#ventasDiaTotal').text(response.total_ventas_dia + ' Bs');
                                $('#ventasDiaNumero').text(response.num_ventas_dia);
                            },
                            error: function() {
                                alert('Error al obtener datos del día');
                            }
                        });
                    }
                });


                // Filtrar ventas del Mes
                $('#filter_month').on('change', function() {
                    let selectedMonth = $(this).val();

                    if (selectedMonth) {
                        $.ajax({
                            url: "{{ route('report.mess') }}", // Ruta del reporte del mes
                            method: "GET",
                            data: {
                                month: selectedMonth,
                            },
                            success: function(response) {
                                // Actualizar los datos de ventas del mes en el card
                                $('#ventasMesTotal').text(response.total_ventas_mes + ' Bs');
                                $('#ventasMesNumero').text(response.num_ventas_mes);
                            },
                            error: function() {
                                alert('Error al obtener datos del mes');
                            }
                        });
                    }
                });

                // Generar Reporte del Día
                $('#btnDailyReport').on('click', function() {
                    let selectedDay = $('#filter_day').val();
                    if (selectedDay) {
                        window.open("{{ route('report.pdfdia') }}?day=" + selectedDay, "_blank");
                    } else {
                        alert("Por favor, seleccione un día.");
                    }
                });

                // Generar Reporte del Mes
                $('#btnMonthlyReport').on('click', function() {
                    let selectedMonth = $('#filter_month').val();
                    if (selectedMonth) {
                        window.open("{{ route('report.pdfmes') }}?month=" + selectedMonth, "_blank");
                    } else {
                        alert("Por favor, seleccione un mes.");
                    }
                });
            });
        </script>
    @endpush
@endsection
