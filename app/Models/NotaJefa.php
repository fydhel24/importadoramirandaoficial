<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotaJefa extends Model
{
    use HasFactory;
    // Especificar la tabla si no sigue la convención
    protected $table = 'notasjefa';
    protected $fillable = ['fecha', 'titulo', 'nota', 'user_id', 'color',];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
