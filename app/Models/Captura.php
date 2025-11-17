<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Captura extends Model
{
    use HasFactory;

    protected $fillable = [
        'foto_original',
        'carpeta_id',
        'campo_texto',
    ];

    /**
     * Relación: Una captura pertenece a una carpeta.
     */
    public function carpeta()
    {
        return $this->belongsTo(Carpeta::class);
    }
}
