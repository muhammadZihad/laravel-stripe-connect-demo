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

        $invoiceTemplates = [
            [
                'title' => 'Website Development Project',
                'description' => 'Full-stack web application development including frontend, backend, and database design.',
                'amount' => 5000.00,
                'tax_amount' => 500.00,
                'invoice_items' => [
                    ['description' => 'Frontend Development', 'amount' => 2000.00],
                    ['description' => 'Backend Development', 'amount' => 2500.00],
                    ['description' => 'Database Design', 'amount' => 500.00],
                ]
            ],
            [
                'title' => 'Digital Marketing Campaign',
                'description' => 'Complete digital marketing strategy including SEO, social media, and content marketing.',
                'amount' => 3000.00,
                'tax_amount' => 300.00,
                'invoice_items' => [
                    ['description' => 'SEO Optimization', 'amount' => 1200.00],
                    ['description' => 'Social Media Management', 'amount' => 1000.00],
                    ['description' => 'Content Creation', 'amount' => 800.00],
                ]
            ],
            [
                'title' => 'Business Strategy Consultation',
                'description' => 'Strategic planning and business process optimization consultation.',
                'amount' => 4500.00,
                'tax_amount' => 450.00,
                'invoice_items' => [
                    ['description' => 'Strategy Development', 'amount' => 2500.00],
                    ['description' => 'Process Analysis', 'amount' => 1500.00],
                    ['description' => 'Implementation Plan', 'amount' => 500.00],
                ]
            ],
            [
                'title' => 'Mobile App Development',
                'description' => 'Cross-platform mobile application development for iOS and Android.',
                'amount' => 8000.00,
                'tax_amount' => 800.00,
                'invoice_items' => [
                    ['description' => 'iOS Development', 'amount' => 4000.00],
                    ['description' => 'Android Development', 'amount' => 3500.00],
                    ['description' => 'Testing & QA', 'amount' => 500.00],
                ]
            ],
            [
                'title' => 'Brand Identity Package',
                'description' => 'Complete brand identity design including logo, guidelines, and marketing materials.',
                'amount' => 2500.00,
                'tax_amount' => 250.00,
                'invoice_items' => [
                    ['description' => 'Logo Design', 'amount' => 1000.00],
                    ['description' => 'Brand Guidelines', 'amount' => 800.00],
                    ['description' => 'Marketing Materials', 'amount' => 700.00],
                ]
            ],
            [
                'title' => 'Technical Support Package',
                'description' => 'Monthly technical support and maintenance services.',
                'amount' => 1500.00,
                'tax_amount' => 150.00,
                'invoice_items' => [
                    ['description' => '24/7 Support', 'amount' => 800.00],
                    ['description' => 'System Maintenance', 'amount' => 500.00],
                    ['description' => 'Updates & Patches', 'amount' => 200.00],
                ]
            ],
        ];

        $statusOptions = ['pending', 'paid', 'draft'];
        $invoiceCounter = 1;

        foreach ($companies as $company) {
            foreach ($company->agents as $agent) {
                // Create 3-5 invoices per agent
                $invoiceCount = rand(3, 5);
                
                for ($i = 0; $i < $invoiceCount; $i++) {
                    $template = $invoiceTemplates[array_rand($invoiceTemplates)];
                    
                    // Vary the amounts slightly
                    $amount = $template['amount'] + rand(-500, 500);
                    $taxAmount = $template['tax_amount'] + ($amount - $template['amount']) * 0.1;
                    $totalAmount = $amount + $taxAmount;
                    
                    // Create different statuses with realistic dates
                    $status = $statusOptions[array_rand($statusOptions)];
                    $createdAt = Carbon::now()->subDays(rand(1, 90));
                    
                    $dueDate = $createdAt->copy()->addDays(30);
                    $paidDate = null;
                    
                    if ($status === 'paid') {
                        $paidDate = $createdAt->copy()->addDays(rand(1, 25));
                    }

                    Invoice::create([
                        'invoice_number' => 'INV-' . date('Ym', $createdAt->timestamp) . '-' . str_pad($invoiceCounter++, 4, '0', STR_PAD_LEFT),
                        'company_id' => $company->id,
                        'agent_id' => $agent->id,
                        'title' => $template['title'],
                        'description' => $template['description'],
                        'amount' => $amount,
                        'tax_amount' => $taxAmount,
                        'total_amount' => $totalAmount,
                        'status' => $status,
                        'due_date' => $dueDate,
                        'paid_date' => $paidDate,
                        'invoice_items' => $template['invoice_items'],
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);
                }
            }
        }

        // Create some additional overdue invoices for demonstration (using 'pending' status with past due dates)
        $overdueDates = [
            Carbon::now()->subDays(45),
            Carbon::now()->subDays(60),
            Carbon::now()->subDays(75),
        ];

        foreach ($overdueDates as $index => $createdAt) {
            $company = $companies->random();
            $agent = $company->agents->random();
            $template = $invoiceTemplates[array_rand($invoiceTemplates)];

            Invoice::create([
                'invoice_number' => 'INV-' . date('Ym', $createdAt->timestamp) . '-' . str_pad($invoiceCounter++, 4, '0', STR_PAD_LEFT),
                'company_id' => $company->id,
                'agent_id' => $agent->id,
                'title' => $template['title'] . ' (Overdue)',
                'description' => $template['description'],
                'amount' => $template['amount'],
                'tax_amount' => $template['tax_amount'],
                'total_amount' => $template['amount'] + $template['tax_amount'],
                'status' => 'pending', // Will show as overdue due to past due date
                'due_date' => $createdAt->copy()->addDays(30),
                'paid_date' => null,
                'invoice_items' => $template['invoice_items'],
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }
    }
}
