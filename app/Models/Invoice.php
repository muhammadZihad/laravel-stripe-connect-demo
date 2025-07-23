<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'company_id',
        'agent_id',
        'title',
        'description',
        'amount',
        'tax_amount',
        'total_amount',
        'status',
        'due_date',
        'paid_date',
        'stripe_payment_intent_id',
        'invoice_items',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'due_date' => 'date',
            'paid_date' => 'date',
            'invoice_items' => 'array',
        ];
    }

    /**
     * Get the company this invoice belongs to
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the agent this invoice is for
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    /**
     * Get all transactions for this invoice
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Check if invoice is paid
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Check if invoice is overdue
     */
    public function isOverdue(): bool
    {
        return $this->status !== 'paid' && $this->due_date < now();
    }

    /**
     * Generate unique invoice number
     */
    public static function generateInvoiceNumber(): string
    {
        $prefix = 'INV';
        $year = date('Y');
        $month = date('m');
        $count = self::whereYear('created_at', $year)->whereMonth('created_at', $month)->count() + 1;
        return $prefix . '-' . $year . $month . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Mark invoice as paid
     */
    public function markAsPaid(): void
    {
        $this->update([
            'status' => 'paid',
            'paid_date' => now(),
        ]);
    }

    /**
     * Calculate total including tax
     */
    public function calculateTotal(): void
    {
        $this->total_amount = $this->amount + $this->tax_amount;
        $this->save();
    }
}
