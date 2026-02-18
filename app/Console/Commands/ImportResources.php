<?php

namespace App\Console\Commands;

use App\Models\Resource;
use App\Models\ResourceCategory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportResources extends Command
{
    protected $signature = 'nada:import-resources
        {--limit=0 : Limit number of resources to import (0 = all)}
        {--dry-run : Preview what would happen without writing to DB}
        {--skip-files : Skip downloading file attachments}';

    protected $description = 'Import resources from WordPress CSV export';

    private int $imported = 0;
    private int $skipped = 0;
    private int $errors = 0;
    private int $filesDownloaded = 0;
    private int $fileErrors = 0;
    private array $importLog = [];

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $limit = (int) $this->option('limit');
        $skipFiles = $this->option('skip-files');

        if ($dryRun) {
            $this->components->warn('DRY RUN — no database writes will be made.');
        }

        $csvFile = 'Resources-Export-2026-February-18-0129.csv';
        $csvPath = storage_path("app/migration/{$csvFile}");
        if (! file_exists($csvPath)) {
            // Fallback to project root for local dev
            $csvPath = base_path($csvFile);
        }
        if (! file_exists($csvPath)) {
            $this->error("CSV file not found. Place it at storage/app/migration/{$csvFile}");
            return Command::FAILURE;
        }

        $rows = $this->parseCsv($csvPath);
        $this->components->info('Parsed ' . count($rows) . ' rows from CSV.');

        // Phase 1: Create categories
        $categoryMap = $this->buildCategories($rows, $dryRun);
        $this->components->info('Categories: ' . count($categoryMap));

        // Phase 2: Import resources
        if ($limit > 0) {
            $rows = array_slice($rows, 0, $limit);
            $this->components->info("Limited to {$limit} rows.");
        }

        $bar = $this->output->createProgressBar(count($rows));

        foreach ($rows as $row) {
            try {
                $this->importRow($row, $categoryMap, $dryRun, $skipFiles);
            } catch (\Throwable $e) {
                $this->errors++;
                $this->importLog[] = [
                    'wp_id' => $row[0] ?? '?',
                    'status' => 'error',
                    'error' => $e->getMessage(),
                ];
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Write log
        if (! $dryRun) {
            Storage::makeDirectory('migration');
            Storage::put('migration/import-resources-log.json', json_encode($this->importLog, JSON_PRETTY_PRINT));
            $this->components->info('Log written to storage/app/migration/import-resources-log.json');
        }

        // Summary table
        $this->table(
            ['Metric', 'Count'],
            [
                ['Imported', $this->imported],
                ['Skipped (duplicate/non-publish)', $this->skipped],
                ['Errors', $this->errors],
                ['Files Downloaded', $this->filesDownloaded],
                ['File Download Errors', $this->fileErrors],
                ['Categories Created', count($categoryMap)],
            ]
        );

        return Command::SUCCESS;
    }

    private function parseCsv(string $path): array
    {
        $handle = fopen($path, 'r');

        // Skip BOM if present
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $header = fgetcsv($handle);
        $rows = [];

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) >= 26) {
                $rows[] = $row;
            }
        }

        fclose($handle);

        return $rows;
    }

    private function buildCategories(array $rows, bool $dryRun): array
    {
        $categoryNames = [];

        foreach ($rows as $row) {
            $resourceTypes = $row[14] ?? '';
            if (empty($resourceTypes)) {
                continue;
            }

            foreach (explode('|', $resourceTypes) as $entry) {
                $entry = trim($entry);
                if (! str_contains($entry, '>')) {
                    continue;
                }

                [$parent, $child] = explode('>', $entry, 2);
                $child = html_entity_decode(trim($child), ENT_QUOTES, 'UTF-8');

                // Parents are access levels, not categories
                if (in_array(trim($parent), ['Members Only', 'Public'])) {
                    $categoryNames[$child] = true;
                }
            }
        }

        $map = [];

        foreach (array_keys($categoryNames) as $name) {
            $slug = Str::slug($name);

            if ($dryRun) {
                $this->components->twoColumnDetail($name, $slug);
                $map[$name] = $slug;
            } else {
                $cat = ResourceCategory::firstOrCreate(
                    ['slug' => $slug],
                    ['name' => $name, 'sort_order' => 0]
                );
                $map[$name] = $cat->id;
            }
        }

        return $map;
    }

    private function importRow(array $row, array $categoryMap, bool $dryRun, bool $skipFiles): void
    {
        $wpId = (int) $row[0];
        $title = trim($row[1]);
        $content = $row[2] ?? '';
        $excerpt = trim($row[3] ?? '');
        $date = $row[4] ?? null;
        $status = strtolower(trim($row[19] ?? ''));
        $slug = trim($row[25] ?? '');
        $fileUrls = trim($row[13] ?? '');
        $resourceTypes = trim($row[14] ?? '');

        // Skip non-publish
        if ($status !== 'publish') {
            $this->skipped++;
            $this->importLog[] = ['wp_id' => $wpId, 'status' => 'skipped', 'reason' => "status: {$status}"];
            return;
        }

        // Skip duplicate
        if (! $dryRun && Resource::where('wp_post_id', $wpId)->exists()) {
            $this->skipped++;
            $this->importLog[] = ['wp_id' => $wpId, 'status' => 'skipped', 'reason' => 'duplicate'];
            return;
        }

        // Parse categories and determine members-only
        $isMembersOnly = false;
        $categoryIds = [];

        foreach (explode('|', $resourceTypes) as $entry) {
            $entry = trim($entry);
            if (! str_contains($entry, '>')) {
                continue;
            }

            [$parent, $child] = explode('>', $entry, 2);
            $parent = trim($parent);
            $child = html_entity_decode(trim($child), ENT_QUOTES, 'UTF-8');

            if ($parent === 'Members Only') {
                $isMembersOnly = true;
            }

            if (isset($categoryMap[$child])) {
                $categoryIds[] = $categoryMap[$child];
            }
        }

        $categoryIds = array_unique($categoryIds);

        // Strip WP block editor comments
        $body = preg_replace('/<!--\s*\/?wp:[\s\S]*?-->/', '', $content);
        // Fix WordPress double-encoded non-breaking spaces (Â artifacts)
        $body = str_replace("\xC2\xA0", ' ', $body);
        $body = str_replace('Â', '', $body);
        $body = trim($body);

        // Extract YouTube video ID
        $videoEmbed = null;
        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([\w-]{11})/', $body, $m)) {
            $videoEmbed = $m[1];
        }

        // Ensure slug is unique
        if (empty($slug)) {
            $slug = Str::slug($title);
        }

        // Parse date
        $publishedAt = null;
        if ($date) {
            try {
                $publishedAt = \Carbon\Carbon::parse($date);
            } catch (\Throwable $e) {
                // ignore invalid dates
            }
        }

        if ($dryRun) {
            $this->imported++;
            $cats = implode(', ', array_keys(array_filter($categoryMap, fn ($id) => in_array($id, $categoryIds))));
            $this->components->twoColumnDetail(
                "[{$wpId}] {$title}",
                ($isMembersOnly ? 'MEMBERS ' : 'PUBLIC ') . "| {$cats}" . ($videoEmbed ? " | YT:{$videoEmbed}" : '')
            );
            $this->importLog[] = ['wp_id' => $wpId, 'status' => 'dry_run', 'title' => $title];
            return;
        }

        $resource = Resource::create([
            'title' => $title,
            'slug' => $slug,
            'body' => $body ?: null,
            'excerpt' => $excerpt ?: null,
            'is_members_only' => $isMembersOnly,
            'video_embed' => $videoEmbed,
            'is_published' => true,
            'published_at' => $publishedAt,
            'wp_post_id' => $wpId,
        ]);

        $resource->categories()->sync($categoryIds);

        // Download files
        if (! $skipFiles && ! empty($fileUrls)) {
            foreach (explode('|', $fileUrls) as $url) {
                $url = trim($url);
                if (empty($url)) {
                    continue;
                }

                try {
                    $resource->addMediaFromUrl($url)
                        ->toMediaCollection('attachments');
                    $this->filesDownloaded++;
                } catch (\Throwable $e) {
                    $this->fileErrors++;
                    $this->importLog[] = [
                        'wp_id' => $wpId,
                        'status' => 'file_error',
                        'url' => $url,
                        'error' => $e->getMessage(),
                    ];
                }
            }
        }

        // Download inline linked files and rewrite URLs
        if (! $skipFiles && $body) {
            preg_match_all('/href="(https?:\/\/acudetox\.com\/wp-content\/uploads\/[^"]+)"/', $body, $inlineMatches);
            if (! empty($inlineMatches[1])) {
                $updatedBody = $body;
                foreach (array_unique($inlineMatches[1]) as $inlineUrl) {
                    try {
                        $media = $resource->addMediaFromUrl($inlineUrl)
                            ->toMediaCollection('attachments');
                        $updatedBody = str_replace($inlineUrl, $media->getUrl(), $updatedBody);
                        $this->filesDownloaded++;
                    } catch (\Throwable $e) {
                        $this->fileErrors++;
                        $this->importLog[] = [
                            'wp_id' => $wpId,
                            'status' => 'inline_file_error',
                            'url' => $inlineUrl,
                            'error' => $e->getMessage(),
                        ];
                    }
                }
                if ($updatedBody !== $body) {
                    $resource->update(['body' => $updatedBody]);
                }
            }
        }

        $this->imported++;
        $this->importLog[] = ['wp_id' => $wpId, 'status' => 'imported', 'id' => $resource->id, 'title' => $title];
    }
}
