<?php
namespace App\Services;

use App\Exceptions\AffiliateCreateException;
use App\Mail\AffiliateCreated;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use RuntimeException;

class AffiliateService
{
    protected ApiService $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Create a new affiliate for the merchant with the given commission rate.
     *
     * @param  Merchant $merchant
     * @param  string $email
     * @param  string $name
     * @param  float $commissionRate
     * @return Affiliate
     * @throws AffiliateCreateException
     */
    public function register(Merchant $merchant, string $email, string $name, float $commissionRate): Affiliate
    {
        // Check if a user with the same email exists
        $existingUser = User::where('email', $email)->first();
    
        if ($existingUser) {
            // User exists, check its type
            if ($existingUser->type === User::TYPE_MERCHANT) {
                // Existing user is a merchant, handle it accordingly
                // For example, throw an exception or log a message
                throw new AffiliateCreateException('User with the same email is already registered as a merchant.');
            }
    
            // Existing user is not a merchant, proceed to create an affiliate
        }
    
        // Generate a discount code using the ApiService
        $discountCodeData = $this->apiService->createDiscountCode($merchant);
    
        // Create a new user (affiliate)
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt(Str::random(16)), // You may set a random password for the affiliate
            'type' => User::TYPE_AFFILIATE,
        ]);
    
        // Create a new affiliate associated with the user and merchant
        $affiliate = Affiliate::create([
            'user_id' => $user->id,
            'merchant_id' => $merchant->id,
            'commission_rate' => $commissionRate,
            'discount_code' => $discountCodeData['code'],
        ]);
    
        // Send an email with the discount code to the affiliate
        $this->sendAffiliateCreatedEmail($affiliate);
    
        return $affiliate;
    }
     

    /**
     * Send an email to the affiliate with the created discount code.
     *
     * @param  Affiliate $affiliate
     * @return void
     */
    protected function sendAffiliateCreatedEmail(Affiliate $affiliate)
    {
        $email = $affiliate->user->email;
        // You may customize the email content or view based on your requirements
        Mail::to($email)->send(new AffiliateCreated($affiliate));
    }
}
