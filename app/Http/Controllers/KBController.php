<?php

namespace App\Http\Controllers;

use App\Models\Pdb;
use App\Models\Sentiment;
use Illuminate\Http\Request;

class KBController extends Controller
{
    public function getDrugSearch(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $query = Sentiment::query()->where('drug', $request->term)->orderBy('confidence', 'desc')->get();
            $data = [
                'data' => [
                    'diseases' => $query,
                    'sideEffects' => [],
                    'PDBs' => Pdb::query()->where('drug', $request->term)->get(),
                ],
                'stats' => [
                    0 => ['name' => 'Positive', 'count' => $query->where('class', 'Positive')->count()],
                    1 => ['name' => 'Negative', 'count' => $query->where('class', 'Negative')->count()],
                    2 => ['name' => 'Neutral', 'count' => $query->where('class', 'Neutral')->count()]
                ]
            ];
            return response()->json($data);
        } catch (\Exception $exception) {
            return response()->json($exception->getMessage(), 500);
        }
    }
}
