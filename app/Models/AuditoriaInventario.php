<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditoriaInventario extends Model
{
    use HasFactory;
    protected $table = 'auditorias_inventario';

    protected $fillable = [
        'sucursal_id',
        'fecha',
        'observaciones',
        'usuario_id',
    ];

    public function detalles()
    {
        return $this->hasMany(AuditoriaDetalle::class, 'auditoria_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursale::class, 'sucursal_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
