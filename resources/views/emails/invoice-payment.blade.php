<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Payment Request</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #4F46E5;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f9fafb;
            padding: 30px;
            border: 1px solid #e5e7eb;
        }
        .invoice-details {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: 600;
            color: #6b7280;
        }
        .detail-value {
            color: #111827;
        }
        .total-amount {
            font-size: 24px;
            font-weight: bold;
            color: #4F46E5;
        }
        .button {
            display: inline-block;
            background-color: #10b981;
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 20px 0;
            text-align: center;
        }
        .button:hover {
            background-color: #059669;
        }
        .footer {
            text-align: center;
            color: #6b7280;
            font-size: 12px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }
        .warning {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Invoice Payment Request</h1>
    </div>
    
    <div class="content">
        <p>Hello,</p>
        
        <p>You have received an invoice that requires payment. Please review the details below:</p>
        
        <div class="invoice-details">
            <div class="detail-row">
                <span class="detail-label">Invoice Number:</span>
                <span class="detail-value">{{ $invoice->invoice_number }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">From:</span>
                <span class="detail-value">{{ $invoice->company->company_name }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">To:</span>
                <span class="detail-value">{{ $invoice->agent->user->name }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Description:</span>
                <span class="detail-value">{{ $invoice->title }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Due Date:</span>
                <span class="detail-value">{{ $invoice->due_date->format('M d, Y') }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Amount Due:</span>
                <span class="detail-value total-amount">${{ number_format($invoice->total_amount, 2) }}</span>
            </div>
        </div>
        
        <div style="text-align: center;">
            <a href="{{ $paymentUrl }}" class="button">Pay Invoice Now</a>
        </div>
        
        <div class="warning">
            <strong>Note:</strong> This payment link will expire in 30 days. You can pay using credit/debit card or ACH bank transfer.
        </div>
        
        @if($invoice->description)
        <div style="margin-top: 20px;">
            <p><strong>Additional Details:</strong></p>
            <p>{{ $invoice->description }}</p>
        </div>
        @endif
        
        <p>If you have any questions about this invoice, please contact {{ $invoice->company->company_name }}.</p>
    </div>
    
    <div class="footer">
        <p>This is an automated email. Please do not reply to this message.</p>
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
    </div>
</body>
</html>

