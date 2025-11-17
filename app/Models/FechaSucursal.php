<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FechaSucursal extends Model
{
    use HasFactory;
    // Especificar la tabla si no sigue la convención
    protected $table = 'fecha_sucursal';

    // Definir los campos que se pueden asignar masivamente
    protected $fillable = [
        'fecha_inicio',
        'fecha_fin',
        'detalle',
    ];

    // Relaciones
    public function cajaSucursales()
    {
        return $this->hasMany(CajaSucursal::class, 'fecha_sucursal_id');
    }
}
