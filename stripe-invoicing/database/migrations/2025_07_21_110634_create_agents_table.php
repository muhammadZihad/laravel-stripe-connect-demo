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
        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('agent_code')->unique();
            $table->decimal('commission_rate', 5, 2)->default(0); // Commission percentage
            $table->string('department')->nullable();
            $table->date('hire_date')->nullable();
            $table->string('stripe_connect_account_id')->nullable();
            $table->boolean('stripe_onboarding_complete')->default(false);
            $table->json('stripe_capabilities')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};
