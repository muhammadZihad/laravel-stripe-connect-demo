<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            // Verification status for ACH/bank accounts
            $table->enum('verification_status', [
                'pending', 
                'verification_required', 
                'pending_verification', 
                'verified', 
                'failed'
            ])->default('pending')->after('is_active');
            
            // Track verification attempts
            $table->integer('verification_attempts')->default(0)->after('verification_status');
            
            // Stripe verification session ID for micro-deposits
            $table->string('stripe_verification_session_id')->nullable()->after('verification_attempts');
            
            // Timestamps for verification process
            $table->timestamp('verification_initiated_at')->nullable()->after('stripe_verification_session_id');
            $table->timestamp('verified_at')->nullable()->after('verification_initiated_at');
            
            // Additional metadata for verification
            $table->json('verification_metadata')->nullable()->after('verified_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropColumn([
                'verification_status',
                'verification_attempts',
                'stripe_verification_session_id',
                'verification_initiated_at',
                'verified_at',
                'verification_metadata'
            ]);
        });
    }
};
