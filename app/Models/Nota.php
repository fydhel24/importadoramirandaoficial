<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nota extends Model
{
    use HasFactory;

    protected $fillable = ['fecha', 'titulo', 'nota', 'user_id','color',];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
