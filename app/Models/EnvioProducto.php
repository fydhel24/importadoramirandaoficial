<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnvioProducto extends Model
{
    use HasFactory;

    protected $table = 'inventario';

    protected $fillable = [
        'id_producto',
        'id_sucursal',
        'cantidad',
    ];

    // Relación con Producto
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }

    // Relación con Sucursal
    public function sucursal()
    {
        return $this->belongsTo(Sucursale::class, 'id_sucursal');
    }
}
