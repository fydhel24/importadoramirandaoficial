<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Carpeta extends Model
{
    use HasFactory;

    protected $fillable = [
        'descripcion',
        'fecha',
        'sucursal_id',
    ];

    protected $dates = ['fecha'];

    /**
     * Relación: Una carpeta tiene muchas capturas.
     */
    public function capturas()
    {
        return $this->hasMany(Captura::class);
    }
    public function sucursal()
    {
        return $this->belongsTo(Sucursale::class);
    }

}
