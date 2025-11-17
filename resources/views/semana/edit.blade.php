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
                        <span class="card-title">{{ __('Update') }} Semana</span>
                    </div>
                    <div class="card-body bg-white">
                        <form method="POST" action="{{ route('semanas.update', $semana->id) }}"  role="form" enctype="multipart/form-data">
                            {{ method_field('PATCH') }}
                            @csrf

                            @include('semana.form')

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
