<?php

namespace App\Http\Controllers;

use App\Models\AlternateMedicine;
use App\Models\Gene;
use App\Models\Lncrna;
use App\Models\PageView;
use App\Models\Pdb;
use App\Models\Rna;
use App\Models\Sentiment;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class PageViewController extends Controller
{
    public function getOptions(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $type = $request->type;
            $term = $request->term;
            $results = match ($type) {
                default => null,
                'Drugs' => Sentiment::query()->distinct()->select(['drug'])->where('drug', 'LIKE', "%$term%")->pluck('drug'),
                'AlternateMedicine' => AlternateMedicine::query()->distinct()->select(['drug'])->where('drug', 'LIKE', "%$term%")->pluck('drug'),
                'Diseases' => Sentiment::query()->distinct()->select(['disease'])->where('disease', 'LIKE', "%$term%")->pluck('disease'),
                'Genes' => Gene::query()->distinct()->select(['gene'])->where('gene', 'LIKE', "%$term%")->pluck('gene'),
                'miRNAs' => Rna::query()->distinct()->select(['RNA'])->where('RNA', 'LIKE', "%$term%")->pluck('RNA')->transform(function ($v) {
                    return trim($v);
                }),
                'lncRNAs' => Lncrna::query()->distinct()->select(['RNA'])->where('RNA', 'LIKE', "%$term%")->pluck('RNA')->transform(function ($v) {
                    return trim($v);
                }),
                'PDBs' => Pdb::query()->distinct()->select(['pdb'])->where('pdb', 'LIKE', "%$term%")->pluck('pdb')->transform(function ($v) {
                    return trim($v);
                }),
                'Drug-Disease' => Sentiment::query()->distinct()->where('disease', 'LIKE', "%$term%")->orWhere('drug', 'LIKE', "%$term%")->select(['drug', 'disease'])->get()->map(function ($v) {
                    return "{$v->drug}+{$v->disease}";
                }),
                'Disease-Gene' => Gene::query()->distinct()->where('disease', 'LIKE', "%$term")->orWhere('gene', 'LIKE', "%$term%")->select(['disease', 'gene'])->get()->map(function ($v) {
                    return "{$v->disease}+{$v->gene}";
                }),
                'Disease-miRNA' => Rna::query()->distinct()->where('disease', 'LIKE', "%$term")->orWhere('RNA', 'LIKE', "%$term%")->select(['disease', 'RNA'])->get()->map(function ($v) {
                    $v->disease = trim($v->disease);
                    $v->RNA = trim($v->RNA);
                    return "{$v->disease}+{$v->RNA}";
                }),
                'Disease-lncRNA' => Lncrna::query()->distinct()->where('disease', 'LIKE', "%$term")->orWhere('RNA', 'LIKE', "%$term%")->select(['disease', 'RNA'])->get()->map(function ($v) {
                    $v->disease = trim($v->disease);
                    $v->RNA = trim($v->RNA);
                    return "{$v->disease}+{$v->RNA}";
                }),
                'Drug-PDB' => Pdb::query()->distinct()->where('drug', 'LIKE', "%$term")->orWhere('pdb', 'LIKE', "%$term%")->select(['drug', 'pdb'])->get()->map(function ($v) {
                    $str = trim($v->drug);
                    $str2 = trim($v->pdb);
                    return "{$str}+{$str2}";
                }),
            };
            $results = $results->map(function ($v) {
                return ['label' => $v, 'value' => $v];
            });
            return response()->json($results);
        } catch (\Exception $exception) {
            return response()->json($exception->getMessage(), 500);
        }
    }

    public function getKBStats(): \Illuminate\Http\JsonResponse
    {
        try {
            $stats = [
                'visits' => PageView::query()->select(['userId'])->distinct()->count('userId'),
                'drugs' => Sentiment::query()->select(['drug'])->distinct()->count('drug'),
                'alternateMedicine' => AlternateMedicine::query()->select(['drug'])->distinct()->count('drug'),
                'diseases' => Sentiment::query()->select(['disease'])->distinct()->count('disease'),
                'genes' => Gene::query()->select('gene')->distinct()->count('gene'),
                'miRNAs' => Rna::query()->select(['RNA'])->distinct()->count('RNA'),
                'lncRNAs' => Lncrna::query()->select(['RNA'])->distinct()->count('RNA'),
                'PDBs' => Pdb::query()->select(['pdb'])->distinct()->count('pdb'),
                'drugDiseasePairs' => Sentiment::query()->select(['drug', 'disease'])->distinct()->count(['drug', 'disease']),
                'drugPDBPairs' => Pdb::query()->select(['drug', 'pdb'])->distinct()->count(['drug', 'pdb']),
                'diseaseGenePairs' => Gene::query()->select(['disease', 'gene'])->distinct()->count(['disease', 'gene']),
                'diseaseMiRNAPairs' => Rna::query()->select(['disease', 'RNA'])->distinct()->count(['disease', 'RNA']),
                'diseaseLncRNAPairs' => Lncrna::query()->select(['disease', 'RNA'])->distinct()->count(['disease', 'RNA']),
            ];
            return response()->json($stats);
        } catch (\Exception $exception) {
            return response()->json($exception->getMessage(), 500);
        }
    }

    public function getAllAlternateMedicines(): \Illuminate\Http\JsonResponse
    {
        try {
            $am = AlternateMedicine::query()->select('drug')->distinct()->orderBy('drug')->get()->map(function ($v) { return $v->drug; })->groupBy(function ($i) { return $i[0]; });
            return response()->json($am->toArray());
        } catch (\Exception $exception) {
            return response()->json($exception->getMessage(), 500);
        }
    }

    public function getAllDrugNames(): \Illuminate\Http\JsonResponse
    {
        try {
            $drugs = Sentiment::query()->select('drug')->distinct()->orderBy('drug')->get()->map(function ($v) { return $v->drug; })->groupBy(function($i) { return ucfirst($i[0]); });
            return response()->json($drugs->toArray());
        } catch (\Exception $exception) {
            return response()->json($exception->getMessage(), 500);
        }
    }

    private function paginator(Request $request, array $array, int $per_page, int $current_page = 1): LengthAwarePaginator
    {
        $total = count($array);
        $starting_point = ($current_page * $per_page) - $per_page;
        $array = array_slice($array, $starting_point, $per_page, true);
        return new LengthAwarePaginator($array, $total, $per_page, $current_page, [
            'path' => $request->url(),
            'query' => $request->query()
        ]);
    }

    public function getAllDrugDiseasePairs(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $pairs = Cache::rememberForever('disease-drug', function () {
                return Sentiment::query()->select(['drug', 'disease'])->orderBy('disease')->get()->map(function ($v) {
                    return "{$v->disease}+{$v->drug}";
                })->groupBy(function ($i) {
                    return ucfirst($i[0]);
                });
            });
            return response()->json($pairs);
        } catch (\Exception $exception) {
            return response()->json($exception->getMessage(), 500);
        }
    }

    public function getAllDiseaseNames(): \Illuminate\Http\JsonResponse
    {
        try {
            $diseases = Sentiment::query()->select('disease')->distinct()->orderBy('disease')->get()->map(function ($v) { return ucfirst(trim($v->disease)); })->groupBy(function($i) { return $i[0]; });
            return response()->json($diseases->toArray());
        } catch (\Exception $exception) {
            return response()->json($exception->getMessage(), 500);
        }
    }

    public function getAllGeneNames(): \Illuminate\Http\JsonResponse
    {
        try {
            $genes = Gene::query()->select('gene')->distinct()->orderBy('gene')->get()->map(function ($v) { return ucfirst(trim($v->gene)); })->groupBy(function($i) { return $i[0]; });
            return response()->json($genes);
        } catch (\Exception $exception) {
            return response()->json($exception->getMessage(), 500);
        }
    }

    public function getAllPDBNames(): \Illuminate\Http\JsonResponse
    {
        try {
            $pdbs = Pdb::query()->select('pdb')->distinct()->orderBy('pdb')->get()->map(function ($v) {
                return ucfirst(trim($v->pdb));
            })->groupBy(function ($i) { return $i[0]; });
            return response()->json($pdbs);
        } catch (\Exception $exception) {
            return response()->json($exception->getMessage(), 500);
        }
    }

    public function getAllMirnaNames(): \Illuminate\Http\JsonResponse
    {
        try {
            $RNAs = Rna::query()->select('RNA')->distinct()->orderBy('RNA')->get()->map(function ($v) {
                return trim($v->RNA);
            })->groupBy(function ($v) {
                return ucfirst($v[0]);
            });
            return response()->json($RNAs);
        } catch (\Exception $exception) {
            return response()->json($exception->getMessage(), 500);
        }
    }

    public function getAllLncrnaNames(): \Illuminate\Http\JsonResponse
    {
        try {
            $RNAs = Lncrna::query()->select('RNA')->distinct()->orderBy('RNA')->get()->map(function ($v) {
                return trim($v->RNA);
            })->groupBy(function ($v) {
                return ucfirst($v[0]);
            });
            return response()->json($RNAs);
        } catch (\Exception $exception) {
            return response()->json($exception->getMessage(), 500);
        }
    }

    public function getAllDrugPDBPairs(): \Illuminate\Http\JsonResponse
    {
        try {
            $pairs = Pdb::query()->select(['drug', 'pdb'])->orderBy('drug')->get()->map(function ($v) {
                $v->drug = trim($v->drug);
                $v->pdb = trim($v->pdb);
                return "{$v->drug}+{$v->pdb}";
            })->groupBy(function ($i) {
                return $i[0];
            });
            return response()->json($pairs);
        } catch (\Exception $exception) {
            return response()->json($exception->getMessage(), 500);
        }
    }

    public function getAllDiseaseGenePairs(): \Illuminate\Http\JsonResponse
    {
        try {
            $pairs = Gene::query()->select(['disease', 'gene'])->orderBy('disease')->get()->map(function ($v) {
                $v->disease = ucfirst(trim($v->disease));
                $v->gene = trim($v->gene);
                return "{$v->disease}+{$v->gene}";
            })->groupBy(function ($i) {
                return ucfirst($i[0]);
            });
            return response()->json($pairs);
        } catch (\Exception $exception) {
            return response()->json($exception->getMessage(), 500);
        }
    }

    public function getAllDiseaseMiRNAPairs(): \Illuminate\Http\JsonResponse
    {
        try {
            $pairs = Rna::query()->select(['disease', 'RNA'])->get()->map(function ($v) {
                $v->disease = ucfirst(trim($v->disease));
                $v->RNA = trim($v->RNA);
                return "{$v->disease}+{$v->RNA}";
            })->groupBy(function ($i) {
                return $i[0];
            });
            return response()->json($pairs);
        } catch (\Exception $exception) {
            return response()->json($exception->getMessage(), 500);
        }
    }

    public function getAllDiseaseLncRNAPairs(): \Illuminate\Http\JsonResponse
    {
        try {
            $pairs = Lncrna::query()->select(['disease', 'RNA'])->get()->map(function ($v) {
                $v->disease = ucfirst(trim($v->disease));
                $v->RNA = trim($v->RNA);
                return "{$v->disease}+{$v->RNA}";
            })->groupBy(function ($i) {
                return $i[0];
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
