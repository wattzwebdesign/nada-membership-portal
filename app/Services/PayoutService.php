<?php

namespace App\Services;

use App\Models\PayoutSetting;
use App\Models\TrainingRegistration;
use App\Models\User;
use Carbon\Carbon;

class PayoutService
{
    public function calculateSplit(int $amountCents, int $trainerId): array
    {
        $settings = PayoutSetting::getForTrainer($trainerId);

        $platformAmount = (int) round($amountCents * ($settings->platform_percentage / 100));
        $trainerAmount = $amountCents - $platformAmount;

        return [
            'total' => $amountCents,
            'platform_amount' => $platformAmount,
            'trainer_amount' => $trainerAmount,
            'platform_percentage' => $settings->platform_percentage,
            'trainer_percentage' => $settings->trainer_percentage,
        ];
    }

    public function getEarningsReport(User $trainer, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $query = TrainingRegistration::query()
            ->whereHas('training', fn ($q) => $q->where('trainer_id', $trainer->id))
            ->where('amount_paid_cents', '>', 0);

        if ($from) {
            $query->where('created_at', '>=', $from);
        }
        if ($to) {
            $query->where('created_at', '<=', $to);
        }

        $registrations = $query->with('training')->get();
        $settings = PayoutSetting::getForTrainer($trainer->id);

        $totalRevenue = $registrations->sum('amount_paid_cents');
        $platformFees = (int) round($totalRevenue * ($settings->platform_percentage / 100));
        $trainerEarnings = $totalRevenue - $platformFees;

        $perTraining = $registrations->groupBy('training_id')->map(function ($regs) use ($settings) {
            $training = $regs->first()->training;
            $total = $regs->sum('amount_paid_cents');
            $platformFee = (int) round($total * ($settings->platform_percentage / 100));

            return [
                'training_id' => $training->id,
                'training_title' => $training->title,
                'paid_attendees' => $regs->count(),
                'total_revenue' => $total,
                'platform_fee' => $platformFee,
                'trainer_payout' => $total - $platformFee,
            ];
        })->values();

        return [
            'total_revenue' => $totalRevenue,
            'platform_fees' => $platformFees,
            'trainer_earnings' => $trainerEarnings,
            'per_training' => $perTraining,
            'period' => [
                'from' => $from?->toDateString(),
                'to' => $to?->toDateString(),
            ],
        ];
    }
}
