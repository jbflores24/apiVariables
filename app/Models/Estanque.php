<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estanque extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'descripcion',
        'producer_id'
    ];

    public function producer(){
        return $this->belongsTo(Producer::class);
    }

    public function registers(){
        return $this->hasMany(Register::class);
    }
}
