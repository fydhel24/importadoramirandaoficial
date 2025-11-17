@extends('adminlte::page')

@section('title', 'Lista de Roles')

@section('content_header')
    <h1>
        Lista de Roles
    </h1>
@stop
@section('content')
    <div class="container">
        @can('roles.create')
            <div class="mb-3 text-right">
                <a href="{{ route('roles.create') }}" class="btn btn-gradient-primary btn-lg">
                    <i class="fas fa-plus-circle"></i> Agregar Rol
                </a>
            </div>
        @endcan

        <div class="card shadow-lg border-0" style="border-radius: 15px;">
            <div class="card-header bg-gradient-blue text-white"
                style="border-top-left-radius: 15px; border-top-right-radius: 15px;">
                <h3 class="card-title"><i class="fas fa-user-tag"></i> Roles Registrados</h3>
            </div>
            <div class="card-body" style="background: #f8f9fa;">
                <!-- Contenedor de tabla responsiva -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="rolesTable">
                        <thead class="linear-gradient">
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($roles as $role)
                                <tr class="animated fadeIn">
                                    <td>{{ $role->id }}</td>
                                    <td>{{ $role->name }}</td>
                                    <td class="text-center">
                                        @can('roles.edit')
                                            <a href="{{ route('roles.edit', $role) }}" class="btn btn-gradient-success btn-sm"
                                                title="Editar rol" style="border-radius: 5px;">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endcan

                                        @can('roles.destroy')
                                            <form action="{{ route('roles.destroy', $role) }}" method="POST"
                                                style="display:inline;" id="delete-form-{{ $role->id }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-gradient-danger btn-sm"
                                                    onclick="confirmDelete({{ $role->id }})" title="Eliminar rol"
                                                    style="border-radius: 5px;">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        @endcan
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
        function confirmDelete(roleId) {
            Swal.fire({
                title: '¿Está seguro?',
                text: '¡No podrás recuperar este rol después de eliminarlo!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminarlo',
                cancelButtonText: 'Cancelar',
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + roleId).submit();
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            $('#rolesTable').DataTable({
                responsive: true,
                paging: true,
                searching: true,
                ordering: true,
                language: {
                    decimal: ",", // Cambia el separador decimal, si es necesario
                    thousands: ".", // Cambia el separador de miles, si es necesario
                    processing: "Procesando...", // Mensaje cuando está cargando los datos
                    lengthMenu: "Mostrar _MENU_ registros", // Mensaje para el selector de cantidad de registros
                    info: "Mostrando _START_ a _END_ de _TOTAL_ registros", // Mensaje de paginación
                    infoEmpty: "No hay registros disponibles", // Mensaje si no hay registros
                    infoFiltered: "(filtrado de _MAX_ registros)", // Mensaje de filtrado
                    search: "Buscar:", // Mensaje para el campo de búsqueda
                    zeroRecords: "No se encontraron registros", // Mensaje cuando no hay registros coincidentes
                    emptyTable: "No hay datos disponibles en la tabla", // Mensaje cuando la tabla está vacía
                    paginate: {
                        first: "Primero", // Texto del botón "Primero"
                        previous: "Anterior", // Texto del botón "Anterior"
                        next: "Siguiente", // Texto del botón "Siguiente"
                        last: "Último" // Texto del botón "Último"
                    },
                    aria: {
                        sortAscending: ": activar para ordenar la columna de manera ascendente", // Texto para el orden ascendente
                        sortDescending: ": activar para ordenar la columna de manera descendente" // Texto para el orden descendente
                    }
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
