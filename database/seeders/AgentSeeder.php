<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Company;
use App\Models\Agent;

class AgentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $agents = [
            // Consultant Group LLC Agents
            [
                'email' => 'emma@consultgroup.com',
                'company_email' => 'michael@consultgroup.com',
                'agent_data' => [
                    'commission_rate' => 25.00,
                    'department' => 'Strategy Consulting',
                    'hire_date' => '2024-01-05',
                    'is_active' => true,
                ]
            ],
            [
                'email' => 'frank@consultgroup.com',
                'company_email' => 'michael@consultgroup.com',
                'agent_data' => [
                    'commission_rate' => 22.00,
                    'department' => 'Financial Consulting',
                    'hire_date' => '2024-01-25',
                    'is_active' => true,
                ]
            ],
            
            // My Broker Cloud Agents
            [
                'email' => 'zihad@blubirdinteractive.com',
                'company_email' => 'support@mybrokercloud.com',
                'agent_data' => [
                    'commission_rate' => 20.00,
                    'department' => 'Real Estate Sales',
                    'hire_date' => '2024-02-01',
                    'is_active' => true,
                ]
            ],
            [
                'email' => 'Richard@mybrokercloud.com',
                'company_email' => 'support@mybrokercloud.com',
                'agent_data' => [
                    'commission_rate' => 18.00,
                    'department' => 'Property Management',
                    'hire_date' => '2024-02-15',
                    'is_active' => true,
                ]
            ],
        ];

        foreach ($agents as $agentInfo) {
            $user = User::where('email', $agentInfo['email'])->first();
            $companyUser = User::where('email', $agentInfo['company_email'])->first();
            
            if ($user && $companyUser) {
                $company = Company::where('user_id', $companyUser->id)->first();
                
                if ($company) {
                    Agent::create(array_merge(
                        $agentInfo['agent_data'],
                        [
                            'user_id' => $user->id,
                            'company_id' => $company->id,
                            'agent_code' => Agent::generateAgentCode($company),
                        ]
                    ));
                }
            }
        }
    }
}
