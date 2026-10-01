<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producer extends Model
{
    use HasFactory;
    protected $fillable = [
        'calle',
        'numero',
        'colonia',
        'cp',
        'municipio',
        'agencia',
        'estado',
        'telPrincipal',
        'telSecundario',
        'user_id'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function estanques(){
        return $this->hasMany(Estanque::class);
    }

    public function scopeVisiblePara($query, User $user){
        if ($user->puedeVerTodo()) {
            return $query;
        }
        return $query->where('user_id', $user->id);
    }
}
