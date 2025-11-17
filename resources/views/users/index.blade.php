@extends('adminlte::page')

@section('title', 'Lista de Usuarios')

@section('content_header')
    <h1 class="text-center">Lista de Usuarios</h1>
@stop

@section('content')
    <div class="container">
        @can('users.create')
            <div class="mb-3 text-right">
                <a href="{{ route('users.create') }}" class="btn btn-gradient-primary btn-lg">
                    <i class="fas fa-plus-circle"></i> Agregar Usuario
                </a>
            </div>
        @endcan

        <div class="card shadow-lg border-0" style="border-radius: 15px;">
            <div class="card-header bg-gradient-blue text-white"
                style="border-top-left-radius: 15px; border-top-right-radius: 15px;">
                <h3 class="card-title"><i class="fas fa-user-tag"></i> Usuarios Registrados</h3>
            </div>
            <div class="card-body" style="background: #f8f9fa;">
                <!-- Comienza la parte responsive -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="usersTable">
                        <thead class="linear-gradient">
                            <tr>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th>Sucursal</th>
                                <th>Estado</th> <!-- Agregamos la columna de Estado -->
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr class="animated fadeIn">
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td class="text-center">
                                        <span
                                            class="badge {{ $user->roles->isNotEmpty() ? 'badge-info' : 'badge-secondary' }}">
                                            @if ($user->roles->isNotEmpty())
                                                {{ $user->roles->pluck('name')->join(', ') }}
                                            @else
                                                Usuario nuevo (sin rol asignado)
                                            @endif
                                        </span>
                                    </td>
                                    <td>
                                        @if ($user->sucursales->isNotEmpty())
                                            {{ $user->sucursales->pluck('nombre')->join(', ') }}
                                        @else
                                            No asignada
                                        @endif
                                    </td>
                                    <td>
                                        @if ($user->status == 'active')
                                            <span class="badge badge-success">Activo</span>
                                        @else
                                            <span class="badge badge-danger">Inactivo</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @can('users.edit')
                                            <a href="{{ route('users.edit', $user) }}" class="btn btn-gradient-success btn-sm"
                                                title="Editar usuario" style="border-radius: 5px;">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endcan

                                        @can('users.destroy')
                                            <form action="{{ route('users.destroy', $user) }}" method="POST"
                                                style="display:inline;" id="delete-form-{{ $user->id }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-gradient-danger btn-sm"
                                                    onclick="confirmDelete({{ $user->id }})" title="Eliminar usuario"
                                                    style="border-radius: 5px;">
                                                    <i class="fas fa-trash-alt"></i>
                                            </form>
                                        @endcan
                                        @can('users.destroy')
                                            <form action="{{ route('users.resetPassword', $user) }}" method="POST"
                                                style="display:inline;" id="reset-password-form-{{ $user->id }}">
                                                @csrf
                                                @method('DELETE') <!-- Usamos DELETE aunque no eliminamos el recurso -->
                                                <button type="button" class="btn btn-gradient-warning btn-sm"
                                                    onclick="confirmResetPassword({{ $user->id }})"
                                                    title="Restablecer contraseña" style="border-radius: 5px;">
                                                    <i class="fas fa-key"></i>
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
        function confirmResetPassword(userId) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: 'La contraseña se restablecerá y será igual al email del usuario.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, restablecer',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('reset-password-form-' + userId).submit();
                }
            });
        }

        function confirmDelete(userId) {
            Swal.fire({
                title: '¿Está seguro?',
                text: '¡No podrás recuperar este usuario después de eliminarlo!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminarlo',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + userId).submit();
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            $('#usersTable').DataTable({
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
@endsection
