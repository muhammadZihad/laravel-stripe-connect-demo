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
        $companyToAgentInvoices = array_fill(0, 10, [
                'title' => 'Monthly Office Supplies Fee',
                'description' => 'Office supplies and utilities fee charged by company to agent.',
                'amount' => 300,
                'invoice_items' => [
                    ['description' => 'Office Supplies', 'amount' => 200],
                    ['description' => 'Utilities Share', 'amount' => 100],
                ]
            ]);



        $invoiceCounter = 1;

        foreach ($companies as $company) {
            foreach ($company->agents as $agent) {
                // Create 2-3 company-to-agent invoices (agent pays company)
                $companyInvoiceCount = 15;
                for ($i = 0; $i < $companyInvoiceCount; $i++) {
                    $template = $companyToAgentInvoices[array_rand($companyToAgentInvoices)];
                    
                    // Vary amounts slightly but keep under $30 and ensure 10% > $2
                    $baseAmount = $template['amount'];
                    $variation = rand(-50, 50); // ±$3 variation
                    $amount = max(310, min(290, $baseAmount + $variation)); // Keep between $350-$350
                    
                    // Calculate 10% application fee (ensure > $2)
                    $applicationFee = round($amount * 0.1, 2);

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
