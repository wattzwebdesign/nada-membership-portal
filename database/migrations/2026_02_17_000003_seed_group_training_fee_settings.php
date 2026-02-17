<?php

use App\Models\SiteSetting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        SiteSetting::set('group_training_fee_type', 'flat');
        SiteSetting::set('group_training_fee_value', '0');
    }

    public function down(): void
    {
        SiteSetting::where('key', 'group_training_fee_type')->delete();
        SiteSetting::where('key', 'group_training_fee_value')->delete();
    }
};
