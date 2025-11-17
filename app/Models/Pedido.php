<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'ci',
        'celular',
        'destino',
        'direccion',
        'estado',
        'cantidad_productos',
        'detalle',
        'productos',
        'monto_deposito',
        'monto_enviado_pagado',
        'fecha',
        'id_semana',
        'foto_comprobante', // Agregado
        'codigo', // Agregado
        'efectivo',             // nuevo campo
        'transferencia_qr',     // nuevo campo
        'garantia',             // nuevo campo
    ];

    public function semana()
    {
        return $this->belongsTo(Semana::class, 'id_semana');
    }

    // app/Models/Pedido.php
public function pedidoProductos()
{
    return $this->hasMany(PedidoProducto::class, 'id_pedido');
}

     
}

