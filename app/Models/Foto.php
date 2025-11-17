<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Foto extends Model
{
    use HasFactory;

    protected $fillable = ['foto'];

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'catalogos', 'id_foto', 'id_producto');
    }
}
