<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Transaction;

class OtpValidation extends Command
{
    protected $signature = 'otp:expire';

    protected $description = 'Expire pending OTP transactions after 7 minutes';

    public function handle()
    {
        Transaction::where('method', 'User Transfer')
            ->where('status', 'Pending')
            ->where('updated_at', '<=', now()->subMinutes(7))
            ->update([
                'status' => 'Expired',
                'confirmation_code' => null
            ]);

        $this->info('Expired transfer transactions updated successfully.');
    }
    
}
