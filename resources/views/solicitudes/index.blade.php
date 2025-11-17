@extends('adminlte::page')

@section('title', 'Solicitudes de Trabajo')

@section('content_header')
    <h1>Solicitudes de Trabajo</h1>
@stop

@section('content')
    <div class="container">
        <div class="mb-3 text-right">
            <a href="{{ route('solicitudes.create') }}" class="btn btn-gradient-primary btn-lg">
                <i class="fas fa-plus-circle"></i> Nueva Solicitud
            </a>
        </div>

        <div class="card shadow-lg border-0" style="border-radius: 15px;">
            <div class="card-header bg-gradient-blue text-white"
                style="border-top-left-radius: 15px; border-top-right-radius: 15px;">
                <h3 class="card-title"><i class="fas fa-file-alt"></i> Solicitudes Registradas</h3>
            </div>
            <div class="card-body" style="background: #f8f9fa;">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="solicitudesTable">
                        <thead class="linear-gradient">
                            <tr>
                                <th>#</th>
                                <th>Nombre</th>
                                <th>CI</th>
                                <th>Celular</th>
                                <th>CV</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($solicitudes as $i => $s)
                                <tr class="animated fadeIn">
                                    <td>{{ $s->id }}</td>
                                    <td>{{ $s->nombre }}</td>
                                    <td>{{ $s->ci }}</td>
                                    <td>{{ $s->celular }}</td>
                                    <td>
                                        @if ($s->cv_pdf)
                                            <a href="{{ asset('storage/' . $s->cv_pdf) }}" target="_blank">Ver CV</a>
                                        @else
                                            Sin archivo
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a class="btn btn-sm btn-warning" href="{{ route('solicitudes.edit', $s->id) }}">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('solicitudes.destroy', $s->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-danger"
                                                onclick="return confirm('¿Eliminar esta solicitud?')">
                                                <i class="fa fa-fw fa-trash"></i>
                                            </button>
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
        document.addEventListener('DOMContentLoaded', function () {
            $('#solicitudesTable').DataTable({
                responsive: true,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/Spanish.json'
                }
            });
        });
    </script>
@stop
