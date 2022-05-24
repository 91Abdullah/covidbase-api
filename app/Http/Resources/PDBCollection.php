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
            'drug' => $this->drug,
            'pdb' => $this->pdb,
            'title' => $this->title,
            'doi' => $this->doi ? "https://doi.org/{$this->doi}" : '',
            'abstract' => $this->abstract
        ];
    }
}
