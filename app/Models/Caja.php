<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caja extends Model
{
    use HasFactory;

    protected $fillable = [
        'fecha_apertura',
        'fecha_cierre',
        'id_user',
        'id_user_cierre',
        'monto_inicial',
        'efectivo_inicial',
        'qr_inicial',
        'monto_total',
        'total_efectivo',
        'total_qr',
        'sucursal_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
    public function userCierre()
    {
        return $this->belongsTo(User::class, 'id_user_cierre');
    }
   

    public function sucursal()
    {
        return $this->belongsTo(Sucursale::class, 'sucursal_id');
    }
}
