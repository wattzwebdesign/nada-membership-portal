<?php

namespace App\Services;

use App\Models\SiteSetting;

class GroupTrainingFeeService
{
    public function calculateFeeCents(int $subtotalCents): int
    {
        $type = $this->getFeeType();
        $value = (float) $this->getFeeValue();

        if ($value <= 0) {
            return 0;
        }

        if ($type === 'percentage') {
            return (int) round($subtotalCents * $value / 100);
        }

        // Flat fee: stored in dollars, convert to cents
        return (int) round($value * 100);
    }

    public function getFeeType(): string
    {
        return SiteSetting::get('group_training_fee_type', 'flat');
    }

    public function getFeeValue(): string
    {
        return SiteSetting::get('group_training_fee_value', '0');
    }

    public function getFeeDescription(): string
    {
        $type = $this->getFeeType();
        $value = $this->getFeeValue();

        if ((float) $value <= 0) {
            return 'No transaction fee';
        }

        if ($type === 'percentage') {
            return $value . '% transaction fee';
        }

        return '$' . number_format((float) $value, 2) . ' transaction fee';
    }
}
