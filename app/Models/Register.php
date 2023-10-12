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
}
