@extends('adminlte::page')

@section('title', 'Panel de Administración')

@section('content_header')
    <h1>Bienvenido al Panel de Administración</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">

                            <span id="card_title">
                                {{ __('Semanas') }}
                            </span>

                            <div class="float-right">
                                @can('semanas.create')
                                    {{-- Permiso para crear semanas --}}
                                    <a href="{{ route('semanas.create') }}" class="btn btn-primary btn-sm float-right"
                                        data-placement="left">
                                        {{ __('Create New') }}
                                    </a>
                                @endcan
                            </div>
                        </div>
                    </div>
                    @if ($message = Session::get('success'))
                        <div class="alert alert-success m-4">
                            <p>{{ $message }}</p>
                        </div>
                    @endif

                    <div class="card-body bg-white">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="thead">
                                    <tr>
                                        <th>No</th>

                                        <th>Nombre</th>
                                        <th>Fecha</th>

                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($semanas as $semana)
                                        <tr>
                                            <td>{{ ++$i }}</td>

                                            <td>{{ $semana->nombre }}</td>
                                            <td>{{ $semana->fecha }}</td>

                                            <td>
                                                <form action="{{ route('semanas.destroy', $semana->id) }}" method="POST">
                                                    <a class="btn btn-sm btn-primary"
                                                        href="{{ route('semanas.show', $semana->id) }}">
                                                        <i class="fa fa-fw fa-eye"></i> {{ __('Show') }}
                                                    </a>

                                                    @can('semanas.edit')
                                                        {{-- Permiso para editar semanas --}}
                                                        <a class="btn btn-sm btn-success"
                                                            href="{{ route('semanas.edit', $semana->id) }}">
                                                            <i class="fa fa-fw fa-edit"></i> {{ __('Edit') }}
                                                        </a>
                                                    @endcan

                                                    @can('semanas.destroy')
                                                        {{-- Permiso para eliminar semanas --}}
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm"
                                                            onclick="event.preventDefault(); confirm('Are you sure to delete?') ? this.closest('form').submit() : false;">
                                                            <i class="fa fa-fw fa-trash"></i> {{ __('Delete') }}
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
                {!! $semanas->withQueryString()->links() !!}
            </div>
        </div>
    </div>
@endsection
