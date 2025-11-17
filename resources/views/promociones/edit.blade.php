@extends('adminlte::page')
@section('content')
<div class="container">
    <h1 class="mb-4 text-center">Editar Promoción</h1>
    <form action="{{ route('promociones.update', $promocion->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre</label>
            <input type="text" name="nombre" id="nombre" class="form-control form-futuristic" value="{{ $promocion->nombre }}" required>
        </div>

        <div class="mb-3">
            <label for="id_sucursal" class="form-label">Sucursal</label>
            <select name="id_sucursal" id="id_sucursal" class="form-control form-futuristic" required>
                @foreach($sucursales as $sucursal)
                    <option value="{{ $sucursal->id }}" {{ $promocion->id_sucursal == $sucursal->id ? 'selected' : '' }}>
                        {{ $sucursal->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="buscar_producto" class="form-label">Buscar Producto</label>
            <input type="text" id="buscar_producto" class="form-control form-futuristic" placeholder="Escribe para buscar productos..." list="sugerencias_productos">
            <datalist id="sugerencias_productos">
                @foreach($productos as $producto)
                    <option value="{{ $producto->nombre }}" data-id="{{ $producto->id }}" data-precio="{{ $producto->precio }}">
                @endforeach
            </datalist>
        </div>

        <div class="table-responsive">
            <table class="table table-futuristic">
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
                <tbody id="productos_seleccionados">
                    @foreach($promocion->productos as $producto)
                        <tr id="producto-{{ $producto->id }}">
                            <td>{{ $producto->id }}</td>
                            <td>{{ $producto->nombre }}</td>
                            <td><input type="number" name="productos[{{ $producto->id }}][precio]" value="{{ $producto->pivot->precio_unitario }}" class="form-control precio" step="0.01"></td>
                            <td><input type="number" name="productos[{{ $producto->id }}][cantidad]" value="{{ $producto->pivot->cantidad }}" class="form-control cantidad" step="1" min="1"></td>
                            <td><input type="number" name="productos[{{ $producto->id }}][total]" value="{{ $producto->pivot->cantidad * $producto->pivot->precio_unitario }}" class="form-control total" step="0.01"></td>
                            <td><button type="button" class="btn btn-danger btn-sm eliminar">Eliminar</button></td>
                            <input type="hidden" name="productos[{{ $producto->id }}][id]" value="{{ $producto->id }}">
                        </tr>
                    @endforeach
                </tbody>
                
            </table>
        </div>

        <div class="mb-3">
            <label for="precio_promocion" class="form-label">Precio Total de la Promoción</label>
            <input type="number" step="0.01" name="precio_promocion" id="precio_promocion" class="form-control form-futuristic" value="{{ $promocion->precio_promocion }}" readonly>
        </div>

        <div class="mb-3">
            <label for="fecha_inicio" class="form-label">Fecha de Inicio</label>
            <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control form-futuristic" value="{{ $promocion->fecha_inicio }}" required>
        </div>

        <div class="mb-3">
            <label for="fecha_fin" class="form-label">Fecha de Fin</label>
            <input type="date" name="fecha_fin" id="fecha_fin" class="form-control form-futuristic" value="{{ $promocion->fecha_fin }}" required>
        </div>

        <div class="form-check">
            <input type="checkbox" name="estado" id="estado" class="form-check-input" {{ $promocion->estado ? 'checked' : '' }}>
            <label for="estado" class="form-check-label">Activo</label>
        </div>

        <button type="submit" class="btn btn-futuristic">Actualizar Promoción</button>
    </form>
</div>

<style>
    .form-futuristic {
        background: #1d3557;
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: 10px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        transition: all 0.3s ease;
    }

    .form-futuristic:focus {
        outline: none;
        box-shadow: 0 0 10px #457b9d;
    }

    .btn-futuristic {
        background: linear-gradient(135deg, #457b9d, #1d3557);
        color: white;
        border: none;
        border-radius: 20px;
        padding: 10px 20px;
        font-size: 14px;
        font-weight: bold;
        text-transform: uppercase;
        transition: transform 0.3s, background 0.3s;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }

    .btn-futuristic:hover {
        transform: scale(1.05);
        background: linear-gradient(135deg, #1d3557, #457b9d);
    }

    .table-futuristic {
        background: #f1faee;
        border: 1px solid #457b9d;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }

    .table-futuristic th {
        background: #457b9d;
        color: white;
        text-align: center;
        padding: 10px;
    }

    .table-futuristic td {
        text-align: center;
        padding: 10px;
    }

    /* Color negro para los precios */
    .form-control.precio, .form-control.total {
        color: black;
    }

    /* Color negro para el precio total de la promoción */
    #precio_promocion {
        color: black;
    }
</style>
<script>

document.addEventListener('DOMContentLoaded', function () {
    const buscarInput = document.getElementById('buscar_producto');
    const sugerencias = document.getElementById('sugerencias_productos');
    const tablaProductos = document.getElementById('productos_seleccionados');
    const precioPromocionInput = document.getElementById('precio_promocion');
    const fechaInicioInput = document.getElementById('fecha_inicio');
    const fechaFinInput = document.getElementById('fecha_fin');
    const estadoCheckbox = document.getElementById('estado');

    // Actualizar el total general de la promoción
    function actualizarTotalPromocion() {
        let total = 0;
        tablaProductos.querySelectorAll('tr').forEach(row => {
            const totalInput = row.querySelector('.total');
            total += parseFloat(totalInput.value) || 0;
        });
        precioPromocionInput.value = total.toFixed(2);
    }

    // Redistribuir precios y totales cuando el total general cambia
    precioPromocionInput.addEventListener('input', function () {
        const nuevoTotalPromocion = parseFloat(precioPromocionInput.value) || 0;
        const filas = Array.from(tablaProductos.querySelectorAll('tr'));
        const totalActual = filas.reduce((sum, row) => sum + (parseFloat(row.querySelector('.total').value) || 0), 0);

        if (totalActual === 0 || filas.length === 0) return; // Evitar división por cero o sin productos

        filas.forEach(row => {
            const totalInput = row.querySelector('.total');
            const cantidadInput = row.querySelector('.cantidad');
            const precioInput = row.querySelector('.precio');

            const proporcion = parseFloat(totalInput.value) / totalActual;
            const nuevoTotalProducto = nuevoTotalPromocion * proporcion;

            totalInput.value = nuevoTotalProducto.toFixed(2);
            precioInput.value = (nuevoTotalProducto / parseFloat(cantidadInput.value)).toFixed(2);
        });
    });

    // Verificar el estado de la promoción según las fechas
    function actualizarEstado() {
        const fechaInicio = new Date(fechaInicioInput.value);
        const fechaFin = new Date(fechaFinInput.value);
        const hoy = new Date();

        const estaActivo = hoy >= fechaInicio && hoy <= fechaFin;
        estadoCheckbox.checked = estaActivo;
    }

    // Agregar producto al listado
    buscarInput.addEventListener('change', function () {
        const nombreSeleccionado = buscarInput.value;
        const optionSeleccionada = Array.from(sugerencias.options).find(option => option.value === nombreSeleccionado);

        if (!optionSeleccionada) {
            alert('Producto no válido. Selecciona uno de la lista.');
            buscarInput.value = '';
            return;
        }

        const id = optionSeleccionada.dataset.id;
        const precio = parseFloat(optionSeleccionada.dataset.precio).toFixed(2);
        const nombre = nombreSeleccionado;

        if (document.querySelector(`#producto-${id}`)) {
            alert('El producto ya está agregado.');
            buscarInput.value = '';
            return;
        }

        const fila = document.createElement('tr');
        fila.id = `producto-${id}`;
        fila.innerHTML = `
            <td>${id}</td>
            <td>${nombre}</td>
            <td><input type="number" name="productos[${id}][precio]" value="${precio}" class="form-control precio" step="0.01"></td>
            <td><input type="number" name="productos[${id}][cantidad]" value="1" class="form-control cantidad" step="1" min="1"></td>
            <td><input type="number" name="productos[${id}][total]" value="${precio}" class="form-control total" step="0.01"></td>
            <td><button type="button" class="btn btn-danger btn-sm eliminar">Eliminar</button></td>
            <input type="hidden" name="productos[${id}][id]" value="${id}">
        `;
        tablaProductos.appendChild(fila);

        agregarEventosFila(fila);
        actualizarTotalPromocion();
        buscarInput.value = '';
    });

    // Agregar eventos a una fila específica
    function agregarEventosFila(fila) {
        fila.querySelector('.eliminar').addEventListener('click', function () {
            fila.remove();
            actualizarTotalPromocion();
        });

        fila.querySelector('.precio').addEventListener('input', function () {
            const cantidadInput = fila.querySelector('.cantidad');
            const totalInput = fila.querySelector('.total');
            const nuevoPrecio = parseFloat(this.value) || 0;
            const cantidad = parseFloat(cantidadInput.value) || 1;

            totalInput.value = (nuevoPrecio * cantidad).toFixed(2);
            actualizarTotalPromocion();
        });

        fila.querySelector('.cantidad').addEventListener('input', function () {
            const precioInput = fila.querySelector('.precio');
            const totalInput = fila.querySelector('.total');
            const cantidad = parseFloat(this.value) || 1;
            const precio = parseFloat(precioInput.value) || 0;

            totalInput.value = (cantidad * precio).toFixed(2);
            actualizarTotalPromocion();
        });

        fila.querySelector('.total').addEventListener('input', function () {
            const cantidadInput = fila.querySelector('.cantidad');
            const precioInput = fila.querySelector('.precio');
            const nuevoTotal = parseFloat(this.value) || 0;
            const cantidad = parseFloat(cantidadInput.value) || 1;

            if (cantidad > 0) {
                precioInput.value = (nuevoTotal / cantidad).toFixed(2);
            }
            actualizarTotalPromocion();
        });
    }

    // Inicializar filas existentes
    tablaProductos.querySelectorAll('tr').forEach(fila => {
        agregarEventosFila(fila);
        const cantidadInput = fila.querySelector('.cantidad');
        const precioInput = fila.querySelector('.precio');
        const totalInput = fila.querySelector('.total');

        // Recalcular inicial si hay datos precargados
        const cantidad = parseFloat(cantidadInput.value) || 1;
        const precio = parseFloat(precioInput.value) || 0;
        totalInput.value = (cantidad * precio).toFixed(2);
    });

    // Detectar cambios en las fechas para actualizar el estado
    fechaInicioInput.addEventListener('change', actualizarEstado);
    fechaFinInput.addEventListener('change', actualizarEstado);

    // Verificar el estado al cargar la página
    actualizarEstado();

    // Calcular el total inicial
    actualizarTotalPromocion();
});


</script>
@endsection
