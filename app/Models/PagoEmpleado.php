<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PagoEmpleado extends Model
{
    use HasFactory;
    protected $table = 'pagos_empleados';

    protected $fillable = [
        'mes',
        'año',
        'fecha_inicio',
        'fecha_fin',
        'monto',
        'bono_extra',
        'descuento',
        'descripcion',
        'observaciones',
        'total'
    ];

    public function pagosUsers()
    {
        return $this->hasMany(PagoUser::class, 'pago_id');
    }
}
