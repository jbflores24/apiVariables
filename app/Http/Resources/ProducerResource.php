<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProducerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'=>$this->id,
            'user_id'=>$this->user_id,
            'usuario'=>$this->user,
            'calle'=>$this->calle,
            'numero'=>$this->numero,
            'colonia'=>$this->colonia,
            'cp'=>$this->cp,
            'municipio'=>$this->municipio,
            'agencia'=>$this->agencia,
            'estado'=>$this->estado,
            'telPrincipal'=>$this->telPrincipal,
            'telSecundario'=>$this->telSecundario,
        ];
    }
}
