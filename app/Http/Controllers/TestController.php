<?php

namespace App\Http\Controllers;

use App\Http\Resources\SideEffectResource;
use App\Models\Sentiment;
use App\Models\SideEffect;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function test(Request $request)
    {
        dd(Sentiment::query()->with(['diseaseOntology'])->limit(10)->get());
    }
}
