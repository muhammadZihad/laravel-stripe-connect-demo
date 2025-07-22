<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_name',
        'business_type',
        'address',
        'phone',
        'website',
        'tax_id',
        'stripe_id',
        'stripe_connect_account_id',
        'stripe_onboarding_complete',
        'stripe_capabilities',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'stripe_onboarding_complete' => 'boolean',
            'stripe_capabilities' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the user that owns this company
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all agents for this company
     */
    public function agents(): HasMany
    {
        return $this->hasMany(Agent::class);
    }

    /**
     * Get all invoices for this company
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get all transactions for this company
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get all payment methods for this company (polymorphic)
     */
    public function paymentMethods(): MorphMany
    {
        return $this->morphMany(PaymentMethod::class, 'payable');
    }

    /**
     * Get the default payment method
     */
    public function defaultPaymentMethod()
    {
        return $this->paymentMethods()->where('is_default', true)->first();
    }

    /**
     * Check if Stripe Connect onboarding is complete
     */
    public function isStripeOnboardingComplete(): bool
    {
        return $this->stripe_onboarding_complete && !empty($this->stripe_connect_account_id);
    }
}
