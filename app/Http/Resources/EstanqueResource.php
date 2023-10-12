<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EstanqueResource extends JsonResource
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
            'nombre'=>$this->nombre,
            'descripcion'=>$this->descripcion,
            'producer_id'=>$this->producer_id,
            //'productor'=>$this->producer,
            //'usuario'=>$this->producer->user,
        ];
    }
}
