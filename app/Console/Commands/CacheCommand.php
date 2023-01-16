<?php

namespace App\Console\Commands;

use App\Http\Resources\AlternateMedicineCollection;
use App\Http\Resources\RNACollection;
use App\Http\Resources\SentimentCollection;
use App\Models\AlternateMedicine;
use App\Models\Gene;
use App\Models\Lncrna;
use App\Models\Rna;
use App\Models\Sentiment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class CacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:disease {term}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to cache disease results.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $data = Cache::rememberForever("{$this->argument('term')}", function() {
            $query = Sentiment::query()->where('disease', 'LIKE', "%{$this->argument('term')}%")->orderBy('confidence', 'desc')->get();
            $geneQuery = Gene::query()->where('disease', 'LIKE', "%{$this->argument('term')}%")->get();
            $rnaQuery = Rna::query()->where('disease', 'LIKE', "%{$this->argument('term')}%")->get();
            $lncRNAQuery = Lncrna::query()->where('disease', 'LIKE', "%{$this->argument('term')}%")->get();
            $am = AlternateMedicine::query()->where('disease', 'LIKE', "%{$this->argument('term')}%")->get();

            return [
                'data' => [
                    'drugs' => SentimentCollection::collection($query),
                    'genes' => $geneQuery,
                    'miRNAs' => RNACollection::collection($rnaQuery),
                    'lncRNAs' => RNACollection::collection($lncRNAQuery),
                    'alternateMedicines' => AlternateMedicineCollection::collection($am)
                ],
                'stats' => [
                    0 => ['name' => 'Positive', 'count' => $query->where('class', 'Positive')->count()],
                    1 => ['name' => 'Negative', 'count' => $query->where('class', 'Negative')->count()],
                    //2 => ['name' => 'Neutral', 'count' => $query->where('class', 'Neutral')->count()]
                ],
                'count' => [
                    'drugs' => $query->unique('drug')->count(),
                    'genes' => $geneQuery->unique('gene')->count(),
                    'miRNAs' => $rnaQuery->unique('RNA')->count(),
                    'lncRNAs' => $lncRNAQuery->unique('RNA')->count(),
                    'alternateMedicines' => $am->unique('drug')->count(),
                ]
            ];
        });
        $this->info(json_encode($data['count']));
        return Command::SUCCESS;
    }
}
