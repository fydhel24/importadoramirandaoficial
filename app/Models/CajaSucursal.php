<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CajaSucursal extends Model
{
    use HasFactory;
     // Especificar la tabla si no sigue la convención
     protected $table = 'caja_sucursal';

     // Definir los campos que se pueden asignar masivamente
     protected $fillable = [
         'total_vendido',
         'qr',
         'efectivo',
         'qr_oficial',
         'efectivo_oficial',
         'sucursal_id',
         'fecha_sucursal_id',
     ];
 
     // Relaciones
     public function sucursal()
     {
         return $this->belongsTo(Sucursale::class, 'sucursal_id');
     }
 
     public function fechaSucursal()
     {
         return $this->belongsTo(FechaSucursal::class, 'fecha_sucursal_id');
     }
}
