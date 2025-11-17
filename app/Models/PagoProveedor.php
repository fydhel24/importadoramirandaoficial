<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PagoProveedor extends Model
{
    use HasFactory;
    
    protected $table = 'pagos_proveedores';
    
    protected $fillable = [
        'proveedor_id',
        'monto_pago',
        'fecha_pago',
        'saldo_restante',
        // Add this new line
        'foto_factura'
    ];

    protected $casts = [
        'fecha_pago' => 'date',
        'monto_pago' => 'decimal:2',
        'saldo_restante' => 'decimal:2'
    ];
    
    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }
}