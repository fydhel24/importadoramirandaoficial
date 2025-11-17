<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditoriaDetalle extends Model
{
    use HasFactory;
    protected $table = 'auditoria_detalles';

    protected $fillable = [
        'auditoria_id',
        'producto_id',
        'stock_sistema',
        'stock_real',
        'diferencia',
        'estado',
        'comentario',
        'observacion_solucion',
        'fecha_solucion',
    ];


    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function auditoria()
    {
        return $this->belongsTo(AuditoriaInventario::class, 'auditoria_id');
    }
}
