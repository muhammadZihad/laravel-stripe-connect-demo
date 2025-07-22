<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'stripe_connect_account_id',
        'stripe_onboarding_complete',
        'stripe_capabilities',
        'stripe_id',
        'pm_type',
        'pm_last_four',
        'trial_ends_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'stripe_onboarding_complete' => 'boolean',
            'stripe_capabilities' => 'array',
        ];
    }

    /**
     * Check if user is super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * Check if user is company
     */
    public function isCompany(): bool
    {
        return $this->role === 'company';
    }

    /**
     * Check if user is agent
     */
    public function isAgent(): bool
    {
        return $this->role === 'agent';
    }

    /**
     * Get the company profile for this user
     */
    public function company(): HasOne
    {
        return $this->hasOne(Company::class);
    }

    /**
     * Get the agent profile for this user
     */
    public function agent(): HasOne
    {
        return $this->hasOne(Agent::class);
    }

    public function paymentMethods(): MorphMany
    {
        return $this->morphMany(PaymentMethod::class, 'payable');
    }
}
