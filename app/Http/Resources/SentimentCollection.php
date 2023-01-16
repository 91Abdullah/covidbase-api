<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SentimentCollection extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request): array|\JsonSerializable|\Illuminate\Contracts\Support\Arrayable
    {
        return [
            'id' => $this->id,
            'drug' => $this->drug,
            'disease' => $this->disease,
            'class' => $this->class,
            'confidence' => $this->confidence * 100,
            //'sentence' => $this->sentences,
            //'diseaseOntology' => $this->diseaseOntology
        ];
    }
}
