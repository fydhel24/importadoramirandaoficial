<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Proveedor extends Model
{
    use HasFactory;
    
    protected $table = 'proveedores';
    
    protected $fillable = [
        'nombre',
        'codigo_factura',
        'pago_inicial',
        'deuda_total',
        'fecha_registro',
        'estado',
        'foto_factura'
    ];

    protected $casts = [
        'fecha_registro' => 'date',
        'pago_inicial' => 'decimal:2',
        'deuda_total' => 'decimal:2'
    ];
    
    public function pagos(): HasMany
    {
        return $this->hasMany(PagoProveedor::class, 'proveedor_id');
    }

    public function getSaldoPendienteAttribute()
    {
        $pagosTotales = $this->pagos()->sum('monto_pago') + $this->pago_inicial;
        return max(0, $this->deuda_total - $pagosTotales);
    }

    public function getPorcentajePagadoAttribute()
    {
        if ($this->deuda_total <= 0) return 0;
        $pagosTotales = $this->pagos()->sum('monto_pago') + $this->pago_inicial;
        return min(100, round(($pagosTotales / $this->deuda_total) * 100));
    }
}