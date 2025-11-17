<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PagoUser extends Model
{
    use HasFactory;
    protected $table = 'pagos_user';

    protected $fillable = [
        'user_id',
        'pago_id',
        'estado',
        'fecha_pago',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pagoEmpleado()
    {
        return $this->belongsTo(PagoEmpleado::class, 'pago_id');
    }
}
