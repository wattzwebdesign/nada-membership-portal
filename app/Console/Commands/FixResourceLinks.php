<?php

namespace App\Console\Commands;

use App\Models\Resource;
use Illuminate\Console\Command;

class FixResourceLinks extends Command
{
    protected $signature = 'nada:fix-resource-links
        {--dry-run : Preview what would happen without downloading or updating}';

    protected $description = 'Download inline acudetox.com file links in resource content and rewrite URLs to local media';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->components->warn('DRY RUN — no downloads or updates will be made.');
        }

        $resources = Resource::where('body', 'like', '%acudetox.com/wp-content/uploads/%')->get();

        $this->components->info("Found {$resources->count()} resources with inline acudetox.com links.");

        $totalLinks = 0;
        $downloaded = 0;
        $errors = 0;

        $bar = $this->output->createProgressBar($resources->count());

        foreach ($resources as $resource) {
            $body = $resource->body;

            // Find all acudetox.com file URLs in href attributes
            preg_match_all('/href="(https?:\/\/acudetox\.com\/wp-content\/uploads\/[^"]+)"/', $body, $matches);

            if (empty($matches[1])) {
                $bar->advance();
                continue;
            }

            $urls = array_unique($matches[1]);
            $totalLinks += count($urls);

            foreach ($urls as $url) {
                if ($dryRun) {
                    $this->newLine();
                    $this->components->twoColumnDetail(
                        "[{$resource->wp_post_id}] " . basename($url),
                        $url
                    );
                    continue;
                }

                try {
                    $media = $resource->addMediaFromUrl($url)
                        ->toMediaCollection('attachments');

                    // Replace old URL with new Spatie media URL
                    $body = str_replace($url, $media->getUrl(), $body);
                    $downloaded++;
                } catch (\Throwable $e) {
                    $errors++;
                    $this->newLine();
                    $this->components->error("[{$resource->wp_post_id}] Failed: {$url} — {$e->getMessage()}");
                }
            }

            if (! $dryRun && $body !== $resource->body) {
                $resource->update(['body' => $body]);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(
            ['Metric', 'Count'],
            [
                ['Resources with links', $resources->count()],
                ['Total inline links', $totalLinks],
                ['Files downloaded', $downloaded],
                ['Errors', $errors],
            ]
        );

        return Command::SUCCESS;
    }
}
