<?php

namespace App\Http\Controllers;

use App\Models\Sentiment;
use Illuminate\Http\Request;

class SentimentController extends Controller
{
    public function getStats(Request $request): \Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $drugs = Sentiment::query()->pluck('drug')->unique()->count();
            $disease = Sentiment::query()->pluck('disease')->unique()->count();
            $drugDiseasePairs = Sentiment::query()->pluck('drug', 'disease')->unique()->count();
            return response(['drug' => $drugs, 'disease' => $disease, 'pairs' => $drugDiseasePairs]);
        } catch (\Throwable $throwable) {
            return response($throwable->getMessage(), 500);
        }
    }

    public function getSearch(Request $request): \Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        $request->validate([
            'type' => ['required', 'in:drug,disease'],
            'term' => ['required']
        ]);
        try {
            $type = $request->type;
            $term = $request->term;
            $query = Sentiment::query()->where($type, 'LIKE', "%$term%")->orderBy('confidence', 'desc')->get();
            return response($query);
        } catch (\Throwable $throwable) {
            return response($throwable->getMessage(), 500);
        }
    }
}
