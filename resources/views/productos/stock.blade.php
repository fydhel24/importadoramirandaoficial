@extends('adminlte::page')

@section('title', 'Reporte de Productos')

@section('content')
    <div class="container">
        <div class="card shadow mb-4">
            <div class="card-header bg-primary text-white">
                <h2 class="card-title">Reporte de Productos</h2>
            </div>
            <div class="card-body">
                <!-- Formulario de Filtro de Fecha -->
                <h3>Productos vendidos :</h3>
                <form action="{{ route('report.stock') }}" method="GET" class="form-inline mb-4">
                    <div class="form-group mr-3">
                        <label for="start_date" class="mr-2 font-weight-bold">Desde Fecha Inicio:</label>
                        <input type="date" id="start_date" name="start_date" class="form-control"
                            value="{{ request('start_date', $startDate ? $startDate->toDateString() : '') }}">
                    </div>
                    <div class="form-group mr-3">
                        <label for="end_date" class="mr-2 font-weight-bold">Hasta Fecha Fin:</label>
                        <input type="date" id="end_date" name="end_date" class="form-control"
                            value="{{ request('end_date', $endDate ? $endDate->toDateString() : '') }}">
                    </div>
                    <button type="submit" class="btn btn-primary font-weight-bold mr-2">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                </form>

                <!-- Tabla de Productos -->
                <div class="table-responsive">
                    <table id="products-report-table" class="table table-bordered table-striped table-hover shadow-sm">
                        <thead class="thead-dark">
                            <tr>
                                <th>Nombre del Producto</th>
                                <th>Stock Incial</th>
                                <th>Stock en Almacen</th>
                                <th>Total Vendido entre fechas</th>
                                @foreach ($sucurnombre as $sucursal)
                                    <th>{{ $sucursal->nombre }}</th> <!-- Mostrar el nombre de la sucursal -->
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Los datos se cargarán automáticamente desde DataTables -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('js')
    <script>
        $(document).ready(function() {
            $('#products-report-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('report.stock') }}', // Ruta del controlador
                    data: function(d) {
                        // Pasamos las fechas seleccionadas en el filtro al servidor
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();

                        // Mostrar en consola los datos que se están enviando en la petición AJAX
                        console.log('Datos enviados a AJAX:', d);
                    },
                    error: function(xhr, error, thrown) {
                        // Mostrar en consola cualquier error de la petición AJAX
                        console.log('Error AJAX:', xhr.responseText);
                        console.log('Error:', error);
                        console.log('Thrown Error:', thrown);
                    }
                },
                columns: [{
                        data: 'nombre'
                    }, // Columna de Nombre del Producto
                    {
                        data: 'precio_descuento'
                    },
                    {
                        data: 'stock'
                    },
                    {
                        data: 'total_vendido'
                    },
                    @foreach ($sucursales as $sucursalId)
                        {
                            data: 'stocks.{{ $sucursalId }}',
                            render: function(data) {
                                return data || 0;
                            }
                        },
                    @endforeach
                ],
                language: {
                    decimal: ",",
                    thousands: ".",
                    processing: "Procesando...",
                    lengthMenu: "Mostrar _MENU_ registros",
                    info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                    infoEmpty: "No hay registros disponibles",
                    infoFiltered: "(filtrado de _MAX_ registros)",
                    search: "Buscar:",
                    zeroRecords: "No se encontraron registros",
                    emptyTable: "No hay datos disponibles en la tabla",
                    paginate: {
                        first: "Primero",
                        previous: "Anterior",
                        next: "Siguiente",
                        last: "Último"
                    }
                },
                // Limitar la búsqueda solo a la columna 'nombre'
                columnDefs: [{
                        targets: 0,
                        searchable: true
                    }, // Solo la primera columna (nombre) es searchable
                    {
                        targets: '_all',
                        searchable: false
                    } // Todas las demás columnas no son searchable
                ],
                drawCallback: function(settings) {
                    // Mostrar en consola los datos de la tabla después de que se haya dibujado
                    var dataTableData = settings.json.data;
                    console.log('Datos cargados en DataTables:', dataTableData);
                }
            });
        });
    </script>
@endsection
