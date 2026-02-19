<?php

namespace Database\Seeders;

use App\Models\PayoutSetting;
use Illuminate\Database\Seeder;

class GlobalPayoutSettingSeeder extends Seeder
{
    public function run(): void
    {
        PayoutSetting::firstOrCreate(
            ['user_id' => null, 'type' => 'trainer'],
            [
                'platform_percentage' => 20.00,
                'payee_percentage' => 80.00,
                'is_active' => true,
                'notes' => 'Global default payout split',
            ]
        );
    }
}
