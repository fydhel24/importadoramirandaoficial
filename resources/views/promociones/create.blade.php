@extends('adminlte::page')
@section('content')
<div class="container">
    <h1 class="mb-4">Crear Promoción</h1>
    <form action="{{ route('promociones.store') }}" method="POST">
        @csrf

        <!-- Nombre de la Promoción -->
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre de la Promoción</label>
            <input type="text" name="nombre" id="nombre" class="form-control form-futuristic" placeholder="Ingrese el nombre" required>
        </div>

        <!-- Sucursal -->
        <div class="mb-3">
            <label for="id_sucursal" class="form-label">Sucursal</label>
            <select name="id_sucursal" id="id_sucursal" class="form-control form-futuristic" required>
                <option value="" disabled selected>Seleccione una sucursal</option>
                @foreach($sucursales as $sucursal)
                    <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                @endforeach
            </select>
        </div>

        <!-- Buscar Producto -->
        <div class="mb-3">
            <label for="buscar_producto" class="form-label">Buscar Producto</label>
            <input type="text" id="buscar_producto" class="form-control form-futuristic" placeholder="Escribe para buscar productos..." list="sugerencias_productos" disabled>
            <datalist id="sugerencias_productos"></datalist>
        </div>

        <!-- Tabla de Productos Seleccionados -->
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
                    <!-- Productos seleccionados dinámicamente -->
                </tbody>
            </table>
        </div>

        <!-- Campo Oculto para Productos -->
        <input type="hidden" name="productos" id="productos_input">

        <!-- Precio Total -->
        <div class="mb-3">
            <label for="precio_promocion" class="form-label">Precio Total de la Promoción</label>
            <input type="number" step="0.01" name="precio_promocion" id="precio_promocion" class="form-control form-futuristic" readonly>
        </div>

        <!-- Fechas -->
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="fecha_inicio" class="form-label">Fecha de Inicio</label>
                <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control form-futuristic" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="fecha_fin" class="form-label">Fecha de Fin</label>
                <input type="date" name="fecha_fin" id="fecha_fin" class="form-control form-futuristic" required>
            </div>
        </div>

        <!-- Botón de Envío -->
        <button type="submit" class="btn btn-futuristic">Crear Promoción</button>
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
    const sucursalInput = document.getElementById('id_sucursal');
    const buscarInput = document.getElementById('buscar_producto');
    const sugerencias = document.getElementById('sugerencias_productos');
    const tablaProductos = document.getElementById('productos_seleccionados');
    const productosInput = document.getElementById('productos_input');
    const precioPromocionInput = document.getElementById('precio_promocion');
    const fechaInicioInput = document.getElementById('fecha_inicio');
    const fechaFinInput = document.getElementById('fecha_fin');

    // Lógica para fechas por defecto
    const fechaActual = new Date();
    const fechaFin = new Date();
    fechaFin.setDate(fechaActual.getDate() + 7); // 7 días desde la fecha actual

    const formatoFecha = (fecha) => {
        const anio = fecha.getFullYear();
        const mes = String(fecha.getMonth() + 1).padStart(2, '0');
        const dia = String(fecha.getDate()).padStart(2, '0');
        return `${anio}-${mes}-${dia}`;
    };

    fechaInicioInput.value = formatoFecha(fechaActual);
    fechaFinInput.value = formatoFecha(fechaFin);

    // Cargar productos por sucursal
    sucursalInput.addEventListener('change', function () {
        fetch(`/sucursales/${this.value}/productos`)
            .then(response => response.json())
            .then(data => {
                sugerencias.innerHTML = data.map(producto => {
                    const stockText = producto.stock > 0 ? `Stock: ${producto.stock}` : 'Stock: 0';
                    const disabled = producto.stock === 0 ? 'disabled' : '';
                    return `
                        <option value="${producto.nombre}" 
                                data-id="${producto.id}" 
                                data-precio="${producto.precio}" 
                                data-stock="${producto.stock}" 
                                ${disabled}>
                            ${producto.nombre} - ${stockText}
                        </option>`;
                }).join('');
                buscarInput.disabled = false;
            })
            .catch(() => alert('Error al cargar productos.'));
    });

    // Seleccionar producto
    buscarInput.addEventListener('change', function () {
        const option = Array.from(sugerencias.options).find(opt => opt.value === this.value);

        if (!option) {
            alert('Producto inválido.');
            buscarInput.value = '';
            return;
        }

        agregarProducto(option);
        this.value = ''; // Limpiar el campo de búsqueda
    });

    function agregarProducto(option) {
        const existe = Array.from(tablaProductos.querySelectorAll('tr')).some(row => row.dataset.id === option.dataset.id);

        if (existe) {
            alert('Este producto ya está en la lista.');
            return;
        }

        const fila = `
            <tr data-id="${option.dataset.id}">
                <td>${option.dataset.id}</td>
                <td>${option.value}</td>
                <td><input type="number" class="form-control precio" value="${option.dataset.precio}" step="0.01"></td>
                <td><input type="number" class="form-control cantidad" value="1" min="1" max="${option.dataset.stock}"></td>
                <td><input type="number" class="form-control total" value="${option.dataset.precio}" step="0.01"></td>
                <td><button type="button" class="btn btn-danger eliminar">Eliminar</button></td>
            </tr>`;
        tablaProductos.insertAdjacentHTML('beforeend', fila);
        actualizarProductos();
    }

    function actualizarProductos() {
        const productos = [];
        tablaProductos.querySelectorAll('tr').forEach(row => {
            const id = row.dataset.id;
            const cantidad = parseFloat(row.querySelector('.cantidad').value) || 0;
            const precio = parseFloat(row.querySelector('.precio').value) || 0;
            const total = parseFloat(row.querySelector('.total').value) || precio * cantidad;

            productos.push({ id, cantidad, precio }); // Agregar datos actualizados
        });

        productosInput.value = JSON.stringify(productos); // Actualizar el campo oculto
        actualizarTotal();
    }

    function actualizarTotal() {
        let totalGeneral = 0;
        tablaProductos.querySelectorAll('.total').forEach(totalInput => {
            totalGeneral += parseFloat(totalInput.value) || 0;
        });
        precioPromocionInput.value = totalGeneral.toFixed(2); // Actualizar el total general
    }

    // Eventos dinámicos para actualizar precios y totales
    tablaProductos.addEventListener('input', function (event) {
        const row = event.target.closest('tr');
        if (row) {
            const cantidad = parseFloat(row.querySelector('.cantidad').value) || 0;
            const precio = parseFloat(row.querySelector('.precio').value) || 0;

            if (event.target.classList.contains('precio') || event.target.classList.contains('cantidad')) {
                const total = precio * cantidad;
                row.querySelector('.total').value = total.toFixed(2); // Actualizar total
            } else if (event.target.classList.contains('total')) {
                const total = parseFloat(event.target.value) || 0;
                const nuevoPrecio = total / (cantidad || 1); // Actualizar precio unitario
                row.querySelector('.precio').value = nuevoPrecio.toFixed(2);
            }

            actualizarProductos(); // Actualizar todos los productos y el total general
        }
    });

    // Editar el total general
    precioPromocionInput.addEventListener('input', function () {
        const nuevoTotal = parseFloat(this.value) || 0;
        let totalActual = 0;

        const filas = tablaProductos.querySelectorAll('tr');
        filas.forEach((row, index) => {
            const cantidad = parseFloat(row.querySelector('.cantidad').value) || 1;
            let nuevoPrecio;

            // Distribuir proporcionalmente el total nuevo
            if (index === filas.length - 1) {
                nuevoPrecio = nuevoTotal - totalActual; // Ajuste final
            } else {
                const totalFila = parseFloat(row.querySelector('.total').value) || 0;
                nuevoPrecio = (nuevoTotal * totalFila) / totalActual;
                totalActual += totalFila;
            }

            row.querySelector('.precio').value = (nuevoPrecio / cantidad).toFixed(2);
            row.querySelector('.total').value = nuevoPrecio.toFixed(2);
        });

        actualizarProductos();
    });

    // Eliminar producto
    tablaProductos.addEventListener('click', function (event) {
        if (event.target.classList.contains('eliminar')) {
            event.target.closest('tr').remove();
            actualizarProductos();
        }
    });

    // Inicializar el total general al cargar la página
    actualizarTotal();
});


</script>

@endsection
