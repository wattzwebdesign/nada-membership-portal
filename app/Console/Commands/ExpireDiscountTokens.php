<?php

namespace App\Console\Commands;

use App\Models\DiscountRequest;
use Illuminate\Console\Command;

class ExpireDiscountTokens extends Command
{
    protected $signature = 'nada:expire-discount-tokens';
    protected $description = 'Expire discount approval tokens older than 30 days';

    public function handle(): int
    {
        $expired = DiscountRequest::where('status', 'pending')
            ->whereNotNull('approval_token')
            ->where('token_expires_at', '<', now())
            ->update([
                'approval_token' => null,
                'token_expires_at' => null,
            ]);

        $this->info("Expired {$expired} discount tokens");
        return Command::SUCCESS;
    }
}
