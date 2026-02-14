<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Services\CertificateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CertificateController extends Controller
{
    public function __construct(
        protected CertificateService $certificateService,
    ) {}

    /**
     * List all certificates for the authenticated user.
     */
    public function index(Request $request): View
    {
        $certificates = $request->user()
            ->certificates()
            ->with('training')
            ->orderByDesc('date_issued')
            ->get();

        return view('certificates.index', compact('certificates'));
    }

    /**
     * Download the PDF for a specific certificate.
     */
    public function download(Request $request, Certificate $certificate): StreamedResponse
    {
        // Ensure the certificate belongs to the authenticated user
        if ($certificate->user_id !== $request->user()->id) {
            abort(403, 'You are not authorized to download this certificate.');
        }

        // Generate PDF on the fly if it doesn't already exist
        if (!$certificate->pdf_path || !Storage::exists($certificate->pdf_path)) {
            $this->certificateService->generatePdf($certificate);
            $certificate->refresh();
        }

        $filename = str_replace(' ', '_', $request->user()->full_name)
            . '_NADA_Certificate_' . $certificate->certificate_code . '.pdf';

        return Storage::download($certificate->pdf_path, $filename);
    }
}
