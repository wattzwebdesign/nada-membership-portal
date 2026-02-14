<?php

namespace Database\Seeders;

use App\Models\PayoutSetting;
use Illuminate\Database\Seeder;

class GlobalPayoutSettingSeeder extends Seeder
{
    public function run(): void
    {
        PayoutSetting::firstOrCreate(
            ['trainer_id' => null],
            [
                'platform_percentage' => 20.00,
                'trainer_percentage' => 80.00,
                'is_active' => true,
                'notes' => 'Global default payout split',
            ]
        );
    }
}
