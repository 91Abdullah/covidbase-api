<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sentiment extends Model
{
    use HasFactory;

    public function getDiseaseAttribute($value): string
    {
        return ucfirst($value);
    }

    public function diseaseOntology(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(DiseaseOntology::class, 'name', 'disease');
    }
}
