<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ChatbotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function send(Request $request, ChatbotService $chatbot): JsonResponse
    {
        if (! config('chatbot.enabled')) {
            return response()->json(['error' => 'Chat is currently unavailable.'], 503);
        }

        $request->validate([
            'messages' => 'required|array|max:' . config('chatbot.max_messages', 20),
            'messages.*.role' => 'required|in:user,assistant',
            'messages.*.content' => 'required|string|max:5000',
            'current_path' => 'nullable|string|max:255',
        ]);

        $ip = $request->ip();

        if ($chatbot->checkRateLimit($ip)) {
            return response()->json(['error' => 'Too many requests. Please try again later.'], 429);
        }

        $chatbot->hitRateLimit($ip);

        $userContext = $this->buildUserContext();

        $currentPath = $request->input('current_path');
        if ($currentPath) {
            $userContext .= "\n\n## Current Page\nThe user is currently viewing: `{$currentPath}`";
        }

        try {
            $response = $chatbot->sendMessage($request->input('messages'), $userContext);

            return response()->json(['content' => $response]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['error' => 'Something went wrong. Please try again.'], 500);
        }
    }

    private function buildUserContext(): ?string
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user) {
            return null;
        }

        $user->load([
            'activeSubscription.plan',
            'certificates.training',
            'trainingRegistrations.training',
            'clinicals',
            'roles',
        ]);

        $lines = [];
        $lines[] = '## Current User Context';
        $lines[] = '';
        $lines[] = "- **Name:** {$user->full_name}";
        $lines[] = "- **Email:** {$user->email}";

        $roles = $user->roles->pluck('name')->map(fn ($r) => ucwords(str_replace('_', ' ', $r)))->implode(', ');
        $lines[] = "- **Role(s):** {$roles}";

        // Subscription
        $sub = $user->activeSubscription;
        if ($sub) {
            $planName = $sub->plan?->name ?? 'Unknown';
            $lines[] = '';
            $lines[] = '### Subscription';
            $lines[] = "- **Plan:** {$planName}";
            $lines[] = "- **Status:** {$sub->status->value}";
            if ($sub->current_period_end) {
                $lines[] = '- **Renewal date:** ' . $sub->current_period_end->format('F j, Y');
            }
            if ($sub->cancel_at_period_end) {
                $lines[] = '- **Canceling:** Yes — access continues until the renewal date above, then the subscription ends.';
            }
        } else {
            $lines[] = '';
            $lines[] = '### Subscription';
            $lines[] = '- No active subscription.';
        }

        // Certificates
        if ($user->certificates->isNotEmpty()) {
            $lines[] = '';
            $lines[] = '### Certificates';
            foreach ($user->certificates as $cert) {
                $trainingName = $cert->training?->title ?? 'N/A';
                $issued = $cert->date_issued?->format('F j, Y') ?? 'N/A';
                $expires = $cert->expiration_date?->format('F j, Y') ?? 'No expiration';
                $lines[] = "- **{$cert->certificate_code}** — Training: {$trainingName}, Issued: {$issued}, Expires: {$expires}, Status: {$cert->status}";
            }
        }

        // Training registrations
        if ($user->trainingRegistrations->isNotEmpty()) {
            $lines[] = '';
            $lines[] = '### Training Registrations';
            foreach ($user->trainingRegistrations as $reg) {
                $title = $reg->training?->title ?? 'Unknown';
                $date = $reg->training?->start_date?->format('F j, Y') ?? 'TBD';
                $status = $reg->status->value;
                $lines[] = "- **{$title}** — Date: {$date}, Status: {$status}";
            }
        }

        // Clinicals
        if ($user->clinicals->isNotEmpty()) {
            $lines[] = '';
            $lines[] = '### Clinical Submissions';
            foreach ($user->clinicals as $clinical) {
                $estDate = $clinical->estimated_training_date?->format('F j, Y') ?? 'N/A';
                $lines[] = "- Submitted for training date {$estDate} — Status: {$clinical->status}";
            }
        }

        // Discount
        if ($user->discount_type && $user->discount_type !== \App\Enums\DiscountType::None) {
            $lines[] = '';
            $lines[] = '### Discount';
            $lines[] = "- **Type:** {$user->discount_type->label()}";
            $lines[] = '- **Approved:** ' . ($user->discount_approved ? 'Yes' : 'Pending');
        }

        return implode("\n", $lines);
    }
}
