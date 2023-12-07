<?php

namespace App\Services;

use App\Jobs\PayoutOrderJob;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class MerchantService
{
    /**
     * Register a new user and associated merchant.
     * Hint: Use the password field to store the API key.
     * Hint: Be sure to set the correct user type according to the constants in the User model.
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return Merchant
     */
    public function register(array $data): Merchant
    {
        // Create a new user with merchant type and API key as password
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['api_key'], // No need to use bcrypt for API key
            'type' => User::TYPE_MERCHANT,
        ]);
    
        // Create a new merchant associated with the user

        $merchant = Merchant::create([
            'user_id' => $user->id,
            'domain' => $data['domain'],
            'display_name' => $data['name'],
            // Add other merchant fields as needed
        ]);
    
        return $merchant;
    }

    /**
     * Update the user
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return void
     */
    public function updateMerchant(User $user, array $data)
    {
        // Update the user details
        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['api_key']), // Update the password (API key)
        ]);

        // Update the associated merchant details
        $user->merchant->update([
            'domain' => $data['domain'],
            'display_name' => $data['name'],
            // You can update other fields as needed
        ]);
    }

    /**
     * Find a merchant by their email.
     * Hint: You'll need to look up the user first.
     *
     * @param string $email
     * @return Merchant|null
     */
    public function findMerchantByEmail(string $email): ?Merchant
    {
        // Find the user by email
        $user = User::where('email', $email)->first();

        // If the user is found, check if the user is a merchant and return the associated merchant
        if ($user && $user->type === User::TYPE_MERCHANT) {
            return $user->merchant;
        }

        // Return null if the user is not found or is not a merchant
        return null;
    }

    /**
     * Pay out all of an affiliate's orders.
     * Hint: You'll need to dispatch the job for each unpaid order.
     *
     * @param Affiliate $affiliate
     * @return void
     */
    public function payout(Affiliate $affiliate)
    {
        // Get the affiliate's unpaid orders
        $unpaidOrders = $affiliate->orders()->where('payout_status', Order::STATUS_UNPAID)->get();

        // Loop through each unpaid order and dispatch a job for processing payouts
        foreach ($unpaidOrders as $order) {
            // Check if the order has already been marked as paid
            if ($order->payout_status != Order::STATUS_UNPAID) {
                continue;
            }

            // Dispatch the job with the necessary data (e.g., order ID)
            PayoutOrderJob::dispatch($order);

            // Optionally, you can update the order to mark it as being processed for payout
            // This depends on your application logic and whether you want to prevent double payouts
            // For now, let's leave the order status as it is, but you may modify this part based on your requirements.
        }
    }
}
