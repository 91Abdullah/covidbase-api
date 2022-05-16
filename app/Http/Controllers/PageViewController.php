<?php

namespace App\Http\Controllers;

use App\Models\Gene;
use App\Models\PageView;
use App\Models\Pdb;
use App\Models\Sentiment;
use Illuminate\Http\Request;

class PageViewController extends Controller
{
    public function getKBStats(): \Illuminate\Http\JsonResponse
    {
        try {
            $stats = [
                'drugs' => Sentiment::query()->select(['drug'])->distinct()->count('drug'),
                'diseases' => Sentiment::query()->select(['disease'])->distinct()->count('disease'),
                'genes' => Gene::query()->select('gene')->distinct()->count('gene'),
                'miRNAs' => "0",
                'PDBs' => Pdb::query()->select(['pdb'])->distinct()->count('pdb'),
                'drugDiseasePairs' => Sentiment::query()->select(['drug', 'disease'])->count(),
                'drugPDBPairs' => Pdb::query()->select(['drug', 'pdb'])->count(),
                'DiseaseGenePairs' => Gene::query()->select(['disease', 'gene'])->count(),
                'DiseaseMiRNAPairs' => "0",
            ];
            return response()->json($stats);
        } catch (\Exception $exception) {
            return response()->json($exception->getMessage(), 500);
        }
    }

    public function getAllDrugNames(): \Illuminate\Http\JsonResponse
    {
        try {
            $drugs = Sentiment::query()->select('drug')->distinct()->orderBy('drug')->get()->map(function ($v) { return $v->drug; })->groupBy(function($i) { return $i[0]; });
            return response()->json($drugs->toArray());
        } catch (\Exception $exception) {
            return response()->json($exception->getMessage(), 500);
        }
    }

    public function getAllDrugDiseasePairs(): \Illuminate\Http\JsonResponse
    {
        try {
            $pairs = Sentiment::query()->select(['drug', 'disease'])->get()->map(function ($v) {
                return "{$v->disease}+{$v->drug}";
            })->groupBy(function ($i) {
                return $i->disease[0];
            });
            return response()->json($pairs);
        } catch (\Exception $exception) {
            return response()->json($exception->getMessage(), 500);
        }
    }

    public function getAllDiseaseNames(): \Illuminate\Http\JsonResponse
    {
        try {
            $diseases = Sentiment::query()->select('disease')->distinct()->orderBy('disease')->get()->map(function ($v) { return ucfirst($v->disease); })->groupBy(function($i) { return $i[0]; });
            return response()->json($diseases->toArray());
        } catch (\Exception $exception) {
            return response()->json($exception->getMessage(), 500);
        }
    }

    public function getAllGeneNames(): \Illuminate\Http\JsonResponse
    {
        try {
            $genes = Gene::query()->select('gene')->distinct()->get()->groupBy(function($i) { return $i->gene[0]; });
            return response()->json($genes);
        } catch (\Exception $exception) {
            return response()->json($exception->getMessage(), 500);
        }
    }

    public function getAllPDBNames(): \Illuminate\Http\JsonResponse
    {
        try {
            $pdbs = Gene::query()->select('pdb')->distinct()->get()->groupBy(function($i) { return $i->pdb[0]; });
            return response()->json($pdbs);
        } catch (\Exception $exception) {
            return response()->json($exception->getMessage(), 500);
        }
    }

    public function getAllDrugPDBPairs(): \Illuminate\Http\JsonResponse
    {
        try {
            $pairs = Pdb::query()->select(['drug', 'pdb'])->get()->map(function ($v) {
                return "{$v->drug}+{$v->pdb}";
            })->groupBy(function ($i) {
                return $i->drug[0];
            });
            return response()->json($pairs);
        } catch (\Exception $exception) {
            return response()->json($exception->getMessage(), 500);
        }
    }

    public function getAllDiseaseGenePairs(): \Illuminate\Http\JsonResponse
    {
        try {
            $pairs = Gene::query()->select(['disease', 'gene'])->get()->map(function ($v) {
                return "{$v->disease}+{$v->gene}";
            })->groupBy(function ($i) {
                return $i->disease[0];
            });
            return response()->json($pairs);
        } catch (\Exception $exception) {
            return response()->json($exception->getMessage(), 500);
        }
    }

    public function getAllDiseaseMiRNAPairs(): \Illuminate\Http\JsonResponse
    {
        try {
            $pairs = Gene::query()->select(['disease', 'miRNA'])->get()->map(function ($v) {
                return "{$v->disease}+{$v->miRNA}";
            })->groupBy(function ($i) {
                return $i->disease[0];
            });
            return response()->json($pairs);
        } catch (\Exception $exception) {
            return response()->json($exception->getMessage(), 500);
        }
    }

    public function logPageView(Request $request): \Illuminate\Http\JsonResponse
    {
        if(config('app.log_page_view')) {
            PageView::query()->create($request->all());
            return response()->json('Page visit logged');
        }

        return response()->json('Page view disabled.');
    }
}
