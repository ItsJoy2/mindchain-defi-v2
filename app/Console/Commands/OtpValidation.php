<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Transaction;

class OtpValidation extends Command
{
    protected $signature = 'otp:expire';

    protected $description = 'Expire pending OTP transactions after 3 minutes';

    public function handle()
    {
        Transaction::where('method', 'Transfer')
            ->where('status', 'Pending')
            ->where('updated_at', '<=', now()->subMinutes(3))
            ->update([
                'status' => 'Expired',
                'confirmation_code' => null
            ]);

        $this->info('Expired transfer transactions updated successfully.');
    }

}
