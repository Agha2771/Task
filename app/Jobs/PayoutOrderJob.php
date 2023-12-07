<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\ApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use DB;
use RuntimeException;

class PayoutOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param Order $order
     */
    public function __construct(
        public Order $order
    ) {}

    /**
     * Use the API service to send a payout of the correct amount.
     * Note: The order status must be paid if the payout is successful,
     * or remain unpaid in the event of an exception.
     *
     * @param ApiService $apiService
     * @return void
     */
    public function handle(ApiService $apiService)
    {
        // Wrap the entire process in a database transaction
        DB::transaction(function () use ($apiService) {
            try {
                // Simulate sending a payout to the affiliate's email
                $apiService->sendPayout($this->order->affiliate->user->email, $this->order->commission_owed);

                // Update the order status to paid
                $this->order->update(['payout_status' => Order::STATUS_PAID]);

                // Log or perform any other necessary actions for a successful payout
            } catch (RuntimeException $exception) {
                // Log the exception or perform any necessary actions for a failed payout
                // For example, you might want to retry the job or notify administrators

                // Keep the order status as unpaid in case of an exception
                $this->order->update(['payout_status' => Order::STATUS_UNPAID]);

                // Re-throw the exception to ensure it propagates
                throw $exception;
            }
        });
    }
}
