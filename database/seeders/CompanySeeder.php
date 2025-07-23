<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Company;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = [
            [
                'email' => 'john@techcorp.com',
                'company_data' => [
                    'company_name' => 'TechCorp Solutions',
                    'business_type' => 'Technology',
                    'address' => '123 Tech Street, San Francisco, CA 94105',
                    'phone' => '+1 (555) 123-4567',
                    'website' => 'https://techcorp.com',
                    'tax_id' => '12-3456789',
                    'is_active' => true,
                ]
            ],
            [
                'email' => 'sarah@marketingpro.com',
                'company_data' => [
                    'company_name' => 'MarketingPro Agency',
                    'business_type' => 'Marketing & Advertising',
                    'address' => '456 Marketing Ave, New York, NY 10001',
                    'phone' => '+1 (555) 234-5678',
                    'website' => 'https://marketingpro.com',
                    'tax_id' => '23-4567890',
                    'is_active' => true,
                ]
            ],
            [
                'email' => 'michael@consultgroup.com',
                'company_data' => [
                    'company_name' => 'Consultant Group LLC',
                    'business_type' => 'Business Consulting',
                    'address' => '789 Business Blvd, Chicago, IL 60601',
                    'phone' => '+1 (555) 345-6789',
                    'website' => 'https://consultgroup.com',
                    'tax_id' => '34-5678901',
                    'is_active' => true,
                ]
            ],
        ];

        foreach ($companies as $companyInfo) {
            $user = User::where('email', $companyInfo['email'])->first();
            if ($user) {
                Company::create(array_merge(
                    $companyInfo['company_data'],
                    ['user_id' => $user->id]
                ));
            }
        }
    }
}
