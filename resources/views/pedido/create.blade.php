@extends('adminlte::page')

@section('title', 'Panel de Administración')

@section('content_header')
    <h1>Bienvenido al Panel de Administración</h1>
@stop

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">

                <div class="card card-default">
                    <div class="card-header">
                        <span class="card-title">{{ __('Create') }} Pedido</span>
                    </div>
                    <div class="card-body bg-white">
                        <form method="POST" action="{{ route('pedidos.store') }}"  role="form" enctype="multipart/form-data">
                            @csrf

                            @include('pedido.form')

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
