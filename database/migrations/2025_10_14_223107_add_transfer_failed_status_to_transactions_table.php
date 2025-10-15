<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For SQLite, we need to recreate the table with the new enum values
        if (DB::getDriverName() === 'sqlite') {
            // Create a temporary table with the new structure
            Schema::create('transactions_temp', function (Blueprint $table) {
                $table->id();
                $table->string('transaction_id')->unique();
                $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
                $table->foreignId('company_id')->constrained()->onDelete('cascade');
                $table->foreignId('agent_id')->constrained()->onDelete('cascade');
                $table->decimal('amount', 10, 2);
                $table->decimal('admin_commission', 10, 2)->default(2.00);
                $table->decimal('net_amount', 10, 2);
                $table->enum('type', ['payment', 'refund', 'commission'])->default('payment');
                $table->enum('status', ['pending', 'completed', 'failed', 'cancelled', 'transfer_failed'])->default('pending');
                $table->string('stripe_payment_intent_id')->nullable();
                $table->string('stripe_transfer_id')->nullable();
                $table->string('payment_method_type')->nullable();
                $table->json('stripe_metadata')->nullable();
                $table->text('notes')->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();
            });

            // Copy data from old table to new table
            DB::statement('INSERT INTO transactions_temp SELECT * FROM transactions');

            // Drop old table and rename new table
            Schema::drop('transactions');
            Schema::rename('transactions_temp', 'transactions');
            return;
        }
        
        // For MySQL/PostgreSQL
        DB::statement("ALTER TABLE transactions MODIFY COLUMN status ENUM('pending', 'completed', 'failed', 'cancelled', 'transfer_failed') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }
        
        // Remove 'transfer_failed' from the enum
        DB::statement("ALTER TABLE transactions MODIFY COLUMN status ENUM('pending', 'completed', 'failed', 'cancelled') DEFAULT 'pending'");
    }
};