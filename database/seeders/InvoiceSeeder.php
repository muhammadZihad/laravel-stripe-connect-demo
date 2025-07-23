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

        // Updated invoice templates with amounts under $100
        $invoiceTemplates = [
            [
                'title' => 'Logo Design Service',
                'description' => 'Custom logo design with 3 concept variations and final files.',
                'amount' => 75.00,
                'tax_amount' => 7.50,
                'invoice_items' => [
                    ['description' => 'Logo Concepts', 'amount' => 45.00],
                    ['description' => 'Final Design & Files', 'amount' => 30.00],
                ]
            ],
            [
                'title' => 'Social Media Content Package',
                'description' => 'One week of social media content creation and scheduling.',
                'amount' => 85.00,
                'tax_amount' => 8.50,
                'invoice_items' => [
                    ['description' => 'Content Creation', 'amount' => 50.00],
                    ['description' => 'Scheduling & Management', 'amount' => 35.00],
                ]
            ],
            [
                'title' => 'Business Card Design',
                'description' => 'Professional business card design with print-ready files.',
                'amount' => 45.00,
                'tax_amount' => 4.50,
                'invoice_items' => [
                    ['description' => 'Design & Layout', 'amount' => 35.00],
                    ['description' => 'Print Files', 'amount' => 10.00],
                ]
            ],
            [
                'title' => 'Website Consultation',
                'description' => '2-hour website strategy and improvement consultation.',
                'amount' => 90.00,
                'tax_amount' => 9.00,
                'invoice_items' => [
                    ['description' => 'Strategy Session', 'amount' => 60.00],
                    ['description' => 'Report & Recommendations', 'amount' => 30.00],
                ]
            ],
            [
                'title' => 'Content Writing Service',
                'description' => 'Professional copywriting for landing page content.',
                'amount' => 65.00,
                'tax_amount' => 6.50,
                'invoice_items' => [
                    ['description' => 'Content Research', 'amount' => 25.00],
                    ['description' => 'Writing & Editing', 'amount' => 40.00],
                ]
            ],
            [
                'title' => 'Basic SEO Audit',
                'description' => 'Website SEO analysis with improvement recommendations.',
                'amount' => 80.00,
                'tax_amount' => 8.00,
                'invoice_items' => [
                    ['description' => 'Technical Analysis', 'amount' => 50.00],
                    ['description' => 'Audit Report', 'amount' => 30.00],
                ]
            ],
            [
                'title' => 'Email Template Design',
                'description' => 'Custom email newsletter template with responsive design.',
                'amount' => 55.00,
                'tax_amount' => 5.50,
                'invoice_items' => [
                    ['description' => 'Template Design', 'amount' => 35.00],
                    ['description' => 'Mobile Optimization', 'amount' => 20.00],
                ]
            ],
            [
                'title' => 'Product Photography Edit',
                'description' => 'Professional photo editing for 10 product images.',
                'amount' => 70.00,
                'tax_amount' => 7.00,
                'invoice_items' => [
                    ['description' => 'Image Editing', 'amount' => 50.00],
                    ['description' => 'Color Correction', 'amount' => 20.00],
                ]
            ],
        ];

        $invoiceCounter = 1;

        foreach ($companies as $company) {
            foreach ($company->agents as $agent) {
                // Create 2-4 invoices per agent (only pending)
                $invoiceCount = rand(2, 4);
                
                for ($i = 0; $i < $invoiceCount; $i++) {
                    $template = $invoiceTemplates[array_rand($invoiceTemplates)];
                    
                    // Vary the amounts slightly but keep under $100
                    $baseAmount = $template['amount'];
                    $variation = rand(-15, 15); // ±$15 variation
                    $amount = max(10.00, min(95.00, $baseAmount + $variation)); // Keep between $10-$95
                    
                    // Calculate 10% tax but ensure it makes total under $100
                    $taxAmount = round($amount * 0.1, 2);
                    $totalAmount = $amount + $taxAmount;
                    
                    // If total exceeds $100, reduce the amount
                    if ($totalAmount >= 100.00) {
                        $amount = 90.00;
                        $taxAmount = 9.00;
                        $totalAmount = 99.00;
                    }
                    
                    // Create invoice with realistic dates
                    $createdAt = Carbon::now()->subDays(rand(1, 60));
                    $dueDate = $createdAt->copy()->addDays(rand(15, 45)); // 15-45 days to pay

                    Invoice::create([
                        'invoice_number' => 'INV-' . date('Ym', $createdAt->timestamp) . '-' . str_pad($invoiceCounter++, 4, '0', STR_PAD_LEFT),
                        'company_id' => $company->id,
                        'agent_id' => $agent->id,
                        'title' => $template['title'],
                        'description' => $template['description'],
                        'amount' => $amount,
                        'tax_amount' => $taxAmount,
                        'total_amount' => $totalAmount,
                        'status' => 'pending', // Only pending invoices
                        'due_date' => $dueDate,
                        'paid_date' => null, // No paid date for pending invoices
                        'invoice_items' => $this->adjustInvoiceItems($template['invoice_items'], $amount, $baseAmount),
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);
                }
            }
        }

        // Create some additional overdue invoices (still pending but past due date)
        $overdueDates = [
            Carbon::now()->subDays(70),
            Carbon::now()->subDays(85),
            Carbon::now()->subDays(100),
        ];

        foreach ($overdueDates as $index => $createdAt) {
            $company = $companies->random();
            $agent = $company->agents->random();
            $template = $invoiceTemplates[array_rand($invoiceTemplates)];
            
            $amount = rand(25, 85); // Smaller amounts for overdue
            $taxAmount = round($amount * 0.1, 2);
            $totalAmount = $amount + $taxAmount;

            Invoice::create([
                'invoice_number' => 'INV-' . date('Ym', $createdAt->timestamp) . '-' . str_pad($invoiceCounter++, 4, '0', STR_PAD_LEFT),
                'company_id' => $company->id,
                'agent_id' => $agent->id,
                'title' => $template['title'] . ' (Overdue)',
                'description' => $template['description'],
                'amount' => $amount,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'status' => 'pending', // Will show as overdue due to past due date
                'due_date' => $createdAt->copy()->addDays(30), // Past due date
                'paid_date' => null,
                'invoice_items' => $this->adjustInvoiceItems($template['invoice_items'], $amount, $template['amount']),
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }

        $this->command->info('Invoices seeded successfully with amounts under $100, 10% application fees ($1-$4), and pending status only!');
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
