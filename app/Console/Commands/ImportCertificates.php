<?php

namespace App\Console\Commands;

use App\Models\Certificate;
use App\Models\User;
use App\Services\CertificateService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ImportCertificates extends Command
{
    protected $signature = 'nada:import-certificates {file : Path to CSV file}';
    protected $description = 'Import certificates from CSV with preserved codes';

    public function handle(CertificateService $certificateService): int
    {
        $filePath = $this->argument('file');

        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return Command::FAILURE;
        }

        $csv = array_map('str_getcsv', file($filePath));
        $header = array_shift($csv);
        $header = array_map('strtolower', array_map('trim', $header));

        $imported = 0;
        $notFound = 0;
        $duplicates = 0;
        $errors = 0;
        $importLog = [];

        $bar = $this->output->createProgressBar(count($csv));

        foreach ($csv as $row) {
            try {
                $data = array_combine($header, $row);

                $email = trim($data['member_email'] ?? $data['email'] ?? '');
                $code = trim($data['certificate_code'] ?? $data['code'] ?? '');
                $dateIssued = $data['date_issued'] ?? null;
                $expirationDate = $data['expiration_date'] ?? null;

                if (!$email || !$code) {
                    $errors++;
                    $bar->advance();
                    continue;
                }

                $user = User::where('email', $email)->first();
                if (!$user) {
                    $notFound++;
                    $bar->advance();
                    continue;
                }

                if (Certificate::where('certificate_code', $code)->exists()) {
                    $duplicates++;
                    $bar->advance();
                    continue;
                }

                $expDate = $expirationDate ? Carbon::parse($expirationDate) : null;
                $subscription = $user->activeSubscription;
                if ($subscription && $subscription->current_period_end) {
                    $expDate = $subscription->current_period_end;
                }

                $certificate = Certificate::create([
                    'user_id' => $user->id,
                    'certificate_code' => $code,
                    'date_issued' => $dateIssued ? Carbon::parse($dateIssued)->toDateString() : now()->toDateString(),
                    'expiration_date' => $expDate?->toDateString(),
                    'status' => ($expDate && $expDate->isPast()) ? 'expired' : 'active',
                ]);

                // Generate PDF
                try {
                    $certificateService->generatePdf($certificate);
                } catch (\Exception $e) {
                    // PDF generation may fail if images aren't present yet; continue
                }

                $imported++;
                $importLog[] = ['type' => 'certificate', 'id' => $certificate->id, 'code' => $code];
            } catch (\Exception $e) {
                $errors++;
                $this->error("Error: {$e->getMessage()}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        Storage::put('migration/import-certificates-log.json', json_encode($importLog, JSON_PRETTY_PRINT));

        $this->info("Certificate import complete:");
        $this->line("  Imported: {$imported}");
        $this->line("  Not found by email: {$notFound}");
        $this->line("  Duplicate codes: {$duplicates}");
        $this->line("  Errors: {$errors}");

        return Command::SUCCESS;
    }
}
