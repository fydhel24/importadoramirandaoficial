<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrecioProducto extends Model
{
    use HasFactory;

    protected $table = 'precio_producto'; // Define la tabla asociada al modelo

    protected $fillable = [
        'id_producto',
        'precio_jefa',
        'precio_unitario',
        'cantidad',
        'precio_general',
        'precio_extra',
        'precio_preventa',  // Nuevo campo agregado aquí
        'fecha_creada',
    ];

    // Relación con Producto (si la necesitas)
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id');
    }
}
