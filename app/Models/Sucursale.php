<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Class Sucursale
 *
 * @property $id
 * @property $nombre
 * @property $direccion
 * @property $created_at
 * @property $updated_at
 *
 * @property Inventario[] $inventarios
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Sucursale extends Model
{

    protected $perPage = 20;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['nombre', 'direccion', 'logo', 'celular', 'estado'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    // Modelo Sucursale
    public function inventarios()
    {
        return $this->hasMany(Inventario::class, 'id_sucursal');
    }
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'sucursal_user', 'sucursal_id', 'user_id')
            ->withTimestamps();
    }
        public function carpetas()
    {
        return $this->hasMany(Carpeta::class);
    }

}
