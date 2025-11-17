<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdRegistroMalEstado extends Model
{
    use HasFactory;

    protected $table = 'prod_registro_mal_estado';

    protected $fillable = [
        'celular',
        'persona',
        'departamento',
        'producto_id',
        'estado',
        'descripcion_problema',
        'fecha_inscripcion',
        'fecha_cambio_estado',
        'checkbox',
        'de_la_paz',
        'enviado',
        'extra1',
        'extra2',
        'extra3',
        'extra4',
        'extra5',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
