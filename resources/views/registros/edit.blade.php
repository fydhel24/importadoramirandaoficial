@extends('adminlte::page')
@section('title', 'Editar Registro')

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('prodregistromalestado.update', $registro->id) }}" method="POST" id="editForm">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label>Celular</label>
                    <input type="text" name="celular" class="form-control" value="{{ $registro->celular }}" required>
                </div>

                <div class="mb-3">
                    <label>Persona</label>
                    <input type="text" name="persona" class="form-control" value="{{ $registro->persona }}" required>
                </div>

                <div class="mb-3">
                    <label>Departamento</label>
                    <input type="text" name="departamento" class="form-control" value="{{ $registro->departamento }}"
                        required>
                </div>

                <!-- Producto con búsqueda -->
                <div class="mb-3" style="position: relative;">
                    <label>Producto</label>
                    <input type="text" id="producto_search" class="form-control" placeholder="Buscar producto..."
                        autocomplete="off">
                    <input type="hidden" name="producto_id" id="producto_id" value="{{ $registro->producto_id }}" required>
                    <div id="producto_dropdown" class="dropdown-menu w-100"
                        style="max-height: 200px; overflow-y: auto; display: none;"></div>
                    <div id="producto_selected" class="mt-2">
                        <span class="badge badge-success">
                            {{ $registro->producto->nombre ?? '' }}
                            <button type="button" class="btn btn-sm btn-link text-white p-0 ml-1" onclick="clearProduct()">
                                <i class="fas fa-times"></i>
                            </button>
                        </span>
                    </div>
                </div>

                <div class="mb-3">
                    <label>Estado</label>
                    <select name="estado" class="form-control" required>
                        <option value="mal" {{ $registro->estado == 'mal' ? 'selected' : '' }}>Mal Estado</option>
                        <option value="bueno" {{ $registro->estado == 'bueno' ? 'selected' : '' }}>Buen Estado</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label>Descripción del Problema</label>
                    <input type="text" name="descripcion_problema" class="form-control"
                        value="{{ $registro->descripcion_problema }}">
                </div>

                <div class="mb-3">
                    @foreach (['de_la_paz', 'enviado', 'extra1', 'extra2', 'extra3', 'extra4', 'extra5'] as $checkbox)
                        <div class="form-check form-check-inline">
                            <input type="checkbox" name="{{ $checkbox }}" class="form-check-input" value="1"
                                {{ $registro->$checkbox ? 'checked' : '' }}>
                            <label class="form-check-label">{{ ucfirst(str_replace('_', ' ', $checkbox)) }}</label>
                        </div>
                    @endforeach
                </div>

                <button type="submit" class="btn btn-primary">Actualizar Registro</button>
            </form>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Buscador de productos
            $('#producto_search').on('input', function() {
                const term = $(this).val();
                const dropdown = $('#producto_dropdown');

                if (term.length === 0) {
                    dropdown.hide().empty();
                    return;
                }

                $.get('{{ route('productos.buscar') }}', {
                    term: term
                }, function(data) {
                    dropdown.empty();
                    if (data.length > 0) {
                        data.forEach(function(product) {
                            dropdown.append(
                                `<a href="#" class="dropdown-item producto-option" data-id="${product.id}" data-name="${product.nombre}">${product.nombre}</a>`
                                );
                        });
                        dropdown.show();
                    } else {
                        dropdown.hide();
                    }
                });
            });

            // Seleccionar producto
            $(document).on('click', '.producto-option', function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                const name = $(this).data('name');

                $('#producto_id').val(id);
                $('#producto_search').val('');
                $('#producto_dropdown').hide();
                $('#producto_selected').html(
                    `<span class="badge badge-success">${name} <button type="button" class="btn btn-sm btn-link text-white p-0 ml-1" onclick="clearProduct()"><i class="fas fa-times"></i></button></span>`
                    );
            });

            // Click fuera cierra dropdown
            $(document).click(function(e) {
                if (!$(e.target).closest('#producto_search, #producto_dropdown').length) {
                    $('#producto_dropdown').hide();
                }
            });

            // Validación al enviar
            $('#editForm').on('submit', function(e) {
                if ($('#producto_id').val() === '') {
                    e.preventDefault();
                    alert('Por favor seleccione un producto');
                    $('#producto_search').focus();
                }
            });
        });

        function clearProduct() {
            $('#producto_id').val('');
            $('#producto_selected').html('');
            $('#producto_search').focus();
        }
    </script>
@stop
