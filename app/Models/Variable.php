<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Variable extends Model
{
    use HasFactory;

    protected $fillable = ['nombre'];

    public function registers(){
        return $this->hasMany(Register::class);
    }
}
