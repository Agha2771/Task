<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Mail\PayoutMail;
use App\Models\Merchant;
use Illuminate\Support\Str;
use RuntimeException;
Use Mail;

/**
 * You don't need to do anything here. This is just to help
 */
class ApiService
{
    /**
     * Create a new discount code for an affiliate
     *
     * @param Merchant $merchant
     *
     * @return array{id: int, code: string}
     */
    public function createDiscountCode(Merchant $merchant): array
    {
        return [
            'id' => rand(0, 100000),
            'code' => Str::uuid()
        ];
    }

    /**
     * Send a payout to an email
     *
     * @param  string $email
     * @param  float $amount
     * @return void
     * @throws RuntimeException
     */
    public function sendPayout(string $email, float $amount)
    {
        // Simulate sending a payout
        // You can add your payout logic here
        // For this example, we will assume that the payout is successful
        // If there's an issue, throw a RuntimeException
        if ($email === 'error@example.com') {
            throw new RuntimeException('Payout failed: Invalid email');
        }

        // Log or perform any other necessary actions for a successful payout
    }
    
}
