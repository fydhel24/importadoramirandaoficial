@extends('adminlte::page')

@section('content')
<div class="container">
    <h2>Generar Reporte de Producto</h2>

    <form action="{{ route('reportes.productos.generar') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="id_producto">Seleccionar Producto</label>
            <!-- Aplicamos SlimSelect al select -->
            <select name="id_producto" id="id_producto" class="form-control" required>
                <option value="">Seleccione un producto</option>
                @foreach($productos as $producto)
                    <option value="{{ $producto->id }}">{{ $producto->nombre }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="fecha_inicio">Fecha de Inicio</label>
            <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="fecha_fin">Fecha de Fin</label>
            <input type="date" name="fecha_fin" id="fecha_fin" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Generar Reporte</button>
    </form>
</div>
@endsection

@section('js')
    <!-- Incluir SlimSelect CSS & JS -->
    <script src="https://unpkg.com/slim-select@latest/dist/slimselect.min.js"></script>
    <link href="https://unpkg.com/slim-select@latest/dist/slimselect.css" rel="stylesheet">
    
    <script>
        $(document).ready(function() {
            // Inicializamos SlimSelect para el select de producto
            new SlimSelect({
                select: '#id_producto' // Asegúrate de que el ID coincida con el ID de tu select
            });
        });
    </script>
@endsection
