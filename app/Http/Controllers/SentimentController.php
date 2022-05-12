<?php

namespace App\Http\Controllers;

use App\Models\Gene;
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
                'gene' => Gene::query()->distinct()->select(['gene'])->where('gene', 'LIKE', "%$request->q%")->pluck('gene')->map(function ($v) {
                    return ['label' => $v, 'value' => $v];
                })
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
            $genes = Gene::query()->pluck('gene')->unique()->count();
            $drugDiseasePairs = Sentiment::query()->count();
            $diseaseGenePairs = Gene::query()->count();
            return response(['drug' => $drugs, 'disease' => $disease, 'pairs' => $drugDiseasePairs, 'genes' => $genes, 'diseaseGenePair' => $diseaseGenePairs]);
        } catch (\Throwable $throwable) {
            return response($throwable->getMessage(), 500);
        }
    }

    public function getSearch(Request $request): \Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        $request->validate([
            'type' => ['required', 'in:drug,disease,gene'],
            'term' => ['required']
        ]);
        try {
            $type = $request->type;
            $term = $request->term;
            switch ($type) {
                case 'drug':
                case 'disease':
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
                    break;
                case 'gene':
                    $query = Gene::query()->where('gene', 'LIKE', "%$term%")->get();
                    $searchCount = [
                        ['name' => 'Gene', 'count' => $query->unique('gene')->count()],
                        ['name' => 'Disease', 'count' => $query->unique('disease')->count()],
                        ['name' => 'Gene Disease pair', 'count' => $query->count()],
                    ];
                    $stats = [
                        0 => [
                            'name' => 'High', 'count' => $query->where('association', 'High')->count()
                        ],
                        1 => [
                            'name' => 'Medium', 'count' => $query->where('association', 'Medium')->count()
                        ],
                        2 => [
                            'name' => 'Low', 'count' => $query->where('association', 'Low')->count()
                        ],
                        3 => [
                            'name' => 'Verified', 'count' => $query->where('association', 'Verified')->count()
                        ]
                    ];
                    break;
                default:
                    $query = [];
                    $searchCount = [];
                    $stats = [];
                    break;
            }
            return response(['results' => $query, 'searchCount' => $searchCount, 'stats' => $stats]);
        } catch (\Throwable $throwable) {
            return response($throwable->getMessage(), 500);
        }
    }
}
