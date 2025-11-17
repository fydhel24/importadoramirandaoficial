@extends('adminlte::page')

@section('title', 'Categorías')

@section('content_header')
    <h1>Categorías</h1>
@stop

@section('content')
    <div class="container">
        @can('categorias.create')
            <div class="mb-3 text-right">
                <a href="{{ route('categorias.create') }}" class="btn btn-gradient-primary btn-lg">
                    <i class="fas fa-plus-circle"></i> Agregar Categoría
                </a>
            </div>
        @endcan

        <div class="card shadow-lg border-0" style="border-radius: 15px;">
            <div class="card-header bg-gradient-blue text-white" style="border-top-left-radius: 15px; border-top-right-radius: 15px;">
                <h3 class="card-title"><i class="fas fa-tags"></i> Categorías Registradas</h3>
            </div>
            <div class="card-body" style="background: #f8f9fa;">
                <!-- Contenedor de tabla responsiva -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="categoriasTable">
                        <thead class="linear-gradient">
                            <tr>
                                <th>No</th>
                                <th>Categoria</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($categorias as $categoria)
                                <tr class="animated fadeIn">
                                    <td>{{ ++$i }}</td>
                                    <td>{{ $categoria->categoria }}</td>
                                    <td class="text-center">
                                        <form action="{{ route('categorias.destroy', $categoria->id) }}" method="POST" style="display:inline;" id="delete-form-{{ $categoria->id }}">
                                            @csrf
                                            @method('DELETE')

                                            @can('categorias.show')
                                                <a class="btn btn-gradient-success btn-sm" href="{{ route('categorias.show', $categoria->id) }}" title="Ver categoría" style="border-radius: 5px;">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            @endcan

                                            @can('categorias.edit')
                                                <a class="btn btn-gradient-success btn-sm" href="{{ route('categorias.edit', $categoria->id) }}" title="Editar categoría" style="border-radius: 5px;">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endcan

                                            @can('categorias.destroy')
                                                <button type="button" class="btn btn-gradient-danger btn-sm" onclick="confirmDelete({{ $categoria->id }})" title="Eliminar categoría" style="border-radius: 5px;">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            @endcan
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        function confirmDelete(categoriaId) {
            Swal.fire({
                title: '¿Está seguro?',
                text: '¡No podrás recuperar esta categoría después de eliminarla!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminarla',
                cancelButtonText: 'Cancelar',
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + categoriaId).submit();
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            $('#categoriasTable').DataTable({
                responsive: true,
                paging: true,
                searching: true,
                ordering: true,
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/Spanish.json'
                }
            });
        });
    </script>

    <!-- SweetAlert2 para éxito -->
    @if (session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: '¡Éxito!',
                    text: '{{ session('success') }}',
                    icon: 'success',
                    confirmButtonText: 'Aceptar'
                });
            });
        </script>
    @endif
@stop
