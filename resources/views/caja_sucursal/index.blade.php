@extends('adminlte::page')

@section('title', 'Reporte de Caja')

@section('content_header')
    <h1 class="text-center">Reporte de Caja - Importadora Miranda</h1>
@stop

@section('content')
    <div class="mb-3 text-right">
        <a href="{{ route('caja_sucursal.create') }}" class="btn btn-gradient-primary btn-lg">
            <i class="fas fa-plus-circle"></i> Generar reporte
        </a>
    </div>
    <div class="mb-3 text-right">
        <form id="filterForm" class="form-inline">
            <div class="form-group">
                <label for="fecha_inicio" class="mr-2">Fecha de Inicio</label>
                <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control mr-2" />
            </div>
            <div class="form-group">
                <label for="fecha_fin" class="mr-2">Fecha de Fin</label>
                <input type="date" id="fecha_fin" name="fecha_fin" class="form-control mr-2" />
            </div>
            <button type="submit" class="btn btn-primary">Filtrar</button>
        </form>
    </div>
    <div class="mb-3 text-right">
        <a href="{{ route('reporte_caja_pdf', ['fecha_inicio' => request('fecha_inicio'), 'fecha_fin' => request('fecha_fin')]) }}"
            class="btn btn-danger btn-lg">
            <i class="fas fa-file-pdf"></i> Generar PDF
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="cajaSucursalesTable">
                <thead>
                    <tr>
                        <th>Fechas y Detalle</th>
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
        table td,
        table th {
            font-size: 0.9rem;
            /* Ajustar tamaño de la fuente */
        }

        /* Estilo para las fechas, hacerlas más grandes y negritas */
        .fecha-campo {
            font-weight: bold;
            /* Negrita */
            color: #08090a;
            /* Color azul para el texto */
        }

        /* Diferenciar los grupos por fechas */
        .fechas-group {
            background-color: #f5f5f5;
            /* Fondo más claro para los grupos */
            font-weight: bold;
            /* Hacer la fecha más destacada */
            border-top: 2px solid #ccc;
            /* Línea superior para separar los grupos */
            padding: 8px;
        }

        /* Aseguramos que las filas tengan un poco de separación visual */
        .grouped-row {
            margin-top: 10px;
            /* Separar más los grupos */
        }

        /* Estilos para las filas dentro de los grupos */
        .normal-row {
            background-color: #ffffff;
            /* Fondo blanco para las filas normales */
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
            const updateTotals = () => {
                let totalVendido = 0;
                let totalQr = 0;
                let totalEfectivo = 0;
                let totalQrOficial = 0;
                let totalEfectivoOficial = 0;

                // Recorremos todas las filas de la tabla
                $('#cajaSucursalesTable tbody tr').each(function() {
                    // Extraer los valores de cada columna
                    let vendido = parseFloat($(this).find('td').eq(2).text().replace(',', '').trim()) ||
                        0;
                    let qr = parseFloat($(this).find('td').eq(3).text().replace(',', '').trim()) || 0;
                    let efectivo = parseFloat($(this).find('td').eq(4).text().replace(',', '')
                        .trim()) || 0;
                    let qrOficial = parseFloat($(this).find('td').eq(5).text().replace(',', '')
                        .trim()) || 0;
                    let efectivoOficial = parseFloat($(this).find('td').eq(6).text().replace(',', '')
                        .trim()) || 0;

                    // Acumulamos los totales
                    totalVendido += vendido;
                    totalQr += qr;
                    totalEfectivo += efectivo;
                    totalQrOficial += qrOficial;
                    totalEfectivoOficial += efectivoOficial;
                });

                // Actualizar los totales en la tabla (en la fila correspondiente)
                $('#total-vendido').text(totalVendido.toFixed(2));
                $('#total-qr').text(totalQr.toFixed(2));
                $('#total-efectivo').text(totalEfectivo.toFixed(2));
                $('#total-qr-oficial').text(totalQrOficial.toFixed(2));
                $('#total-efectivo-oficial').text(totalEfectivoOficial.toFixed(2));
            };

            // Iniciar DataTable
            var table = $('#cajaSucursalesTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ url(route('caja_sucursal.index')) }}',
                    data: function(d) {
                        d.fecha_inicio = $('#fecha_inicio').val(); // Fecha de inicio
                        d.fecha_fin = $('#fecha_fin').val(); // Fecha de fin
                    },
                    dataSrc: function(json) {
                        return json.data;
                    }
                },
                columns: [{
                        data: 'fechas',
                        className: 'fecha-campo'
                    },
                    {
                        data: 'sucursal'
                    },
                    {
                        data: 'total_vendido'
                    },
                    {
                        data: 'qr'
                    },
                    {
                        data: 'efectivo'
                    },
                    {
                        data: 'qr_oficial'
                    },
                    {
                        data: 'efectivo_oficial'
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [
                    [0, 'desc']
                ],
                drawCallback: function(settings) {
                    // Llamar a la función para actualizar los totales cuando los datos se dibujan
                    updateTotals();
                }
            });

            // Evento de envío del formulario de filtro
            $('#filterForm').on('submit', function(e) {
                e.preventDefault();
                table.ajax.reload(); // Recargar los datos de la tabla con los nuevos filtros
            });
        });
    </script>
@stop
