<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PedidoProducto extends Model
{
    
    protected $table = 'pedido_producto';
    use HasFactory;

    protected $fillable = [
        'id_pedido',
        'id_producto',
        'cantidad',
        'precio',
        'id_usuario',
        'fecha',
        'id_envio', // Añadir la columna id_envio
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'id_pedido');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
    public function envio(): BelongsTo
    {
        return $this->belongsTo(Envio::class, 'id_envio');
    }
}