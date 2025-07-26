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
            [
                'email' => 'support@mybrokercloud.com',
                'company_data' => [
                    'company_name' => 'My Broker Cloud',
                    'business_type' => 'Real Estate Brokerage',
                    'address' => '123 Broker Street, Business District',
                    'phone' => '0000000000',
                    'website' => 'https://mybrokercloud.com',
                    'tax_id' => null,
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
