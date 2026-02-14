<?php

namespace App\Console\Commands;

use App\Services\StripeService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class StripeAudit extends Command
{
    protected $signature = 'nada:stripe-audit';
    protected $description = 'Fetch all Stripe data and generate audit report';

    public function handle(StripeService $stripeService): int
    {
        $this->info('Starting Stripe audit...');

        $report = [
            'generated_at' => now()->toISOString(),
            'products' => [],
            'prices' => [],
            'subscriptions' => [],
            'customers' => [],
            'summary' => [],
        ];

        // Fetch products
        $this->info('Fetching products...');
        $products = $stripeService->listProducts();
        foreach ($products as $product) {
            $report['products'][] = [
                'id' => $product->id,
                'name' => $product->name,
                'active' => $product->active,
            ];

            // Fetch prices for each product
            $prices = $stripeService->listPrices($product->id);
            foreach ($prices as $price) {
                $report['prices'][] = [
                    'id' => $price->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'unit_amount' => $price->unit_amount,
                    'currency' => $price->currency,
                    'recurring_interval' => $price->recurring?->interval ?? null,
                    'recurring_interval_count' => $price->recurring?->interval_count ?? null,
                    'active' => $price->active,
                ];
            }
        }

        // Fetch subscriptions (paginated)
        $this->info('Fetching subscriptions...');
        $hasMore = true;
        $startingAfter = null;
        while ($hasMore) {
            $params = ['limit' => 100, 'status' => 'all'];
            if ($startingAfter) {
                $params['starting_after'] = $startingAfter;
            }
            $subs = \Stripe\Subscription::all($params);
            foreach ($subs->data as $sub) {
                $report['subscriptions'][] = [
                    'id' => $sub->id,
                    'customer' => $sub->customer,
                    'price_id' => $sub->items->data[0]->price->id ?? null,
                    'status' => $sub->status,
                    'current_period_start' => $sub->current_period_start,
                    'current_period_end' => $sub->current_period_end,
                ];
                $startingAfter = $sub->id;
            }
            $hasMore = $subs->has_more;
        }

        // Fetch customers (paginated)
        $this->info('Fetching customers...');
        $hasMore = true;
        $startingAfter = null;
        while ($hasMore) {
            $params = ['limit' => 100];
            if ($startingAfter) {
                $params['starting_after'] = $startingAfter;
            }
            $customers = \Stripe\Customer::all($params);
            foreach ($customers->data as $customer) {
                $report['customers'][] = [
                    'id' => $customer->id,
                    'email' => $customer->email,
                    'name' => $customer->name,
                    'created' => $customer->created,
                ];
                $startingAfter = $customer->id;
            }
            $hasMore = $customers->has_more;
        }

        // Summary
        $activeSubCount = collect($report['subscriptions'])->where('status', 'active')->count();
        $statusBreakdown = collect($report['subscriptions'])->groupBy('status')->map->count();

        $report['summary'] = [
            'total_products' => count($report['products']),
            'total_prices' => count($report['prices']),
            'active_prices' => collect($report['prices'])->where('active', true)->count(),
            'inactive_prices' => collect($report['prices'])->where('active', false)->count(),
            'total_subscriptions' => count($report['subscriptions']),
            'subscription_status_breakdown' => $statusBreakdown,
            'total_customers' => count($report['customers']),
            'active_subscriptions' => $activeSubCount,
        ];

        // Save report
        Storage::put('migration/stripe-audit.json', json_encode($report, JSON_PRETTY_PRINT));
        $this->info('Audit report saved to storage/app/migration/stripe-audit.json');

        // Print summary
        $this->table(['Metric', 'Value'], collect($report['summary'])->except('subscription_status_breakdown')->map(fn ($v, $k) => [$k, $v])->values()->toArray());

        $this->info('Subscription status breakdown:');
        foreach ($statusBreakdown as $status => $count) {
            $this->line("  {$status}: {$count}");
        }

        return Command::SUCCESS;
    }
}
