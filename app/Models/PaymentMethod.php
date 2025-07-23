<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'stripe_payment_method_id',
        'type',
        'brand',
        'last_four',
        'exp_month',
        'exp_year',
        'bank_name',
        'account_holder_type',
        'is_default',
        'is_active',
        'verification_status',
        'verification_attempts',
        'stripe_verification_session_id',
        'verification_initiated_at',
        'verified_at',
        'verification_metadata',
        'stripe_metadata',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'verification_attempts' => 'integer',
        'verification_initiated_at' => 'datetime',
        'verified_at' => 'datetime',
        'verification_metadata' => 'array',
        'stripe_metadata' => 'array',
    ];

    /**
     * Get the user that owns this payment method
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if this is the default payment method
     */
    public function isDefault(): bool
    {
        return $this->is_default;
    }

    /**
     * Check if payment method is active
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Set as default payment method
     */
    public function setAsDefault(): void
    {
        // First, remove default status from all other payment methods for this user
        $this->user->paymentMethods()->where('id', '!=', $this->id)->update(['is_default' => false]);
        
        // Then set this one as default
        $this->update(['is_default' => true]);
    }

    /**
     * Get display name for payment method
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->type === 'card') {
            return ucfirst($this->brand) . ' ending in ' . $this->last_four;
        } elseif ($this->type === 'bank_account') {
            return $this->bank_name . ' ending in ' . $this->last_four;
        }
        
        return ucfirst($this->type);
    }

    /**
     * Check if payment method is a card
     */
    public function isCard(): bool
    {
        return $this->type === 'card';
    }

    /**
     * Check if payment method is a bank account
     */
    public function isBankAccount(): bool
    {
        return $this->type === 'bank_account';
    }

    /**
     * Check if payment method requires verification
     */
    public function requiresVerification(): bool
    {
        return $this->verification_status === 'verification_required';
    }

    /**
     * Check if payment method is pending verification
     */
    public function isPendingVerification(): bool
    {
        return $this->verification_status === 'pending_verification';
    }

    /**
     * Check if payment method is verified
     */
    public function isVerified(): bool
    {
        return $this->verification_status === 'verified';
    }

    /**
     * Check if verification has failed
     */
    public function verificationFailed(): bool
    {
        return $this->verification_status === 'failed';
    }

    /**
     * Check if can attempt verification
     */
    public function canAttemptVerification(): bool
    {
        return $this->type === 'us_bank_account' 
            && $this->verification_attempts < 3 
            && !$this->isVerified();
    }

    /**
     * Get verification status badge info
     */
    public function getVerificationBadgeAttribute(): array
    {
        return match($this->verification_status) {
            'verified' => [
                'text' => 'Verified',
                'class' => 'bg-green-100 text-green-800',
                'icon' => 'check-circle'
            ],
            'verification_required' => [
                'text' => 'Verification Required',
                'class' => 'bg-yellow-100 text-yellow-800',
                'icon' => 'exclamation-triangle'
            ],
            'pending_verification' => [
                'text' => 'Pending Verification',
                'class' => 'bg-blue-100 text-blue-800',
                'icon' => 'clock'
            ],
            'failed' => [
                'text' => 'Verification Failed',
                'class' => 'bg-red-100 text-red-800',
                'icon' => 'x-circle'
            ],
            default => [
                'text' => 'Pending',
                'class' => 'bg-gray-100 text-gray-800',
                'icon' => 'minus-circle'
            ]
        };
    }
}
