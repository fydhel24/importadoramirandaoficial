@extends('adminlte::page')

@section('title', 'Reporte de Caja Sucursal')

@section('content_header')
    <h1 class="text-center">Reporte de Caja Sucursal - Importadora Miranda</h1>
@stop

@section('content')
    <div class="mb-3 text-right">
        <a href="{{ route('caja_sucursal.create') }}" class="btn btn-gradient-primary btn-lg">
            <i class="fas fa-plus-circle"></i> Generar reporte
        </a>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="cajaSucursalesTable">
                <thead>
                    <tr>
                        <th>Fechas y Detalle</th> <!-- Columna agrupada de fecha_inicio, fecha_fin y detalle -->
                        <th>Sucursales</th>
                        <th>Total Vendido</th>
                        <th>Total QR</th>
                        <th>Total Efectivo</th>
                        <th>QR Oficial</th>
                        <th>Efectivo Oficial</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Los datos se cargarán mediante AJAX -->
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('css')
    <style>
        /* Reducir tamaño de la fuente */
        table td, table th {
            font-size: 0.9rem; /* Ajustar tamaño de la fuente */
        }

        /* Estilo para las fechas, hacerlas más grandes y negritas */
        .fecha-campo {  
            font-weight: bold;  /* Negrita */
            color: #08090a;     /* Color azul para el texto */
        }

        /* Diferenciar los grupos por fechas */
        .fechas-group {
            background-color: #f5f5f5; /* Fondo más claro para los grupos */
            font-weight: bold; /* Hacer la fecha más destacada */
            border-top: 2px solid #ccc; /* Línea superior para separar los grupos */
            padding: 8px;
        }

        /* Aseguramos que las filas tengan un poco de separación visual */
        .grouped-row {
            margin-top: 10px; /* Separar más los grupos */
        }

        /* Estilos para las filas dentro de los grupos */
        .normal-row {
            background-color: #ffffff; /* Fondo blanco para las filas normales */
        }

        /* Agregar un borde inferior a cada fila para mayor separación */
        tr {
            border-bottom: 1px solid #ddd;
        }
    </style>
@stop

@section('js')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#cajaSucursalesTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('caja_sucursal.index') }}', // Ruta para cargar los datos con AJAX
                columns: [
                    { data: 'fechas', className: 'fecha-campo' }, // Aplicar la clase al campo fechas
                    { data: 'sucursal' }, // Nombres de sucursales
                    { data: 'total_vendido' },
                    { data: 'qr' },
                    { data: 'efectivo' },
                    { data: 'qr_oficial' },
                    { data: 'efectivo_oficial' },
                    { data: 'action', orderable: false, searchable: false }
                ],
                language: {
                    "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/es-MX.json" // Traducción al español
                },
                order: [[0, 'desc']] // Ordenar por la primera columna (Fechas)
            });
        });
    </script>
@stop
