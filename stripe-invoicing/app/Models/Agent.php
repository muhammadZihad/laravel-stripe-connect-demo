<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Agent extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_id',
        'agent_code',
        'commission_rate',
        'department',
        'hire_date',
        'stripe_id',
        'stripe_connect_account_id',
        'stripe_onboarding_complete',
        'stripe_capabilities',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'commission_rate' => 'decimal:2',
            'hire_date' => 'date',
            'stripe_onboarding_complete' => 'boolean',
            'stripe_capabilities' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the user that owns this agent profile
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the company this agent belongs to
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get all invoices for this agent
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get all transactions for this agent
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get all payment methods for this agent (polymorphic)
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

    /**
     * Generate unique agent code
     */
    public static function generateAgentCode(Company $company): string
    {
        $prefix = strtoupper(substr($company->company_name, 0, 3));
        $count = $company->agents()->count() + 1;
        return $prefix . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
