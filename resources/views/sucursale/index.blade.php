@extends('adminlte::page')

@section('title', 'Sucursales')

@section('content_header')
    <h1>Sucursales</h1>
@stop

@section('content')
    <div class="container">
        @can('sucursales.create')
            <div class="mb-3 text-right">
                <a href="{{ route('sucursales.create') }}" class="btn btn-gradient-primary btn-lg">
                    <i class="fas fa-plus-circle"></i> Agregar Sucursal
                </a>
            </div>
        @endcan

        <div class="card shadow-lg border-0" style="border-radius: 15px;">
            <div class="card-header bg-gradient-blue text-white"
                style="border-top-left-radius: 15px; border-top-right-radius: 15px;">
                <h3 class="card-title"><i class="fas fa-store"></i> Sucursales Registradas</h3>
            </div>
            <div class="card-body" style="background: #f8f9fa;">
                <!-- Contenedor de tabla responsiva -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="sucursalesTable">
                        <thead class="linear-gradient">
                            <tr>
                                <th>No</th>
                                <th>Logo</th>
                                <th>Nombre</th>
                                <th>Dirección</th>
                                <th>Celular</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sucursales as $sucursale)
                                <tr class="animated fadeIn">
                                    <td>{{ ++$i }}</td>
                                    <td>
                                        @if ($sucursale->logo)
                                            <img src="{{ asset('storage/' . $sucursale->logo) }}" alt="Logo"
                                                class="img-fluid" style="max-width: 50px; height: auto;">
                                        @else
                                            <i class="fas fa-image" style="font-size: 24px;"></i>
                                        @endif
                                    </td>
                                    <td>{{ $sucursale->nombre }}</td>
                                    <td>{{ $sucursale->direccion }}</td>
                                    <td>{{ $sucursale->celular ?? 'No disponible' }}</td>
                                    <td>{{ $sucursale->estado ?? 'No definido' }}</td>
                                    <td class="text-center">
                                        <form action="{{ route('sucursales.destroy', $sucursale->id) }}" method="POST"
                                            style="display:inline;">
                                            @csrf
                                            @method('DELETE')

                                            @can('sucursales.show')
                                                <a class="btn btn-gradient-success btn-sm"
                                                    href="{{ route('sucursales.show', $sucursale->id) }}" title="Ver sucursal"
                                                    style="border-radius: 5px;">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            @endcan

                                            @can('sucursales.edit')
                                                <a class="btn btn-gradient-success btn-sm"
                                                    href="{{ route('sucursales.edit', $sucursale->id) }}"
                                                    title="Editar sucursal" style="border-radius: 5px;">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endcan

                                            @can('sucursales.destroy')
                                                {{-- Permiso para eliminar sucursales --}}
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-gradient-danger btn-sm"
                                                    onclick="event.preventDefault(); confirm('¿Está seguro de eliminar?') ? this.closest('form').submit() : false;">
                                                    <i class="fa fa-fw fa-trash"></i>
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
        document.addEventListener('DOMContentLoaded', function() {
            $('#sucursalesTable').DataTable({
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
