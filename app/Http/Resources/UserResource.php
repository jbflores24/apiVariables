<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'rfc' => $this->rfc,
            'name' => $this->name,
            'email' => $this->email,
            'rol'=> $this->roles,
            //'descripcion_rol' => $this->asign->rol,
            //'producer' => $this->producer,
            //'capture' => $this->producer->capture
        ];
    }
}
