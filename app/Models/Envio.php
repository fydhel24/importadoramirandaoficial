<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Envio extends Model
{
    use HasFactory;

    // Define la tabla que usa este modelo
    protected $table = 'envios';

    // Define los campos que pueden ser asignados masivamente
    protected $fillable = [
        'celular',
        'departamento',
        'monto_de_pago',
        'descripcion',
        'lapaz',
        'enviado',
        'extra',
        'extra1',
        'fecha_hora_enviado',
        'fecha_hora_creada',
        'id_pedido',
        'detalle',
        'estado',
        'extra2',
        'extra3',
        'sucursal_id',
      ];

    // Relación con la tabla 'pedidos' (si es necesario)
    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'id_pedido');
    }
    public function sucursal()
    {
        return $this->belongsTo(Sucursale::class, 'sucursal_id');
    }
}
