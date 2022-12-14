<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SideEffectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request): array|\JsonSerializable|\Illuminate\Contracts\Support\Arrayable
    {
        return [
            'id' => $this->id,
            'drugId' => $this->drugId,
            'drugName' => $this->drugName->drugName,
            'sideEffect' => $this->sideEffect
        ];
    }
}
