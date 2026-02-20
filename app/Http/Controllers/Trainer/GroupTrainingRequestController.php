<?php

namespace App\Http\Controllers\Trainer;

use App\Http\Controllers\Controller;
use App\Models\GroupTrainingRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GroupTrainingRequestController extends Controller
{
    public function index(Request $request): View
    {
        $requests = GroupTrainingRequest::where('trainer_id', $request->user()->id)
            ->whereNotNull('paid_at')
            ->with('training')
            ->withCount('members')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('trainer.group-requests.index', [
            'requests' => $requests,
        ]);
    }

    public function show(Request $request, GroupTrainingRequest $groupTrainingRequest): View
    {
        if ($groupTrainingRequest->trainer_id !== $request->user()->id) {
            abort(403, 'You do not have permission to view this request.');
        }

        if (!$groupTrainingRequest->paid_at) {
            abort(404);
        }

        $groupTrainingRequest->load(['members', 'training']);

        return view('trainer.group-requests.show', [
            'groupRequest' => $groupTrainingRequest,
        ]);
    }
}
