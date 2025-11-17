@extends('adminlte::page')
@section('content')
<div class="container">
    <h1 class="mb-4">Promociones</h1>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('promociones.create') }}" class="btn btn-primary">Crear Promoción</a>
        <input type="text" id="buscar_promocion" class="form-control w-50" placeholder="Buscar promoción...">
    </div>
    <div class="row" id="lista_promociones">
        @foreach($promociones as $promocion)
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title m-0">{{ $promocion->nombre }}</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Precio:</strong> Bs {{ $promocion->precio_promocion }}</p>
                        <p><strong>Estado:</strong> 
                            <span class="badge {{ $promocion->estado ? 'bg-success' : 'bg-danger' }}">
                                {{ $promocion->estado ? 'Activo' : 'Inactivo' }}
                            </span>
                        </p>
                        <p><strong>Sucursal:</strong> {{ $promocion->sucursal->nombre }}</p>
                        <p><strong>Creado por:</strong> {{ $promocion->usuario->name }}</p>
                        <p><strong>Productos:</strong></p>
                        <ul class="list-group list-group-flush">
                            @foreach($promocion->productos as $producto)
                                <li class="list-group-item">{{ $producto->nombre }}</li>
                            @endforeach
                        </ul>
                        <div class="mt-3 d-flex justify-content-between">
                            <button class="btn btn-success btn-sm open-modal" 
                                    data-toggle="modal" 
                                    data-target="#venderModal" 
                                    data-id="{{ $promocion->id }}" 
                                    data-precio="{{ $promocion->precio_promocion }}" 
                                    data-sucursal="{{ $promocion->sucursal->id }}">
                                <i class="fas fa-shopping-cart"></i> Vender
                            </button>
                            <a href="{{ route('promociones.edit', $promocion->id) }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            <form action="{{ route('promociones.destroy', $promocion->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash"></i> Eliminar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Modal de venta directamente incluido -->
<form id="ventaForm" method="POST" action="{{ route('control.fin') }}" target="_blank">
    @csrf
    <div id="venderModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="carritoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="carritoModalLabel">Productos en el Carrito</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table id="lista-carrito" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Precio</th>
                                    <th>Cantidad</th>
                                    <th>Total</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="cliente">Cliente</label>
                                <input type="text" name="nombre_cliente" id="cliente" class="form-control" placeholder="Ingrese nombre del cliente" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="ci">CI / NIT</label>
                                <input type="text" name="ci" id="ci" class="form-control" placeholder="Ingrese CI del cliente">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="descuento">Descuento (Bs)</label>
                                <input type="number" name="descuento" id="descuento" class="form-control" value="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="monto-total">Total Sin Descuento</label>
                                <input type="text" id="monto-total" name="monto_total_sin_descuento" class="form-control" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="total-a-pagar">Monto Total a Pagar</label>
                                <input type="text" name="costo_total" id="total-a-pagar" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="pagado">Monto Pagado</label>
                                <input type="number" id="pagado" class="form-control" placeholder="Ingrese monto pagado">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="cambio">Cambio</label>
                                <input type="text" id="cambio" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tipo_pago">Método de Pago</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tipo_pago" value="Efectivo" id="efectivo" checked>
                                    <label class="form-check-label" for="efectivo">Efectivo</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tipo_pago" value="QR" id="qr">
                                    <label class="form-check-label" for="qr">Transferencia QR</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-success">Confirmar Venta</button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = new bootstrap.Modal(document.getElementById('venderModal'));
        const openModalButtons = document.querySelectorAll('.open-modal');
        const totalSinDescuento = document.getElementById('monto-total');
        const totalPagar = document.getElementById('total-a-pagar');
        const pagado = document.getElementById('pagado');
        const cambio = document.getElementById('cambio');
        const descuento = document.getElementById('descuento');

        openModalButtons.forEach(button => {
            button.addEventListener('click', function () {
                const promocionPrecio = this.getAttribute('data-precio');
                totalSinDescuento.value = promocionPrecio;
                totalPagar.value = promocionPrecio;
            });
        });

        pagado.addEventListener('input', function () {
            const cambioCalculado = parseFloat(pagado.value || 0) - parseFloat(totalPagar.value || 0);
            cambio.value = cambioCalculado > 0 ? cambioCalculado.toFixed(2) : 0;
        });

        descuento.addEventListener('input', function () {
            const descuentoAplicado = parseFloat(descuento.value || 0);
            totalPagar.value = (parseFloat(totalSinDescuento.value || 0) - descuentoAplicado).toFixed(2);
        });
    });
</script>

<style>
    .card {
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .card:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    }

    .btn {
        border-radius: 25px;
    }
</style>
@endsection
