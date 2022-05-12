<?php

namespace App\Http\Controllers;

use App\Models\Sentiment;
use Illuminate\Http\Request;

class SentimentController extends Controller
{
    public function getAutoCompleteData(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'type' => ['required', 'in:drug,disease,gene'],
            'q' => ['required', 'string']
        ]);

        try {
            $type = $request->type;
            $data = [];
            $data = match ($type) {
                default => [],
                'drug' => Sentiment::query()->distinct()->select(['drug'])->where('drug', 'LIKE', "%$request->q%")->pluck('drug')->map(function ($v) {
                    return ['label' => $v, 'value' => $v];
                })->toArray(),
                'disease' => Sentiment::query()->distinct()->select(['disease'])->where('disease', 'LIKE', "%$request->q%")->pluck('disease')->map(function ($v) {
                    return ['label' => $v, 'value' => $v];
                })->toArray(),
            };
            return response()->json($data);
        } catch (\Exception $exception) {
            return response()->json($exception->getMessage(), 500);
        }
    }
    public function getStats(Request $request): \Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $drugs = Sentiment::query()->pluck('drug')->unique()->count();
            $disease = Sentiment::query()->pluck('disease')->unique()->count();
            $drugDiseasePairs = Sentiment::query()->count();
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
            $searchCount = [
                ['name' => 'Drug', 'count' => $query->unique('drug')->count()],
                ['name' => 'Disease', 'count' => $query->unique('disease')->count()],
                ['name' => 'Drug disease pair', 'count' => $query->count()],
            ];
            $stats = [
                0 => [
                    'name' => 'Neutral',
                    'count' => $query->where('class', 'Neutral')->count()
                ],
                1 => [
                    'name' => 'Negative',
                    'count' => $query->where('class', 'Negative')->count(),
                ],
                2 => [
                    'name' => 'Positive',
                    'count' => $query->where('class', 'Positive')->count(),
                ]
            ];
            return response(['results' => $query, 'searchCount' => $searchCount, 'stats' => $stats]);
        } catch (\Throwable $throwable) {
            return response($throwable->getMessage(), 500);
        }
    }
}
