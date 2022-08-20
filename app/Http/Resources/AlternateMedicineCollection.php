<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class AlternateMedicineCollection extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request): array|JsonSerializable|Arrayable
    {
        return [
            'id' => $this->id,
            'drug' => $this->drug,
            'disease' => $this->disease,
            'class' => $this->label,
            'confidence' => $this->confidence * 100,
            'sentence' => $this->sentence,
            'diseaseOntology' => $this->diseaseOntology
        ];
    }
}
