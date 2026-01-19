<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'invoice_id',
        'company_id',
        'agent_id',
        'amount',
        'admin_commission',
        'net_amount',
        'type',
        'status',
        'stripe_payment_intent_id',
        'stripe_transfer_id',
        'payment_method_type',
        'stripe_metadata',
        'notes',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'admin_commission' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'stripe_metadata' => 'array',
            'processed_at' => 'datetime',
        ];
    }

    /**
     * Get the invoice this transaction belongs to
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the company this transaction belongs to
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the agent this transaction is for
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    /**
     * Check if transaction is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if transaction is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if transaction failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Generate unique transaction ID
     */
    public static function generateTransactionId(): string
    {
        return 'TXN-' . strtoupper(Str::random(8)) . '-' . time();
    }

    /**
     * Calculate net amount after commission
     */
    public function calculateNetAmount(): void
    {
        $netAmount = (float)$this->amount - (float)$this->admin_commission;
        $this->net_amount = number_format($netAmount, 2, '.', '');
        $this->save();
    }

    /**
     * Mark transaction as completed
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'processed_at' => now(),
        ]);
    }

    /**
     * Mark transaction as failed
     */
    public function markAsFailed(string $reason = null): void
    {
        $this->update([
            'status' => 'failed',
            'notes' => $reason,
            'processed_at' => now(),
        ]);
    }

    /**
     * Check if transfer failed
     */
    public function isTransferFailed(): bool
    {
        return $this->status === 'transfer_failed';
    }

    /**
     * Mark transaction as transfer failed
     */
    public function markAsTransferFailed(string $reason = null): void
    {
        $this->update([
            'status' => 'transfer_failed',
            'notes' => $reason,
            'processed_at' => now(),
        ]);
    }

    /**
     * Check if transaction is awaiting 3DS authentication
     */
    public function isAwaitingAuthentication(): bool
    {
        return $this->isPending() && 
               $this->stripe_payment_intent_id && 
               !$this->stripe_transfer_id;
    }

    /**
     * Get human-readable status with details
     */
    public function getStatusWithDetails(): string
    {
        if ($this->isPending() && $this->isAwaitingAuthentication()) {
            return 'Pending - Awaiting 3D Secure Authentication';
        }
        
        return ucfirst($this->status);
    }

    /**
     * Check if transaction is old enough to reconcile
     */
    public function shouldReconcile(int $minutesOld = 10): bool
    {
        return $this->isPending() && 
               $this->created_at->diffInMinutes(now()) > $minutesOld;
    }
}
