@extends('adminlte::page')

@section('title', 'Auditoría de Stock')

@section('content_header')
    <h1 class="text-center">Auditoría de Movimientos de Stock</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <table id="stock-log-table" class="table table-bordered table-striped table-sm">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Producto</th>
                        <th>Sucursal</th>
                        <th>Valor Anterior</th>
                        <th>Valor Nuevo</th>
                        <th>Usuario</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
@stop

@section('js')
<script>
$(function () {
    $('#stock-log-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("report.stocklog.data") }}',
        columns: [
            { data: 'created_at', name: 'created_at' },
            { data: 'producto', name: 'producto' },
            { data: 'sucursal', name: 'sucursal' },
            { data: 'valor_anterior', name: 'valor_anterior' },
            { data: 'valor_nuevo', name: 'valor_nuevo' },
            { data: 'usuario', name: 'usuario' },
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
        }
    });
});
</script>
@endsection
