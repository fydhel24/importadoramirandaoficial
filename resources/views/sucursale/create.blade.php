@extends('adminlte::page')

@section('template_title')
    {{ __('Create') }} Sucursale
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card card-default">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('Create') }} Sucursale</h3>
                    </div>
                    <div class="card-body bg-white">
                        <form method="POST" action="{{ route('sucursales.store') }}" role="form" enctype="multipart/form-data">
                            @csrf

                            <!-- Campo Nombre -->
                            <div class="form-group mb-3">
                                <label for="nombre" class="form-label">
                                    <i class="fas fa-building"></i> {{ __('Nombre') }}
                                </label>
                                <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror"
                                       value="{{ old('nombre') }}" id="nombre" placeholder="Nombre" required>
                                {!! $errors->first('nombre', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
                            </div>

                            <!-- Campo Dirección -->
                            <div class="form-group mb-3">
                                <label for="direccion" class="form-label">
                                    <i class="fas fa-map-marker-alt"></i> {{ __('Dirección') }}
                                </label>
                                <input type="text" name="direccion" class="form-control @error('direccion') is-invalid @enderror"
                                       value="{{ old('direccion') }}" id="direccion" placeholder="Dirección" required>
                                {!! $errors->first('direccion', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
                            </div>

                            <!-- Campo Celular -->
                            <div class="form-group mb-3">
                                <label for="celular" class="form-label">
                                    <i class="fas fa-phone"></i> {{ __('Celular') }}
                                </label>
                                <input type="text" name="celular" class="form-control @error('celular') is-invalid @enderror"
                                       value="{{ old('celular') }}" id="celular" placeholder="Celular" required>
                                {!! $errors->first('celular', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
                            </div>

                            <!-- Campo Estado -->
                            <div class="form-group mb-3">
                                <label for="estado" class="form-label">
                                    <i class="fas fa-toggle-on"></i> {{ __('Estado') }}
                                </label>
                                <select name="estado" class="form-control @error('estado') is-invalid @enderror" id="estado" required>
                                    <option value="activo" {{ old('estado') == 'activo' ? 'selected' : '' }}>Activo</option>
                                    <option value="inactivo" {{ old('estado') == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                                    </select>
                                {!! $errors->first('estado', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
                            </div>

                            <!-- Campo Logo -->
                            <div class="form-group mb-3">
                                <label for="logo" class="form-label">
                                    <i class="fas fa-image"></i> {{ __('Logo') }}
                                </label>
                                <input type="file" name="logo" class="form-control @error('logo') is-invalid @enderror" id="logo">
                                {!! $errors->first('logo', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
                            </div>

                            <!-- Botón de Enviar -->
                            <div class="form-group text-center">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> {{ __('Submit') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
