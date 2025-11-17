@extends('adminlte::page')

@section('title', 'Reporte de Pedidos de Productos')

@section('content')
<div class="container">
    <div class="card shadow mb-4">
        <div class="card-header bg-primary text-white">
            <h2 class="card-title">Reporte de Pedidos de Productos - Importadora Miranda</h2>
        </div>
        <div class="card-body">
            <!-- Filtro de fechas -->
            <form action="{{ route('reporte.pedidos_producto') }}" method="GET" class="form-inline mb-4">
                <div class="form-group mr-3">
                    <label for="fecha_inicio" class="mr-2 font-weight-bold">Fecha Inicio:</label>
                    <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control" value="{{ request('fecha_inicio') }}">
                </div>
                <div class="form-group mr-3">
                    <label for="fecha_fin" class="mr-2 font-weight-bold">Fecha Fin:</label>
                    <input type="date" id="fecha_fin" name="fecha_fin" class="form-control" value="{{ request('fecha_fin') }}">
                </div>
                <button type="submit" class="btn btn-primary font-weight-bold mr-2">
                    <i class="fas fa-filter"></i> Buscar
                </button>
                <a href="{{ route('reporte.pedidos_producto.pdf', ['start_date' => request('start_date'), 'end_date' => request('end_date')]) }}" 
                   class="btn btn-success font-weight-bold" target="_blank">
                    <i class="fas fa-file-pdf"></i> Generar PDF
                </a>
            </form>

            <!-- Resumen de pedidos de productos -->
            <div class="mb-4 p-3 bg-light rounded shadow-sm">
                <h3 class="text-secondary font-weight-bold">Resumen de Pedidos de Productos</h3>
                <ul class="list-unstyled">
                    <li><strong>Total de Pedidos:</strong> {{ $totalPedidos }}</li>
                    <li><strong>Total de Cantidad:</strong> {{ $totalCantidad }}</li>
                    <li><strong>Total de Precio:</strong> {{ number_format($totalPrecio, 2) }} Bs</li>
                </ul>
            </div>

            <!-- Tabla de Detalles de Pedidos de Productos -->
            <div class="table-responsive">
                <table id="pedidos_producto-reporte-table" class="table table-bordered table-striped table-hover shadow-sm">
                    <thead class="thead-dark">
                        <tr>
                            <th>Nombre</th>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio</th>
                            <th>Usuario</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pedidoProductos as $pedidoProducto)
                            <tr>
                                <td>{{ $pedidoProducto->pedido->nombre }}</td>
                                <td>{{ $pedidoProducto->producto->nombre }}</td>
                                <td>{{ $pedidoProducto->cantidad }}</td>
                                <td>{{ number_format($pedidoProducto->precio, 2) }} Bs</td>
                                <td>{{ $pedidoProducto->usuario->name }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
    <style>
        .card-header {
            background: linear-gradient(90deg, rgba(75,178,180,1) 0%, rgba(0,15,173,1) 50%, rgba(99,38,190,1) 100%);
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
        .form-group label {
            font-size: 14px;
        }
    </style>
@endpush

@push('js')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
    <script src="//cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#pedidos_producto-reporte-table').DataTable({
                "paging": true,
                "lengthChange": false,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.19/i18n/Spanish.json"
                }
            });
        });
    </script>
@endpush
