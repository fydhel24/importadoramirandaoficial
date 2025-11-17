<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VentaProducto extends Model
{
    use HasFactory;

    protected $table = 'venta_producto';

    protected $fillable = [
        'id_venta',
        'id_producto',
        'cantidad',
        'precio_unitario',
        'descuento',
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class, 'id_venta');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }
}
