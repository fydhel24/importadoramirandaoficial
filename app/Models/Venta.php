<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    use HasFactory;

    protected $fillable = [
        'fecha',            // debería estar en formato 'Y-m-d'
        'nombre_cliente',
        'costo_total',
        'id_user',         // debe ser asignado correctamente
        'id_cupo',         // si lo usas, asegúrate de que esté bien
        'ci',
        'descuento',
        'tipo_pago',
        'id_sucursal',
        'garantia',     // nuevo campo agregado
        'estado',     // Agregamos el nuevo campo
        'efectivo',
        'qr',
        'pagado',
    ];

    public function ventaProductos()
    {
        return $this->hasMany(VentaProducto::class, 'id_venta');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function cupo()
    {
        return $this->belongsTo(Cupo::class, 'id_cupo');
    }
    public function sucursal()
    {
        return $this->belongsTo(Sucursale::class, 'id_sucursal');
    }
}
