@extends('adminlte::page')

@section('content')
    <h1 class="text-center mb-4">Editar Producto</h1>

    <div class="container">
        <form action="{{ route('productos.update', $producto) }}" method="POST" enctype="multipart/form-data"
            class="bg-light p-4 rounded shadow-sm mx-auto" style="max-width: 800px;">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="nombre">Nombre</label>
                    <input type="text" name="nombre" value="{{ $producto->nombre }}" class="form-control" required>
                </div>

                <div class="col-md-6 form-group">
                    <label for="descripcion">Descripción</label>
                    <textarea name="descripcion" class="form-control" rows="4" required>{{ $producto->descripcion }}</textarea>
                </div>
            </div>


            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="precio">Precio</label>
                    <input type="number" step="0.01" name="precio" value="{{ $producto->precio }}" class="form-control"
                        required>
                </div>
                <div class="col-md-6 form-group">
                    <label for="precio_descuento">Stock Inicial</label>
                    <input type="number" step="0.01" name="precio_descuento" value="{{ $producto->precio_descuento }}"
                        class="form-control">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="stock">Stock</label>
                    <input type="number" name="stock" value="{{ $producto->stock }}" class="form-control" required>
                </div>
                <div class="col-md-6 form-group">
                    <label for="estado">Estado</label>
                    <div class="d-flex">
                        <div class="form-check me-3">
                            <input type="radio" name="estado" value="1" class="form-check-input" id="estadoActivo"
                                {{ $producto->estado ? 'checked' : '' }} required>
                            <label class="form-check-label" for="estadoActivo">DESTACADO</label>
                        </div>
                        <div class="form-check">
                            <input type="radio" name="estado" value="0" class="form-check-input" id="estadoInactivo"
                                {{ !$producto->estado ? 'checked' : '' }} required>
                            <label class="form-check-label" for="estadoInactivo">NO DESTACADO</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="fecha">Fecha</label>
                    <input type="date" name="fecha" value="{{ $producto->fecha }}" class="form-control" required>
                </div>

                <div class="col-md-6 form-group">
                    <label for="id_cupo">Cupo</label>
                    <select name="id_cupo" class="form-control">
                        <option value="">Seleccione</option>
                        @foreach ($cupos as $cupo)
                            <option value="{{ $cupo->id }}" {{ $producto->id_cupo == $cupo->id ? 'selected' : '' }}>
                                {{ $cupo->codigo }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 form-group">
                    <label for="id_tipo">Tipo</label>
                    <select name="id_tipo" class="form-control" required>
                        @foreach ($tipos as $tipo)
                            <option value="{{ $tipo->id }}" {{ $producto->id_tipo == $tipo->id ? 'selected' : '' }}>
                                {{ $tipo->tipo }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4 form-group">
                    <label for="id_categoria">Categoría</label>
                    <select name="id_categoria" class="form-control" required>
                        @foreach ($categorias as $categoria)
                            <option value="{{ $categoria->id }}"
                                {{ $producto->id_categoria == $categoria->id ? 'selected' : '' }}>
                                {{ $categoria->categoria }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4 form-group">
                    <label for="id_marca">Marca</label>
                    <select name="id_marca" class="form-control" required>
                        @foreach ($marcas as $marca)
                            <option value="{{ $marca->id }}" {{ $producto->id_marca == $marca->id ? 'selected' : '' }}>
                                {{ $marca->marca }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="fotos">Fotos (Opcional)</label>
                    <input type="file" name="fotos[]" class="form-control" multiple accept="image/*" id="file-input">
                </div>
            </div>

            <!-- Contenedor para las previsualizaciones -->
            <div id="preview" class="mt-3"></div>

            <div class="d-flex justify-content-end mt-3">
                <a href="{{ route('productos.index') }}" class="btn btn-secondary me-2">
                    <i class="fas fa-arrow-left"></i> Volver Atrás
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-edit"></i> Actualizar Producto
                </button>
            </div>
        </form>

        <h3 class="mt-4 text-center">Fotos actuales</h3>
        <div class="text-center">
            <ul class="list-unstyled d-inline-flex flex-wrap justify-content-center">
                @foreach ($fotos as $foto)
                    <li class="m-2">
                        <img src="{{ asset('storage/' . $foto->foto) }}" alt="Foto" style="width: 100px;">
                        <form action="{{ route('productos.fotos.destroy', $foto) }}" method="POST"
                            style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Eliminar Foto</button>
                        </form>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <script>
        document.getElementById('file-input').addEventListener('change', function(event) {
            const preview = document.getElementById('preview');
            preview.innerHTML = ''; // Limpiar el contenedor de previsualización

            const files = event.target.files;
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                const reader = new FileReader();

                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.width = '100px'; // Ajustar el tamaño según sea necesario
                    img.style.marginRight = '10px';
                    preview.appendChild(img);
                }

                reader.readAsDataURL(file);
            }
        });
    </script>
@endsection
