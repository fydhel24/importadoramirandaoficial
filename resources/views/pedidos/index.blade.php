@extends('adminlte::page')

@section('title', 'Pedidos por Semana')

@section('content_header')
    <h1>Pedidos para la Semana Seleccionada</h1>
@stop

@section('content')
    <div class="container-fluid mt-4">

        <div class="row">
            <div class="col-lg-4 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ number_format($totalMontoDeposito, 2) }} BS</h3>
                        <p>Monto Total de Depósitos</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ number_format($totalMontoEnviado, 2) }} BS</h3>
                        <p>Monto Total Enviado/Pagado</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ number_format($totalDiferencia, 2) }} BS</h3>
                        <p>Diferencia (Depósito - Enviado/Pagado)</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-balance-scale"></i>
                    </div>
                </div>
            </div>

            <!-- Agrega más tarjetas según sea necesario -->
        </div>
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Pedidos de {{ $semana->nombre }}</h2>
                <div class="card-tools d-flex flex-wrap justify-content-between">
                    <div class="btn-group mb-2">
                        <a href="#" class="btn btn-primary btn-sm me-2" id="generarNotaVentaBtn">
                            <i class="fas fa-file-pdf"></i> Generar Nota de Venta
                             </a>
                        <a href="#" class="btn btn-warning btn-sm me-2" id="generarPdfSeleccionadosBtn1">
                            <i class="fas fa-file-pdf"></i> Generar Reporte BCP por pedido
                        </a>
                        <a href="#" class="btn btn-danger btn-sm" id="generarPdfSeleccionadosBtn2">
                            <i class="fas fa-file-pdf"></i> Generar Reporte de Fichas por pedido
                        </a>
                        <a href="#" class="btn btn-warning btn-sm me-2" id="generarPdfSeleccionadosBtn3">
                            <i class="fas fa-file-pdf"></i> Resumen BCP
                        </a>
                    </div>

                    <div class="btn-group mb-2">
                        @can('orden.create')
                            <a href="{{ route('orden.create', $id) }}" class="btn btn-primary btn-sm me-2">
                                <i class="fas fa-plus"></i> Crear Pedido
                            </a>
                        @endcan
                        <a href="{{ route('orden.pdf', $id) }}" class="btn btn-success btn-sm me-2">
                            <i class="fas fa-file-pdf"></i> BCP
                        </a>
                        <a href="{{ route('orden.pdf.generate', $id) }}" class="btn btn-info btn-sm">
                            <i class="fas fa-file-alt"></i> Reporte de Fichas
                        </a>
                    </div>

                    <!-- Botón para abrir el modal -->
                    <button type="button" class="btn btn-primary mb-2" data-toggle="modal" data-target="#destinoModal"
                        style="display: none;">
                        Seleccionar Destinos
                    </button>
                </div>

            </div>
            <div class="card-body">
                @if (session('success'))
                    <div id="success-alert" class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if (session('error'))
                    <div id="error-alert" class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <!-- Buscador -->
                <div class="mb-3">
                    <input type="text" id="searchInput" class="form-control" placeholder="Buscar en la tabla...">
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="thead-light">
                            <tr>
                                <th><input type="checkbox" id="selectAll"></th>
                                <th>No</th>
                                <th>Nombre</th>
                                <th>CI</th>
                                <th>Celular</th>
                                <th>Destino</th>
                                <th>Dirección</th>
                                <th>Estado</th>
                                <th>Cantidad Productos</th>
                                <th>Detalle</th>
                                <th>Productos</th>
                                <th>Monto Depósito</th>
                                <th>Monto Enviado/Pagado</th>
                                <th>Fecha</th>
                                <th>Id Semana</th>
                                <th>Código</th> <!-- Nueva columna para el código -->
                                <th>Foto Comprobante</th> <!-- Nueva columna para la foto -->
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            @forelse($pedidos as $pedido)
                                <tr>
                                    <td><input type="checkbox" class="pedido-checkbox" name="pedidos[]"
                                            value="{{ $pedido->id }}"></td>
                                    <td>{{ $pedido->id }}</td>
                                    <td>{{ $pedido->nombre }}</td>
                                    <td>{{ $pedido->ci }}</td>
                                    <td>{{ $pedido->celular }}</td>
                                    <td>{{ $pedido->destino }}</td>
                                    <td>{{ $pedido->direccion }}</td>
                                    <td>{{ $pedido->estado }}</td>
                                    <td>{{ $pedido->cantidad_productos }}</td>
                                    <td>{{ $pedido->detalle }}</td>
                                    <td>{{ $pedido->productos }}</td>
                                    <td>{{ number_format($pedido->monto_deposito, 2) }}</td>
                                    <td>{{ number_format($pedido->monto_enviado_pagado, 2) }}</td>
                                    <td>{{ $pedido->fecha }}</td>
                                    <td>{{ $pedido->semana->nombre }}</td>
                                    <td>{{ $pedido->codigo }}</td> <!-- Muestra el código -->
                                    <td>
                                        @if ($pedido->foto_comprobante)
                                            <img src="{{ asset('storage/' . $pedido->foto_comprobante) }}"
                                                alt="Comprobante" style="width: 100px; height: auto;">
                                        @else
                                            Sin foto
                                        @endif
                                    </td>

                                    <td>
                                        @can('orden.edit')
                                            <a href="{{ route('orden.edit', $pedido->id) }}" class="btn btn-light btn-sm"
                                                style="color: #007bff; border-color: #007bff; background-color: #f0f8ff;">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="{{ route('nota.venta', ['pedidoId' => $pedido->id]) }}" class="btn btn-primary">
                                                <i class="fa fa-file-invoice" aria-hidden="true"></i>
                                            </a>
                                        @endcan

                                        @can('orden.destroy')
                                            <form action="{{ route('orden.destroy', $pedido->id) }}" method="POST"
                                                style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-light btn-sm"
                                                    style="color: #dc3545; border-color: #dc3545; background-color: #f8d7da;">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        @endcan

                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="17" class="text-center">No hay pedidos para esta semana.</td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="destinoModal" tabindex="-1" role="dialog" aria-labelledby="destinoModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="destinoModalLabel">Seleccionar Destinos</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="destinoForm">
                        @foreach (['Cochabamba', 'Santa Cruz', 'Oruro', 'Sucre', 'Tarija', 'Potosi', 'Trinidad', 'Cobija', 'Provincias', 'Camiri', 'Robore', 'Puerto Suarez', 'Riberalta', 'Rurrenabaque', 'Yacuiba', 'Tupiza', 'Villamontes', 'Bermejo', 'Zona Central', 'El Alto', 'Zona Miraflores', 'Zona Sur', 'Zona Sopocachi'] as $destino)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="destinos[]"
                                    value="{{ $destino }}" id="{{ $destino }}">
                                <label class="form-check-label" for="{{ $destino }}">
                                    {{ $destino }}
                                </label>
                            </div>
                        @endforeach
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-success" id="generarBcpBtn">Generar BCP</button>
                    <button type="button" class="btn btn-info" id="generarReporteFichaBtn">Generar Reporte de
                        Fichas</button>
                </div>
            </div>
        </div>
    </div>


    @section('js')
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/font-awesome@5.15.4/js/all.min.js"></script>



        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const searchInput = document.getElementById('searchInput');
                const tableBody = document.getElementById('tableBody');

                searchInput.addEventListener('input', function() {
                    const filter = searchInput.value.toLowerCase();
                    const rows = tableBody.getElementsByTagName('tr');

                    Array.from(rows).forEach(row => {
                        const cells = row.getElementsByTagName('td');
                        let match = false;

                        Array.from(cells).forEach(cell => {
                            if (cell.textContent.toLowerCase().includes(filter)) {
                                match = true;
                            }
                        });

                        row.style.display = match ? '' : 'none';
                    });
                });

                // Tiempo en milisegundos antes de ocultar las alertas (por ejemplo, 5 segundos = 5000 milisegundos)
                const alertTimeout = 5000;

                // Ocultar alerta de éxito
                const successAlert = document.getElementById('success-alert');
                if (successAlert) {
                    setTimeout(() => {
                        $(successAlert).alert('close');
                    }, alertTimeout);
                }

                // Ocultar alerta de error
                const errorAlert = document.getElementById('error-alert');
                if (errorAlert) {
                    setTimeout(() => {
                        $(errorAlert).alert('close');
                    }, alertTimeout);
                }
            });
        </script>
        <script>
            document.getElementById('generarBcpBtn').addEventListener('click', function() {
                const destinosSeleccionados = getSelectedDestinos();
                if (destinosSeleccionados.length > 0) {
                    const idSemana =
                        {{ $id }}; // Suponiendo que tienes el ID de la semana disponible en la vista
                    const url =
                        `{{ route('orden.pdf.nuevo', '') }}/${idSemana}?destinos=${destinosSeleccionados.join(',')}`;
                    window.location.href = url;
                } else {
                    alert('Por favor, selecciona al menos un destino.');
                }
            });

            document.getElementById('generarReporteFichaBtn').addEventListener('click', function() {
                const destinosSeleccionados = getSelectedDestinos();
                if (destinosSeleccionados.length > 0) {
                    const idSemana = {{ $id }};
                    const url =
                        `{{ route('orden.pdf.generate', '') }}/${idSemana}?destinos=${destinosSeleccionados.join(',')}`;
                    window.location.href = url;
                } else {
                    alert('Por favor, selecciona al menos un destino.');
                }
            });

            function getSelectedDestinos() {
                const selectedDestinos = [];
                const checkboxes = document.querySelectorAll('input[name="destinos[]"]:checked');
                checkboxes.forEach((checkbox) => {
                    selectedDestinos.push(checkbox.value);
                });
                return selectedDestinos;
            };
        </script>
        <script>
            document.getElementById('selectAll').addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('.pedido-checkbox');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
            });

            document.addEventListener('DOMContentLoaded', function() {
                const generarPdfBtn = document.getElementById('generarPdfSeleccionadosBtn1');

                if (generarPdfBtn) {
                    generarPdfBtn.addEventListener('click', function() {
                        const selectedPedidos = getSelectedPedidos();
                        const idSemana = {{ $id }}; // Asegúrate de que esta variable esté definida

                        if (selectedPedidos.length > 0) {
                            const url =
                                `{{ route('orden.pdf.nuevo', '') }}/${idSemana}?pedidos=${selectedPedidos.join(',')}`;
                            window.location.href = url;
                        } else {
                            alert('Por favor, selecciona al menos un pedido.');
                        }
                    });
                }

                function getSelectedPedidos() {
                    const selectedIds = [];
                    const checkboxes = document.querySelectorAll('input[name="pedidos[]"]:checked');
                    checkboxes.forEach((checkbox) => {
                        selectedIds.push(checkbox.value);
                    });
                    return selectedIds;
                }
            });
            document.addEventListener('DOMContentLoaded', function() {
                const generarPdfBtn = document.getElementById('generarPdfSeleccionadosBtn3');

                if (generarPdfBtn) {
                    generarPdfBtn.addEventListener('click', function() {
                        const selectedPedidos = getSelectedPedidos();
                        const idSemana = {{ $id }}; // Asegúrate de que esta variable esté definida

                        if (selectedPedidos.length > 0) {
                            const url =
                                `{{ route('orden.pdf.bcResumen', '') }}/${idSemana}?pedidos=${selectedPedidos.join(',')}`;
                            window.location.href = url;
                        } else {
                            alert('Por favor, selecciona al menos un pedido.');
                        }
                    });
                }

                function getSelectedPedidos() {
                    const selectedIds = [];
                    const checkboxes = document.querySelectorAll('input[name="pedidos[]"]:checked');
                    checkboxes.forEach((checkbox) => {
                        selectedIds.push(checkbox.value);
                    });
                    return selectedIds;
                }
            });
        </script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const generarPdfBtn = document.getElementById('generarPdfSeleccionadosBtn2');

                if (generarPdfBtn) {
                    generarPdfBtn.addEventListener('click', function() {
                        const selectedPedidos = getSelectedPedidos();
                        const idSemana =
                            {{ $id }}; // Asegúrate de que esta variable esté definida en tu vista

                        if (selectedPedidos.length > 0) {
                            const url =
                                `{{ route('orden.pdf.nuevo.generate', '') }}/${idSemana}?pedidos=${selectedPedidos.join(',')}`;
                            window.location.href = url;
                        } else {
                            alert('Por favor, selecciona al menos un pedido.');
                        }
                    });
                }

                function getSelectedPedidos() {
                    const selectedIds = [];
                    const checkboxes = document.querySelectorAll('input[name="pedidos[]"]:checked');
                    checkboxes.forEach((checkbox) => {
                        selectedIds.push(checkbox.value);
                    });
                    return selectedIds;
                }
            });
            document.addEventListener('DOMContentLoaded', function() {
    const generarNotaVentaBtn = document.getElementById('generarNotaVentaBtn');

    if (generarNotaVentaBtn) {
        generarNotaVentaBtn.addEventListener('click', function() {
            const selectedPedidos = getSelectedPedidos();
            const idSemana = {{ $id }}; // Asegúrate de que esta variable esté definida en tu vista

            if (selectedPedidos.length > 0) {
                // Actualiza la URL con la ruta correcta
                const url = `{{ route('nota.imprimirSeleccionados') }}?semana=${idSemana}&pedidos=${selectedPedidos.join(',')}`;
                window.location.href = url;
            } else {
                alert('Por favor, selecciona al menos un pedido.');
            }
        });
    }

    function getSelectedPedidos() {
        const selectedIds = [];
        const checkboxes = document.querySelectorAll('input[name="pedidos[]"]:checked');
        checkboxes.forEach((checkbox) => {
            selectedIds.push(checkbox.value);
        });
        return selectedIds;
    }
});
        </script>
    @endsection
@endsection
