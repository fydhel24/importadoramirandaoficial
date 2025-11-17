@extends('adminlte::page')

@section('template_title')
    {{ $cupo->name ?? __('Show') . " " . __('Cupo') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Cupo</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('cupos.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        <div class="form-group mb-2 mb20">
                            <strong>{{ __('Codigo:') }}</strong>
                            {{ $cupo->codigo }}
                        </div>

                        <div class="form-group mb-2 mb20">
                            <strong>{{ __('Porcentaje:') }}</strong>
                            {{ $cupo->porcentaje }}%
                        </div>

                        <div class="form-group mb-2 mb20">
                            <strong>{{ __('Estado:') }}</strong>
                            <span class="{{ $cupo->estado == 'Inactivo' ? 'text-danger' : 'text-success' }}">
                                {{ $cupo->estado }}
                            </span>
                        </div>

                        <div class="form-group mb-2 mb20">
                            <strong>{{ __('Fecha de Inicio:') }}</strong>
                            {{ $cupo->fecha_inicio ? $cupo->fecha_inicio->format('d/m/Y H:i') : 'N/A' }}
                        </div>

                        <div class="form-group mb-2 mb20">
                            <strong>{{ __('Fecha de Fin:') }}</strong>
                            {{ $cupo->fecha_fin ? $cupo->fecha_fin->format('d/m/Y H:i') : 'N/A' }}
                        </div>

                        <div class="form-group mb-2 mb20">
                            <strong>{{ __('Usuario:') }}</strong>
                            {{ $cupo->user->name ?? 'Usuario no asignado' }} <!-- Asumiendo que tienes una relación con usuario -->
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
