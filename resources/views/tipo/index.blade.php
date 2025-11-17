@extends('adminlte::page')

@section('title', 'Tipos')

@section('content_header')
    <h1>Tipos</h1>
@stop

@section('content')
    <div class="container">
        @can('tipos.create')
            <div class="mb-3 text-right">
                <a href="{{ route('tipos.create') }}" class="btn btn-gradient-primary btn-lg">
                    <i class="fas fa-plus-circle"></i> Agregar Tipo
                </a>
            </div>
        @endcan

        <div class="card shadow-lg border-0" style="border-radius: 15px;">
            <div class="card-header bg-gradient-blue text-white" style="border-top-left-radius: 15px; border-top-right-radius: 15px;">
                <h3 class="card-title"><i class="fas fa-cogs"></i> Tipos Registrados</h3>
            </div>
            <div class="card-body" style="background: #f8f9fa;">
                <!-- Contenedor de tabla responsiva -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="tiposTable">
                        <thead class="linear-gradient">
                            <tr>
                                <th>No</th>
                                <th>Tipo</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tipos as $tipo)
                                <tr class="animated fadeIn">
                                    <td>{{ ++$i }}</td>
                                    <td>{{ $tipo->tipo }}</td>
                                    <td class="text-center">
                                        <form action="{{ route('tipos.destroy', $tipo->id) }}" method="POST" style="display:inline;" id="delete-form-{{ $tipo->id }}">
                                            @csrf
                                            @method('DELETE')

                                            @can('tipos.show')
                                                <a class="btn btn-gradient-success btn-sm" href="{{ route('tipos.show', $tipo->id) }}" title="Ver tipo" style="border-radius: 5px;">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            @endcan

                                            @can('tipos.edit')
                                                <a class="btn btn-gradient-success btn-sm" href="{{ route('tipos.edit', $tipo->id) }}" title="Editar tipo" style="border-radius: 5px;">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endcan

                                            @can('tipos.destroy')
                                                <button type="button" class="btn btn-gradient-danger btn-sm" onclick="confirmDelete({{ $tipo->id }})" title="Eliminar tipo" style="border-radius: 5px;">
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
        function confirmDelete(tipoId) {
            Swal.fire({
                title: '¿Está seguro?',
                text: '¡No podrás recuperar este tipo después de eliminarlo!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminarlo',
                cancelButtonText: 'Cancelar',
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + tipoId).submit();
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            $('#tiposTable').DataTable({
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
