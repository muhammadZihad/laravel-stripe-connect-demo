<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\Agent;
use App\Models\Invoice;
use Carbon\Carbon;

class InvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = Company::with('agents')->get();

        // Invoice templates with amounts under $30 and 10% application fee > $2
        // Minimum amount is $20.01 to ensure 10% fee is > $2
        $companyToAgentInvoices = [
            [
                'title' => 'Monthly Office Supplies Fee',
                'description' => 'Office supplies and utilities fee charged by company to agent.',
                'amount' => 22.50,
                'invoice_items' => [
                    ['description' => 'Office Supplies', 'amount' => 15.00],
                    ['description' => 'Utilities Share', 'amount' => 7.50],
                ]
            ],
            [
                'title' => 'Training Program Fee',
                'description' => 'Professional development training program fee.',
                'amount' => 25.00,
                'invoice_items' => [
                    ['description' => 'Training Materials', 'amount' => 15.00],
                    ['description' => 'Instructor Fee', 'amount' => 10.00],
                ]
            ],
            [
                'title' => 'License Processing Fee',
                'description' => 'License renewal and processing administrative fee.',
                'amount' => 28.00,
                'invoice_items' => [
                    ['description' => 'License Renewal', 'amount' => 20.00],
                    ['description' => 'Processing Fee', 'amount' => 8.00],
                ]
            ],
            [
                'title' => 'Marketing Materials Fee',
                'description' => 'Business cards, brochures and marketing materials fee.',
                'amount' => 24.50,
                'invoice_items' => [
                    ['description' => 'Business Cards', 'amount' => 12.50],
                    ['description' => 'Brochures', 'amount' => 12.00],
                ]
            ],
        ];

        $agentToCompanyInvoices = [
            [
                'title' => 'Commission Payment',
                'description' => 'Sales commission earned by agent, to be paid by company.',
                'amount' => 26.00,
                'invoice_items' => [
                    ['description' => 'Sales Commission', 'amount' => 20.00],
                    ['description' => 'Performance Bonus', 'amount' => 6.00],
                ]
            ],
            [
                'title' => 'Referral Bonus',
                'description' => 'Client referral bonus earned by agent.',
                'amount' => 23.00,
                'invoice_items' => [
                    ['description' => 'Client Referral', 'amount' => 18.00],
                    ['description' => 'Bonus Incentive', 'amount' => 5.00],
                ]
            ],
            [
                'title' => 'Overtime Compensation',
                'description' => 'Additional hours worked compensation.',
                'amount' => 27.50,
                'invoice_items' => [
                    ['description' => 'Overtime Hours', 'amount' => 22.50],
                    ['description' => 'Weekend Work', 'amount' => 5.00],
                ]
            ],
            [
                'title' => 'Project Completion Bonus',
                'description' => 'Special project completion bonus payment.',
                'amount' => 29.00,
                'invoice_items' => [
                    ['description' => 'Project Bonus', 'amount' => 25.00],
                    ['description' => 'Quality Incentive', 'amount' => 4.00],
                ]
            ],
        ];

        $invoiceCounter = 1;

        foreach ($companies as $company) {
            foreach ($company->agents as $agent) {
                // Create 2-3 company-to-agent invoices (agent pays company)
                $companyInvoiceCount = rand(2, 3);
                for ($i = 0; $i < $companyInvoiceCount; $i++) {
                    $template = $companyToAgentInvoices[array_rand($companyToAgentInvoices)];
                    
                    // Vary amounts slightly but keep under $30 and ensure 10% > $2
                    $baseAmount = $template['amount'];
                    $variation = rand(-3, 3); // ±$3 variation
                    $amount = max(20.01, min(29.99, $baseAmount + $variation)); // Keep between $20.01-$29.99
                    
                    // Calculate 10% application fee (ensure > $2)
                    $applicationFee = round($amount * 0.1, 2);
                    if ($applicationFee <= 2.00) {
                        $amount = 20.01; // Reset to minimum to ensure fee > $2
                        $applicationFee = 2.01;
                    }
                    $totalAmount = $amount + $applicationFee;
                    
                    $createdAt = Carbon::now()->subDays(rand(1, 45));
                    $dueDate = $createdAt->copy()->addDays(rand(15, 30));

                    Invoice::create([
                        'invoice_number' => 'INV-' . date('Ym', $createdAt->timestamp) . '-' . str_pad($invoiceCounter++, 4, '0', STR_PAD_LEFT),
                        'company_id' => $company->id,
                        'agent_id' => $agent->id,
                        'title' => '[Company → Agent] ' . $template['title'],
                        'description' => $template['description'] . ' (Agent pays company)',
                        'amount' => $amount,
                        'tax_amount' => $applicationFee,
                        'total_amount' => $totalAmount,
                        'status' => 'pending',
                        'due_date' => $dueDate,
                        'paid_date' => null,
                        'invoice_items' => $this->adjustInvoiceItems($template['invoice_items'], $amount, $baseAmount),
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);
                }

                // Create 2-3 agent-to-company invoices (company pays agent)
                $agentInvoiceCount = rand(2, 3);
                for ($i = 0; $i < $agentInvoiceCount; $i++) {
                    $template = $agentToCompanyInvoices[array_rand($agentToCompanyInvoices)];
                    
                    // Vary amounts slightly but keep under $30 and ensure 10% > $2
                    $baseAmount = $template['amount'];
                    $variation = rand(-3, 3); // ±$3 variation
                    $amount = max(20.01, min(29.99, $baseAmount + $variation)); // Keep between $20.01-$29.99
                    
                    // Calculate 10% application fee (ensure > $2)
                    $applicationFee = round($amount * 0.1, 2);
                    if ($applicationFee <= 2.00) {
                        $amount = 20.01; // Reset to minimum to ensure fee > $2
                        $applicationFee = 2.01;
                    }
                    $totalAmount = $amount + $applicationFee;
                    
                    $createdAt = Carbon::now()->subDays(rand(1, 45));
                    $dueDate = $createdAt->copy()->addDays(rand(15, 30));

                    Invoice::create([
                        'invoice_number' => 'INV-' . date('Ym', $createdAt->timestamp) . '-' . str_pad($invoiceCounter++, 4, '0', STR_PAD_LEFT),
                        'company_id' => $company->id,
                        'agent_id' => $agent->id,
                        'title' => '[Agent → Company] ' . $template['title'],
                        'description' => $template['description'] . ' (Company pays agent)',
                        'amount' => $amount,
                        'tax_amount' => $applicationFee,
                        'total_amount' => $totalAmount,
                        'status' => 'pending',
                        'due_date' => $dueDate,
                        'paid_date' => null,
                        'invoice_items' => $this->adjustInvoiceItems($template['invoice_items'], $amount, $baseAmount),
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);
                }
            }
        }

        $this->command->info('Bidirectional invoices seeded successfully!');
        $this->command->info('- Invoice amounts: $20.01 - $29.99 (under $30)');
        $this->command->info('- Application fees: 10% of amount (always > $2)');
        $this->command->info('- Created invoices in both directions: Agent→Company and Company→Agent');
    }

    /**
     * Adjust invoice items proportionally to the new amount
     */
    private function adjustInvoiceItems(array $items, float $newAmount, float $originalAmount): array
    {
        $ratio = $newAmount / $originalAmount;
        
        return array_map(function ($item) use ($ratio) {
            return [
                'description' => $item['description'],
                'amount' => round($item['amount'] * $ratio, 2)
            ];
        }, $items);
    }
}
