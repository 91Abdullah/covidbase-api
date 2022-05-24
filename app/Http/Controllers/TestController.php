<?php

namespace App\Http\Controllers;

use App\Http\Resources\SideEffectResource;
use App\Models\SideEffect;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function test(Request $request)
    {
        $sideEffect = SideEffectResource::collection(SideEffect::query()->whereRelation('drugName', 'drugName', 'LIKE', "%$request->term%")->get());

        dd($sideEffect->toArray($request));
    }
}
