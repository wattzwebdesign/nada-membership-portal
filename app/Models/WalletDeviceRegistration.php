<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletDeviceRegistration extends Model
{
    protected $fillable = [
        'wallet_pass_id',
        'device_library_identifier',
        'push_token',
    ];

    public function walletPass(): BelongsTo
    {
        return $this->belongsTo(WalletPass::class);
    }
}
