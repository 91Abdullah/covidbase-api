<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PDBCollection extends JsonResource
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
            'drug' => $this->drug,
            'pdb' => $this->pdb,
            'title' => $this->title,
            'doi' => $this->doi,
            'abstract' => $this->abstract
        ];
    }
}
