{{-- filepath: d:\Trabajo Miranda\20-03-2025\pro\resources\views\sales\canceled_sales.blade.php --}}
@extends('adminlte::page')

@section('title', 'Ventas Canceladas')

@section('content_header')
    <h1 class="text-center font-weight-bold">Reporte de Ventas Canceladas</h1>
@stop

@section('css')
    <style>
        /* Estilo para los formularios de filtro */
        #filter-form {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Estilo para los botones */
        #filter-button {
            background-color: #007bff;
            border-color: #007bff;
            color: #fff;
            transition: background-color 0.3s ease;
            border-radius: 5px;
        }

        #filter-button:hover {
            background-color: #0056b3;
            border-color: #004085;
        }

        #reset-button {
            background-color: #6c757d;
            border-color: #6c757d;
            color: #fff;
            transition: background-color 0.3s ease;
            border-radius: 5px;
        }

        #reset-button:hover {
            background-color: #5a6268;
            border-color: #545b62;
        }

        /* Estilo para las cajas de resumen */
        .small-box {
            border-radius: 10px;
            padding: 20px;
            color: #fff;
            text-align: center;
        }

        .small-box .icon {
            font-size: 50px;
            opacity: 0.8;
        }

        .small-box .inner h3 {
            font-size: 2rem;
            font-weight: bold;
        }

        .small-box .inner p {
            font-size: 1.2rem;
        }

        /* Estilo para la tabla */
        #canceled-sales-table {
            font-size: 14px;
            border-collapse: collapse;
        }

        #canceled-sales-table thead {
            background-color: #343a40;
            color: #fff;
        }

        #canceled-sales-table tbody tr:hover {
            background-color: #f1f1f1;
        }

        /* Estilo para los selectores */
        select.form-control {
            border: 1px solid #ced4da;
            border-radius: 4px;
            padding: 5px;
        }

        /* Estilo para los inputs de fecha y hora */
        input[type="datetime-local"] {
            border: 1px solid #ced4da;
            border-radius: 4px;
            padding: 5px;
        }
    </style>
@stop

@section('content')
    <div class="container">
        <div class="card shadow-lg border-0" style="border-radius: 15px;">
            <div class="card-header bg-gradient-primary text-white text-center"
                style="border-top-left-radius: 15px; border-top-right-radius: 15px;">
                <h3 class="card-title"><i class="fas fa-ban"></i> Filtros de Ventas Canceladas</h3>
            </div>
            <div class="card-body" style="background: #f8f9fa;">
                <form id="filter-form" class="mb-4">
                    <div class="row">
                        <div class="col-md-3">
                            <label for="start_date" class="font-weight-bold">Fecha y Hora Inicio:</label>
                            <input type="datetime-local" id="start_date" name="start_date" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label for="end_date" class="font-weight-bold">Fecha y Hora Fin:</label>
                            <input type="datetime-local" id="end_date" name="end_date" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label for="user_id" class="font-weight-bold">Usuario:</label>
                            <select id="user_id" name="user_id" class="form-control">
                                <option value="">Todos</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="sucursal_id" class="font-weight-bold">Sucursal:</label>
                            <select id="sucursal_id" name="sucursal_id" class="form-control">
                                <option value="">Todas</option>
                                @foreach ($sucursales as $sucursal)
                                    <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12 text-right">
                            <button type="button" id="filter-button" class="btn btn-primary"><i class="fas fa-filter"></i>
                                Filtrar</button>
                            <button type="button" id="reset-button" class="btn btn-secondary"><i class="fas fa-undo"></i>
                                Reiniciar</button>
                            <a href="#" id="export-button" class="btn btn-success" target="_blank">
                                <i class="fas fa-file-pdf"></i> Exportar PDF
                            </a>
                        </div>
                    </div>
                </form>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3>{{ $totalProductosPerdidos }}</h3>
                                <p>Total Productos Perdidos</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-box"></i>
                            </div>
                        </div>
                    </div>
                  
                    <div class="col-md-4">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h1>{{ $productoMasCancelado ? $productoMasCancelado->producto->nombre : 'N/A' }}</h1>
                                <p>Producto Más Cancelado</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-chart-bar"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="canceled-sales-table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Fecha</th>
                                <th>Productos</th>
                                <th>Efectivo Perdido</th>
                                <th>Cantidad de Productos</th>
                                <th>Usuario</th>
                                <th>Sucursal</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <!-- Importación de SlimSelect -->
    <script src="https://unpkg.com/slim-select@latest/dist/slimselect.min.js"></script>
    <link href="https://unpkg.com/slim-select@latest/dist/slimselect.css" rel="stylesheet">

    <script>
        $(document).ready(function() {
            // Inicializar SlimSelect para Usuario y Sucursal
            new SlimSelect({
                select: '#user_id'
            });

            new SlimSelect({
                select: '#sucursal_id'
            });

            // Inicializar DataTable
            const table = $('#canceled-sales-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('ventas.canceladas') }}',
                    data: function(d) {
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                        d.user_id = $('#user_id').val();
                        d.sucursal_id = $('#sucursal_id').val();
                    }
                },
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'nombre_cliente',
                        name: 'nombre_cliente'
                    },
                    {
                        data: 'fecha',
                        name: 'fecha'
                    },
                    {
                        data: 'productos',
                        name: 'productos',
                        orderable: false,
                        searchable: true
                    },
                    {
                        data: 'efectivo_perdido',
                        name: 'efectivo_perdido'
                    },
                    {
                        data: 'cantidad_productos',
                        name: 'cantidad_productos'
                    },
                    {
                        data: 'user.name',
                        name: 'user.name'
                    },
                    {
                        data: 'sucursal.nombre',
                        name: 'sucursal.nombre'
                    },
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.10.21/i18n/Spanish.json'
                }
            });

            // Filtrar cuando el usuario haga clic en el botón
            $('#filter-button').click(function() {
                table.ajax.reload();
            });

            // Reiniciar filtros
            $('#reset-button').click(function() {
                $('#filter-form')[0].reset();
                table.ajax.reload();
            });
            $(document).ready(function() {
                $('#export-button').click(function(e) {
                    e.preventDefault();
                    const params = {
                        start_date: $('#start_date').val(),
                        end_date: $('#end_date').val(),
                        user_id: $('#user_id').val(),
                        sucursal_id: $('#sucursal_id').val(),
                    };
                    const queryString = $.param(params);
                    const url = '{{ route('ventas.canceladas.export') }}' + '?' + queryString;
                    window.open(url, '_blank'); // Abrir en una nueva pestaña
                });
            });
        });
    </script>
@stop
