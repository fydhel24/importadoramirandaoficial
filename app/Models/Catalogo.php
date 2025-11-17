<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Catalogo extends Model
{
    use HasFactory;

    protected $fillable = ['id_producto', 'id_foto'];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }

    public function foto()
    {
        return $this->belongsTo(Foto::class, 'id_foto');
    }
}
