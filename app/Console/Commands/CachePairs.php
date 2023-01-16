<?php

namespace App\Console\Commands;

use App\Models\Sentiment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CachePairs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:pair {pair} {table} {orderBy}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to cache pairs ex. Drug-Disease. Use pairs as drug-disease, drug-pdb, disease-RNA and disease-gene.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $pairArr = explode('-', $this->argument('pair'));
        $pairs = Cache::rememberForever($this->argument('pair'), function () use ($pairArr) {
            return DB::table($this->argument('table'))->select([$pairArr[0], $pairArr[1]])->orderBy($this->argument('orderBy'))->get()->map(function ($v) use ($pairArr) {
                $v->{$pairArr[0]} = trim($v->{$pairArr[0]});
                $v->{$pairArr[1]} = trim($v->{$pairArr[1]});
                return "{$v->{$pairArr[0]}}+{$v->{$pairArr[1]}}";
            })->groupBy(function ($i) {
                return ucfirst($i[0]);
            });
        });
        $count = count($pairs);
        $this->info("Count: $count");
        return Command::SUCCESS;
    }
}
