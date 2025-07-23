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
        // Step 1: Add Stripe fields to users table (if they don't exist)
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'stripe_id')) {
                $table->string('stripe_id')->nullable()->after('trial_ends_at');
            }
            if (!Schema::hasColumn('users', 'stripe_connect_account_id')) {
                $table->string('stripe_connect_account_id')->nullable()->after('stripe_id');
            }
            if (!Schema::hasColumn('users', 'stripe_onboarding_complete')) {
                $table->boolean('stripe_onboarding_complete')->default(false)->after('stripe_connect_account_id');
            }
            if (!Schema::hasColumn('users', 'stripe_capabilities')) {
                $table->json('stripe_capabilities')->nullable()->after('stripe_onboarding_complete');
            }
        });

        // Step 2: Migrate existing Stripe data from agents and companies to users FIRST
        $this->migrateStripeDataToUsers();

        // Step 3: Add user_id to payment_methods table (if it doesn't exist)
        if (!Schema::hasColumn('payment_methods', 'user_id')) {
            Schema::table('payment_methods', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // Step 4: Migrate existing payment method data from polymorphic to user-based
        $this->migrateExistingPaymentMethods();

        // Step 5: Clean up any payment methods without user_id (orphaned records)
        DB::table('payment_methods')->whereNull('user_id')->delete();

        // Step 6: NOW make user_id required in payment_methods (after all have been migrated)
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });

        // Step 7: Remove polymorphic columns from payment_methods (if they exist)
        if (Schema::hasColumn('payment_methods', 'payable_type')) {
            Schema::table('payment_methods', function (Blueprint $table) {
                // Drop the polymorphic index if it exists
                try {
                    $table->dropIndex(['payable_type', 'payable_id']);
                } catch (\Exception $e) {
                    // Index might not exist, that's okay
                }
                // Drop the columns
                $table->dropColumn(['payable_id', 'payable_type']);
            });
        }

        // Step 8: Remove Stripe columns from agents table (if they exist)
        Schema::table('agents', function (Blueprint $table) {
            if (Schema::hasColumn('agents', 'stripe_id')) {
                $table->dropColumn(['stripe_id']);
            }
            if (Schema::hasColumn('agents', 'stripe_connect_account_id')) {
                $table->dropColumn(['stripe_connect_account_id']);
            }
            if (Schema::hasColumn('agents', 'stripe_onboarding_complete')) {
                $table->dropColumn(['stripe_onboarding_complete']);
            }
            if (Schema::hasColumn('agents', 'stripe_capabilities')) {
                $table->dropColumn(['stripe_capabilities']);
            }
        });

        // Step 9: Remove Stripe columns from companies table (if they exist)
        Schema::table('companies', function (Blueprint $table) {
            if (Schema::hasColumn('companies', 'stripe_id')) {
                $table->dropColumn(['stripe_id']);
            }
            if (Schema::hasColumn('companies', 'stripe_connect_account_id')) {
                $table->dropColumn(['stripe_connect_account_id']);
            }
            if (Schema::hasColumn('companies', 'stripe_onboarding_complete')) {
                $table->dropColumn(['stripe_onboarding_complete']);
            }
            if (Schema::hasColumn('companies', 'stripe_capabilities')) {
                $table->dropColumn(['stripe_capabilities']);
            }
        });
    }

    /**
     * Migrate Stripe data from agents and companies to users
     */
    private function migrateStripeDataToUsers(): void
    {
        // Migrate from agents (if stripe columns exist)
        if (Schema::hasColumn('agents', 'stripe_connect_account_id')) {
            $agents = DB::table('agents')
                ->whereNotNull('stripe_connect_account_id')
                ->orWhereNotNull('stripe_id')
                ->get();

            foreach ($agents as $agent) {
                $updateData = [];
                
                if (!empty($agent->stripe_connect_account_id)) {
                    $updateData['stripe_connect_account_id'] = $agent->stripe_connect_account_id;
                }
                
                if (!empty($agent->stripe_onboarding_complete)) {
                    $updateData['stripe_onboarding_complete'] = $agent->stripe_onboarding_complete;
                }
                
                if (!empty($agent->stripe_capabilities)) {
                    $updateData['stripe_capabilities'] = $agent->stripe_capabilities;
                }
                
                if (!empty($agent->stripe_id)) {
                    $updateData['stripe_id'] = $agent->stripe_id;
                }

                if (!empty($updateData)) {
                    DB::table('users')
                        ->where('id', $agent->user_id)
                        ->update($updateData);
                }
            }
        }

        // Migrate from companies (if stripe columns exist)
        if (Schema::hasColumn('companies', 'stripe_connect_account_id')) {
            $companies = DB::table('companies')
                ->whereNotNull('stripe_connect_account_id')
                ->orWhereNotNull('stripe_id')
                ->get();

            foreach ($companies as $company) {
                $updateData = [];
                
                if (!empty($company->stripe_connect_account_id)) {
                    $updateData['stripe_connect_account_id'] = $company->stripe_connect_account_id;
                }
                
                if (!empty($company->stripe_onboarding_complete)) {
                    $updateData['stripe_onboarding_complete'] = $company->stripe_onboarding_complete;
                }
                
                if (!empty($company->stripe_capabilities)) {
                    $updateData['stripe_capabilities'] = $company->stripe_capabilities;
                }
                
                if (!empty($company->stripe_id)) {
                    $updateData['stripe_id'] = $company->stripe_id;
                }

                if (!empty($updateData)) {
                    DB::table('users')
                        ->where('id', $company->user_id)
                        ->update($updateData);
                }
            }
        }
    }

    /**
     * Migrate existing payment methods from polymorphic to user-based
     */
    private function migrateExistingPaymentMethods(): void
    {
        // Only migrate if polymorphic columns exist
        if (!Schema::hasColumn('payment_methods', 'payable_type')) {
            return;
        }

        // Get all existing payment methods
        $paymentMethods = DB::table('payment_methods')->get();

        foreach ($paymentMethods as $paymentMethod) {
            $userId = null;

            // Find the user based on payable_type and payable_id
            if ($paymentMethod->payable_type === 'App\\Models\\Agent') {
                $agent = DB::table('agents')->where('id', $paymentMethod->payable_id)->first();
                if ($agent) {
                    $userId = $agent->user_id;
                }
            } elseif ($paymentMethod->payable_type === 'App\\Models\\Company') {
                $company = DB::table('companies')->where('id', $paymentMethod->payable_id)->first();
                if ($company) {
                    $userId = $company->user_id;
                }
            }

            // Update the payment method with user_id
            if ($userId) {
                DB::table('payment_methods')
                    ->where('id', $paymentMethod->id)
                    ->update(['user_id' => $userId]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add back Stripe columns to agents table
        Schema::table('agents', function (Blueprint $table) {
            $table->string('stripe_id')->nullable();
            $table->string('stripe_connect_account_id')->nullable();
            $table->boolean('stripe_onboarding_complete')->default(false);
            $table->json('stripe_capabilities')->nullable();
        });

        // Add back Stripe columns to companies table
        Schema::table('companies', function (Blueprint $table) {
            $table->string('stripe_id')->nullable();
            $table->string('stripe_connect_account_id')->nullable();
            $table->boolean('stripe_onboarding_complete')->default(false);
            $table->json('stripe_capabilities')->nullable();
        });

        // Add back polymorphic columns to payment_methods
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->morphs('payable');
        });

        // Migrate data back (reverse migration)
        $this->reverseDataMigration();

        // Remove user_id column from payment_methods
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        // Remove Stripe columns from users table
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'stripe_capabilities')) {
                $table->dropColumn(['stripe_capabilities']);
            }
            if (Schema::hasColumn('users', 'stripe_onboarding_complete')) {
                $table->dropColumn(['stripe_onboarding_complete']);
            }
            if (Schema::hasColumn('users', 'stripe_connect_account_id')) {
                $table->dropColumn(['stripe_connect_account_id']);
            }
            if (Schema::hasColumn('users', 'stripe_id')) {
                $table->dropColumn(['stripe_id']);
            }
        });
    }

    /**
     * Reverse the data migration
     */
    private function reverseDataMigration(): void
    {
        // Migrate payment methods back to polymorphic
        $paymentMethods = DB::table('payment_methods')->get();
        
        foreach ($paymentMethods as $paymentMethod) {
            if (!$paymentMethod->user_id) continue;
            
            $user = DB::table('users')->where('id', $paymentMethod->user_id)->first();
            if (!$user) continue;

            // Determine if this user is an agent or company
            $agent = DB::table('agents')->where('user_id', $user->id)->first();
            $company = DB::table('companies')->where('user_id', $user->id)->first();

            if ($agent) {
                DB::table('payment_methods')
                    ->where('id', $paymentMethod->id)
                    ->update([
                        'payable_id' => $agent->id,
                        'payable_type' => 'App\\Models\\Agent'
                    ]);
                    
                // Migrate Stripe data back to agent
                DB::table('agents')
                    ->where('id', $agent->id)
                    ->update([
                        'stripe_id' => $user->stripe_id,
                        'stripe_connect_account_id' => $user->stripe_connect_account_id,
                        'stripe_onboarding_complete' => $user->stripe_onboarding_complete,
                        'stripe_capabilities' => $user->stripe_capabilities,
                    ]);
            } elseif ($company) {
                DB::table('payment_methods')
                    ->where('id', $paymentMethod->id)
                    ->update([
                        'payable_id' => $company->id,
                        'payable_type' => 'App\\Models\\Company'
                    ]);
                    
                // Migrate Stripe data back to company
                DB::table('companies')
                    ->where('id', $company->id)
                    ->update([
                        'stripe_id' => $user->stripe_id,
                        'stripe_connect_account_id' => $user->stripe_connect_account_id,
                        'stripe_onboarding_complete' => $user->stripe_onboarding_complete,
                        'stripe_capabilities' => $user->stripe_capabilities,
                    ]);
            }
        }
    }
};
