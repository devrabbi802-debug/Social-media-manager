<?php

namespace App\Console\Commands;

use App\Jobs\GenerateTextEmbeddingJob;
use App\Models\Product;
use Illuminate\Console\Command;

class GenerateAllTextEmbeddings extends Command
{
    protected $signature = 'tenants:generate-text-embeddings';

    protected $description = 'Generate text embeddings for all products missing them';

    public function handle(): int
    {
        $tenants = \App\Models\Tenant::all();
        $totalDispatched = 0;

        foreach ($tenants as $tenant) {
            $tenant->run(function () use ($tenant, &$totalDispatched) {
                $products = Product::whereNull('text_embedding')
                    ->where('status', 'active')
                    ->get();

                $this->info("Tenant: {$tenant->id} — {$products->count()} products need embeddings");

                foreach ($products as $product) {
                    GenerateTextEmbeddingJob::dispatch($product);
                    $totalDispatched++;
                }
            });
        }

        $this->info("Dispatched {$totalDispatched} text embedding jobs.");

        return Command::SUCCESS;
    }
}
