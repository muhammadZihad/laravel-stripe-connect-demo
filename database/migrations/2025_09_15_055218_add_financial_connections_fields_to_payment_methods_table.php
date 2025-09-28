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
            // Add verification method type
            $table->enum('verification_method', [
                'microdeposits',
                'instant', 
                'automatic'
            ])->default('automatic')->after('verification_metadata');
            
            // Financial Connections session ID for instant verification
            $table->string('financial_connections_session_id')->nullable()->after('verification_method');
            
            // Store Financial Connections account ID if available
            $table->string('financial_connections_account_id')->nullable()->after('financial_connections_session_id');
            
            // Track which verification method was actually used
            $table->enum('verification_method_used', [
                'microdeposits',
                'instant'
            ])->nullable()->after('financial_connections_account_id');
            
            // Store additional Financial Connections metadata
            $table->json('financial_connections_metadata')->nullable()->after('verification_method_used');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropColumn([
                'verification_method',
                'financial_connections_session_id',
                'financial_connections_account_id',
                'verification_method_used',
                'financial_connections_metadata'
            ]);
        });
    }
};