@extends('adminlte::page')

@section('content')
    <h1 class="text-center mb-4">Crear Producto</h1>
    <div class="container">
        <form action="{{ route('productos.store') }}" method="POST" enctype="multipart/form-data"
            class="bg-light p-4 rounded shadow-sm mx-auto" style="max-width: 800px;">
            @csrf

            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="nombre">Nombre</label>
                    <input type="text" name="nombre" class="form-control" required>
                </div>

                <div class="col-md-6 form-group">
                    <label for="descripcion">Descripción</label>
                    <textarea name="descripcion" class="form-control" rows="4" required></textarea>
                </div>
            </div>


            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="precio">Precio</label>
                    <input type="number" step="0.01" name="precio" class="form-control" required>
                </div>
                <div class="col-md-6 form-group">
                    <label for="precio_descuento">Stock Inicial</label>
                    <input type="number" step="0.01" name="precio_descuento" class="form-control">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="stock">Stock</label>
                    <input type="number" name="stock" class="form-control" required>
                </div>
                <div class="col-md-6 form-group">
                    <label>Estado</label><br>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="estado" id="estado-activo" value="1"
                            required>
                        <label class="form-check-label" for="estado-activo">DESTACADO</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="estado" id="estado-inactivo" value="0"
                            required>
                        <label class="form-check-label" for="estado-inactivo">NO DESTACADO</label>
                    </div>
                </div>

            </div>


            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="fecha">Fecha</label>
                    <input type="date" name="fecha" class="form-control" required id="fecha-input">
                </div>

                <div class="col-md-6 form-group">
                    <label for="id_cupo">Cupo</label>
                    <select name="id_cupo" class="form-control">
                        <option value="">Seleccione</option>
                        @foreach ($cupos as $cupo)
                            <option value="{{ $cupo->id }}">{{ $cupo->codigo }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 form-group">
                    <label for="id_tipo">Tipo</label>
                    <select name="id_tipo" class="form-control" required>
                        @foreach ($tipos as $tipo)
                            <option value="{{ $tipo->id }}">{{ $tipo->tipo }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4 form-group">
                    <label for="id_categoria">Categoría</label>
                    <select name="id_categoria" class="form-control" required>
                        @foreach ($categorias as $categoria)
                            <option value="{{ $categoria->id }}">{{ $categoria->categoria }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 form-group">
                    <label for="id_marca">Marca</label>
                    <select name="id_marca" class="form-control" required>
                        @foreach ($marcas as $marca)
                            <option value="{{ $marca->id }}">{{ $marca->marca }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row">
                

                <div class="col-md-6 form-group">
                    <label for="fotos">Fotos</label>
                    <input type="file" name="fotos[]" class="form-control" multiple accept="image/jpeg, image/png"
                        id="file-input">
                </div>
            </div>


            <!-- Contenedor para las previsualizaciones -->
            <div id="preview" class="mt-3"></div>

            <div class="d-flex justify-content-end mt-3">
                <a href="{{ route('productos.index') }}" class="btn btn-secondary me-2">
                    <i class="fas fa-arrow-left"></i> Volver Atrás
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-plus"></i> Crear Producto
                </button>
            </div>
        </form>
    </div>
    <script>
        // Establecer la fecha actual en el campo de entrada
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0]; // Obtener la fecha actual en formato YYYY-MM-DD
            document.getElementById('fecha-input').value = today; // Asignar la fecha al campo
        });
    </script>
    <script>
        document.getElementById('file-input').addEventListener('change', function(event) {
            const preview = document.getElementById('preview');
            preview.innerHTML = ''; // Limpiar el contenedor de previsualización

            const files = event.target.files;
            const validTypes = ['image/jpeg', 'image/png'];
            let validFiles = [];

            for (let i = 0; i < files.length; i++) {
                if (validTypes.includes(files[i].type)) {
                    validFiles.push(files[i]);
                } else {
                    alert("Solo se permiten archivos JPG y PNG.");
                }
            }

            if (validFiles.length === 0) {
                event.target.value = ''; // Limpiar el input si no hay archivos válidos
                return;
            }

            validFiles.forEach(file => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.width = '100px'; // Ajustar el tamaño según sea necesario
                    img.style.marginRight = '10px';
                    preview.appendChild(img);
                };
                reader.readAsDataURL(file);
            });
        });
    </script>
@endsection
