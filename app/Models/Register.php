<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Register extends Model
{
    use HasFactory;

    protected $fillable = [
        'estanque_id',
        'variable_id',
        'user_id',
        'valor'
    ];

    public function variable(){
        return $this->belongsTo(Variable::class);
    }

    public function estanque(){
        return $this->belongsTo(Estanque::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function scopeVisiblePara($query, User $user){
        if ($user->puedeVerTodo()) {
            return $query;
        }
        // Registros de sus estanques o capturados por el propio usuario
        return $query->where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
                ->orWhereHas('estanque', function ($e) use ($user) {
                    $e->visiblePara($user);
                });
        });
    }
}
