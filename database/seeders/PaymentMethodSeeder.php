<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Agent;
use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all companies
        $companies = Company::with('user')->get();

        foreach ($companies as $company) {
            // Create 2-3 demo payment methods for each company user
            
            // Default card payment method
            PaymentMethod::create([
                'user_id' => $company->user->id,
                'stripe_payment_method_id' => 'pm_demo_' . strtolower(str_replace(' ', '_', $company->company_name)) . '_card_' . rand(1000, 9999),
                'type' => 'card',
                'brand' => 'visa',
                'last_four' => str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT),
                'exp_month' => str_pad(rand(1, 12), 2, '0', STR_PAD_LEFT),
                'exp_year' => (date('Y') + rand(1, 5)),
                'is_default' => true,
                'is_active' => true,
                'verification_status' => 'verified',
                'verified_at' => now(),
                'stripe_metadata' => json_encode([
                    'demo' => true,
                    'company_id' => $company->id,
                    'user_id' => $company->user->id,
                    'created_by' => 'seeder'
                ])
            ]);

            // Secondary card payment method
            PaymentMethod::create([
                'user_id' => $company->user->id,
                'stripe_payment_method_id' => 'pm_demo_' . strtolower(str_replace(' ', '_', $company->company_name)) . '_card2_' . rand(1000, 9999),
                'type' => 'card',
                'brand' => rand(0, 1) ? 'mastercard' : 'amex',
                'last_four' => str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT),
                'exp_month' => str_pad(rand(1, 12), 2, '0', STR_PAD_LEFT),
                'exp_year' => (date('Y') + rand(1, 5)),
                'is_default' => false,
                'is_active' => true,
                'verification_status' => 'verified',
                'verified_at' => now(),
                'stripe_metadata' => json_encode([
                    'demo' => true,
                    'company_id' => $company->id,
                    'user_id' => $company->user->id,
                    'created_by' => 'seeder'
                ])
            ]);

            // Bank account payment method (for some companies)
            if (rand(0, 1)) {
                PaymentMethod::create([
                    'user_id' => $company->user->id,
                    'stripe_payment_method_id' => 'pm_demo_' . strtolower(str_replace(' ', '_', $company->company_name)) . '_bank_' . rand(1000, 9999),
                    'type' => 'us_bank_account',
                    'bank_name' => ['Chase Bank', 'Bank of America', 'Wells Fargo', 'Citibank'][rand(0, 3)],
                    'last_four' => str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT),
                    'account_holder_type' => 'company',
                    'is_default' => false,
                    'is_active' => true,
                    'verification_status' => 'verified', // Set as verified for demo
                    'verified_at' => now(),
                    'stripe_metadata' => json_encode([
                        'demo' => true,
                        'company_id' => $company->id,
                        'user_id' => $company->user->id,
                        'created_by' => 'seeder'
                    ])
                ]);
            }
        }

        // Also add payment methods for agents
        $agents = Agent::with('user')->get();

        foreach ($agents as $agent) {
            // Create 1-2 demo payment methods for each agent user
            
            // Default card payment method for agent
            PaymentMethod::create([
                'user_id' => $agent->user->id,
                'stripe_payment_method_id' => 'pm_demo_agent_' . $agent->agent_code . '_card_' . rand(1000, 9999),
                'type' => 'card',
                'brand' => ['visa', 'mastercard', 'amex'][rand(0, 2)],
                'last_four' => str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT),
                'exp_month' => str_pad(rand(1, 12), 2, '0', STR_PAD_LEFT),
                'exp_year' => (date('Y') + rand(1, 5)),
                'is_default' => true,
                'is_active' => true,
                'verification_status' => 'verified',
                'verified_at' => now(),
                'stripe_metadata' => json_encode([
                    'demo' => true,
                    'agent_id' => $agent->id,
                    'user_id' => $agent->user->id,
                    'created_by' => 'seeder'
                ])
            ]);

            // Some agents get a second payment method
            if (rand(0, 1)) {
                PaymentMethod::create([
                    'user_id' => $agent->user->id,
                    'stripe_payment_method_id' => 'pm_demo_agent_' . $agent->agent_code . '_bank_' . rand(1000, 9999),
                    'type' => 'us_bank_account',
                    'bank_name' => ['Chase Bank', 'Bank of America', 'Wells Fargo'][rand(0, 2)],
                    'last_four' => str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT),
                    'account_holder_type' => 'individual',
                    'is_default' => false,
                    'is_active' => true,
                    'verification_status' => 'verified', // Set as verified for demo
                    'verified_at' => now(),
                    'stripe_metadata' => json_encode([
                        'demo' => true,
                        'agent_id' => $agent->id,
                        'user_id' => $agent->user->id,
                        'created_by' => 'seeder'
                    ])
                ]);
            }
        }

        $this->command->info('Payment methods seeded successfully!');
    }
} 