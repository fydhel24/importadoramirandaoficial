<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Semana extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'fecha',
    ];

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'id_semana');
    }
}
