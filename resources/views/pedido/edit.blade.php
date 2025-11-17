@extends('adminlte::page')

@section('title', 'Panel de Administración')

@section('content_header')
    <h1>Bienvenido al Panel de Administración</h1>
@stop

@section('content')
    <section class="content container-fluid">
        <div class="">
            <div class="col-md-12">

                <div class="card card-default">
                    <div class="card-header">
                        <span class="card-title">{{ __('Update') }} Pedido</span>
                    </div>
                    <div class="card-body bg-white">
                        <form method="POST" action="{{ route('pedidos.update', $pedido->id) }}"  role="form" enctype="multipart/form-data">
                            {{ method_field('PATCH') }}
                            @csrf

                            @include('pedido.form')

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
