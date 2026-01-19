<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Already Paid</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="bg-green-600 px-6 py-4">
                    <h2 class="text-xl font-semibold text-white text-center">Invoice Already Paid</h2>
                </div>
                
                <div class="px-6 py-8 text-center">
                    <svg class="mx-auto h-16 w-16 text-green-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Payment Complete</h3>
                    <p class="text-gray-600 mb-6">
                        This invoice has already been paid on {{ $invoice->paid_date->format('M d, Y') }}.
                    </p>
                    
                    <div class="bg-gray-50 rounded-lg p-4 mb-6">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm text-gray-600">Invoice Number</span>
                            <span class="text-sm font-medium text-gray-900">{{ $invoice->invoice_number }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Amount Paid</span>
                            <span class="text-sm font-medium text-green-600">${{ number_format($invoice->total_amount, 2) }}</span>
                        </div>
                    </div>
                    
                    <p class="text-sm text-gray-500">
                        If you have any questions, please contact {{ $invoice->company->company_name }}.
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

