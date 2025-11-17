<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockLog extends Model
{
    protected $table = 'stock_logs';

    protected $fillable = [
        'producto_id',
        'sucursal_id',
        'valor_anterior',
        'valor_nuevo',
        'usuario_id',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
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
