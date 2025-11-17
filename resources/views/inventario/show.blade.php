@extends('adminlte::page')


@section('template_title')
    {{ $inventario->name ?? __('Show') . " " . __('Inventario') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Inventario</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('inventarios.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                                <div class="form-group mb-2 mb20">
                                    <strong>Id Producto:</strong>
                                    {{ $inventario->id_producto }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Id Sucursal:</strong>
                                    {{ $inventario->id_sucursal }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Cantidad:</strong>
                                    {{ $inventario->cantidad }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Id User:</strong>
                                    {{ $inventario->id_user }}
                                </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
