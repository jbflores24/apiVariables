<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RegisterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'valor'=>$this->valor,
            'estanque_id'=>$this->estanque_id,
            'estanque' => $this->estanque,
            'variable_id'=>$this->variable_id,
            'variable'=>$this->variable,
            'user_id'=> $this->user_id,
            'registro'=> $this->user,
        ];
    }
}
