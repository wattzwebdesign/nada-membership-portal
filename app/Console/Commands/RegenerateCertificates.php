<?php

namespace App\Console\Commands;

use App\Models\Certificate;
use App\Services\CertificateService;
use Illuminate\Console\Command;

class RegenerateCertificates extends Command
{
    protected $signature = 'nada:regenerate-certificates
                            {--id= : Regenerate a specific certificate by ID}
                            {--status=active : Only regenerate certificates with this status (or "all")}';

    protected $description = 'Regenerate PDF files for existing certificates using the current template';

    public function handle(CertificateService $certificateService): int
    {
        $query = Certificate::with('user');

        if ($id = $this->option('id')) {
            $query->where('id', $id);
        } elseif (($status = $this->option('status')) !== 'all') {
            $query->where('status', $status);
        }

        $certificates = $query->get();

        if ($certificates->isEmpty()) {
            $this->warn('No certificates found matching the criteria.');
            return Command::SUCCESS;
        }

        $this->info("Regenerating {$certificates->count()} certificate PDF(s)...");

        $success = 0;
        $failed = 0;

        $bar = $this->output->createProgressBar($certificates->count());
        $bar->start();

        foreach ($certificates as $certificate) {
            try {
                $certificateService->generatePdf($certificate);
                $success++;
            } catch (\Throwable $e) {
                $failed++;
                $this->newLine();
                $this->error("  Failed: #{$certificate->id} ({$certificate->certificate_code}) - {$e->getMessage()}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Done! Regenerated: {$success}, Failed: {$failed}");

        return $failed > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
