<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RNACollection extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'RNA' => $this->RNA,
            'abstract' => $this->abstract,
            'association' => $this->association,
            'cosine' => $this->cosine,
            'disease' => $this->disease,
            'doi' => $this->doi,
            'found_flag' => $this->found_flag,
            'title' => $this->title,
            'diseaseOntology' => $this->diseaseOntology
        ];
    }
}
