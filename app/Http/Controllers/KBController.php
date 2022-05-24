<?php

namespace App\Http\Controllers;

use App\Http\Resources\PDBCollection;
use App\Http\Resources\SentimentCollection;
use App\Http\Resources\SideEffectResource;
use App\Models\DrugName;
use App\Models\Gene;
use App\Models\Pdb;
use App\Models\Rna;
use App\Models\Sentiment;
use App\Models\SideEffect;
use App\Models\TopSearch;
use Illuminate\Http\Request;

class KBController extends Controller
{
    public function getTopDrugSearch(): array
    {
        return TopSearch::query()->selectRaw('COUNT(*) as counter, searchTerm')->where('searchType', 'drug')->groupBy('searchTerm')->orderBy('counter', 'desc')->limit(5)->pluck('searchTerm')->map(function ($v) {
            return ['name' => $v, 'link' => "/drugs/$v"];
        })->toArray();
    }

    public function getTopDiseaseSearch(): array
    {
        return TopSearch::query()->selectRaw('COUNT(*) as counter, searchTerm')->where('searchType', 'disease')->groupBy('searchTerm')->orderBy('counter', 'desc')->limit(5)->pluck('searchTerm')->map(function ($v) {
            return ['name' => $v, 'link' => "/diseases/$v"];
        })->toArray();
    }

    public function getTopGeneSearch(): array
    {
        return TopSearch::query()->selectRaw('COUNT(*) as counter, searchTerm')->where('searchType', 'gene')->groupBy('searchTerm')->orderBy('counter', 'desc')->limit(5)->pluck('searchTerm')->map(function ($v) {
            return ['name' => $v, 'link' => "/genes/$v"];
        })->toArray();
    }

    public function getTopMiRNASearch(): array
    {
        return TopSearch::query()->selectRaw('COUNT(*) as counter, searchTerm')->where('searchType', 'miRNA')->groupBy('searchTerm')->orderBy('counter', 'desc')->limit(5)->pluck('searchTerm')->map(function ($v) {
            return ['name' => $v, 'link' => "/miRNAs/$v"];
        })->toArray();
    }

    public function getTopPDBSearch(): array
    {
        return TopSearch::query()->selectRaw('COUNT(*) as counter, searchTerm')->where('searchType', 'pdb')->groupBy('searchTerm')->orderBy('counter', 'desc')->limit(5)->pluck('searchTerm')->map(function ($v) {
            return ['name' => $v, 'link' => "/PDBs/$v"];
        })->toArray();
    }

    public function getTopDrugDiseaseSearch(): array
    {
        return TopSearch::query()->selectRaw('COUNT(*) as counter, searchTerm')->where('searchType', 'drug+disease')->groupBy('searchTerm')->orderBy('counter', 'desc')->limit(5)->pluck('searchTerm')->map(function ($v) {
            return ['name' => $v, 'link' => "/drug-disease/$v"];
        })->toArray();
    }

    public function getTopDiseaseGeneSearch(): array
    {
        return TopSearch::query()->selectRaw('COUNT(*) as counter, searchTerm')->where('searchType', 'disease+gene')->groupBy('searchTerm')->orderBy('counter', 'desc')->limit(5)->pluck('searchTerm')->map(function ($v) {
            return ['name' => $v, 'link' => "/disease-gene/$v"];
        })->toArray();
    }

    public function getTopDiseaseMiRNASearch(): array
    {
        return TopSearch::query()->selectRaw('COUNT(*) as counter, searchTerm')->where('searchType', 'disease+miRNA')->groupBy('searchTerm')->orderBy('counter', 'desc')->limit(5)->pluck('searchTerm')->map(function ($v) {
            return ['name' => $v, 'link' => "/disease-miRNA/$v"];
        })->toArray();
    }

    public function getTopDrugPDBSearch(): array
    {
        return TopSearch::query()->selectRaw('COUNT(*) as counter, searchTerm')->where('searchType', 'drug+pdb')->groupBy('searchTerm')->orderBy('counter', 'desc')->limit(5)->pluck('searchTerm')->map(function ($v) {
            return ['name' => $v, 'link' => "/drug-pdb/$v"];
        })->toArray();
    }

    public function logTopSearch($type, $term): \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Builder
    {
        return TopSearch::query()->create([
            'searchType' => $type,
            'searchTerm' => $term
        ]);
    }

    public function getDiseaseMiRNASearch(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $this->logTopSearch('disease+miRNA', $request->term);
            [$disease, $miRNA] = explode('+', $request->term);
            $query = Rna::query()->where('disease', 'LIKE', "%$disease%")->where('RNA', 'LIKE', "%$miRNA%")->orderBy('disease')->get();
            $data = [
                'data' => [
                    'diseaseMiRNA' => $query
                ]
            ];
            return response()->json($data);
        } catch (\Exception $exception) {
            return response()->json($exception->getMessage());
        }
    }

    public function getDiseaseGeneSearch(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $this->logTopSearch('disease+gene', $request->term);
            [$disease, $gene] = explode('+', $request->term);
            $query = Gene::query()->where('disease', 'LIKE', "%$disease%")->where('gene', 'LIKE', "%$gene%")->orderBy('disease')->get();
            $data = [
                'data' => [
                    'diseaseGene' => $query
                ]
            ];
            return response()->json($data);
        } catch (\Exception $exception) {
            return response()->json($exception->getMessage());
        }
    }

    public function getDrugDiseaseSearch(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $this->logTopSearch('drug+disease', $request->term);
            [$disease, $drug] = explode('+', $request->term);
            $query = Sentiment::query()->where('drug', 'LIKE', "%$drug%")->where('disease', 'LIKE', "%$disease%")->orderBy('confidence', 'desc')->get();
            $data = [
                'data' => [
                    'drugDisease' => SentimentCollection::collection($query),
                    'genes' => Gene::query()->where('disease', 'LIKE', "%$disease%")->get(),
                    'miRNAs' => Rna::query()->where('disease', 'LIKE', "%$disease%")->get(),
                    'PDBs' => PDBCollection::collection(Pdb::query()->where('drug', 'LIKE', "%$drug%")->get()),
                    'sideEffects' => SideEffectResource::collection(SideEffect::query()->whereRelation('drugName', 'drugName', 'LIKE', "%$drug%")->get())
                ]
            ];
            return response()->json($data);
        } catch (\Exception $exception) {
            return response()->json($exception->getMessage(), 500);
        }
    }

    public function getDrugPDBSearch(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $this->logTopSearch('drug+pdb', $request->term);
            [$drug, $pdb] = explode('+', $request->term);
            $query = Pdb::query()->where('drug', 'LIKE', "%$drug%")->where('pdb', 'LIKE', "%$pdb%")->orderBy('drug')->get();
            $data = [
                'data' => [
                    'drugPDB' => PDBCollection::collection($query),
                    'sideEffects' => SideEffectResource::collection(SideEffect::query()->whereRelation('drugName', 'drugName', 'LIKE', "%$drug%")->get())
                ]
            ];
            return response()->json($data);
        } catch (\Exception $exception) {
            return response()->json($exception->getMessage(), 500);
        }
    }

    public function getPDBSearch(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $this->logTopSearch('pdb', $request->term);
            $query = Pdb::query()->where('pdb', 'LIKE', "%$request->term%")->orderBy('pdb')->get();
            $data = [
                'data' => [
                    'drugs' => PDBCollection::collection($query)
                ]
            ];
            return response()->json($data);
        } catch (\Exception $exception) {
            return response()->json($exception->getMessage(), 500);
        }
    }

    public function getMiRNASearch(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $this->logTopSearch('miRNA', $request->term);
            $query = Rna::query()->where('RNA', 'LIKE', "%$request->term%")->orderBy('RNA')->get();
            $data = [
                'data' => [
                    'diseases' => $query
                ]
            ];
            return response()->json($data);
        } catch (\Exception $exception) {
            return response()->json($exception->getMessage(), 500);
        }
    }

    public function getGeneSearch(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $this->logTopSearch('gene', $request->term);
            $query = Gene::query()->where('gene', 'LIKE', "%$request->term%")->orderBy('gene')->get();
            $data = [
                'data' => [
                    'diseases' => $query
                ]
            ];
            return response()->json($data);
        } catch (\Exception $exception) {
            return response()->json($exception->getMessage(), 500);
        }
    }

    public function getDiseaseSearch(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $this->logTopSearch('disease', $request->term);
            $query = Sentiment::query()->where('disease', 'LIKE', "%$request->term%")->orderBy('confidence', 'desc')->get();
            $data = [
                'data' => [
                    'drugs' => SentimentCollection::collection($query),
                    'genes' => Gene::query()->where('disease', 'LIKE', "%$request->term%")->get(),
                    'miRNAs' => Rna::query()->where('disease', 'LIKE', "%$request->term%")->get()
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

    public function getDrugSearch(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $this->logTopSearch('drug', $request->term);
            $query = Sentiment::query()->where('drug','LIKE', "%$request->term%")->orderBy('confidence', 'desc')->get();
            $data = [
                'data' => [
                    'diseases' => SentimentCollection::collection($query),
                    'sideEffects' => SideEffectResource::collection(SideEffect::query()->whereRelation('drugName', 'drugName', 'LIKE', "%$request->term%")->get()),
                    'PDBs' => PDBCollection::collection(Pdb::query()->where('drug', 'LIKE', "%$request->term%")->get()),
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
