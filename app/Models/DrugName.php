<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DrugName extends Model
{
    use HasFactory;

    public function sideEffects(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SideEffect::class, 'drugId', 'drugId');
    }
}
