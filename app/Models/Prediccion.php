<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prediccion extends Model
{
    use HasFactory;

    protected $table = 'predicciones'; // asegúrate de que exista en la BD

    protected $fillable = [
        'producto_id',
        'fecha',
        'ventas_predichas',
        'error_promedio',
        'conf',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
