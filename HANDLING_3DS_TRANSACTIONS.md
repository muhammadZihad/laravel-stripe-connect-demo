# Handling Pending Transactions Due to 3D Secure (3DS) Authentication

## Overview

When a payment requires 3D Secure authentication, the payment flow works differently than a standard payment. This document explains how your system handles these transactions and what actions you can take.

## How 3DS Payments Work

### Normal Payment Flow (Without 3DS)
1. User submits payment
2. PaymentIntent is created and immediately succeeds
3. Transfer is created to agent
4. Transaction marked as `completed`
5. Invoice marked as `paid`

### 3DS Payment Flow
1. User submits payment
2. PaymentIntent is created with status `requires_action`
3. Transaction is created with status `pending`
4. User completes 3DS authentication (pop-up or redirect)
5. One of three things happens:
   - **Frontend confirmation** (if user stays on page)
   - **Webhook notification** (if user closes browser)
   - **Manual reconciliation** (if webhook fails)

## Transaction Statuses

- **`pending`**: Payment is awaiting 3DS authentication or processing
- **`completed`**: Payment succeeded and transfer completed
- **`failed`**: Payment failed or was canceled
- **`transfer_failed`**: Payment succeeded but transfer to agent failed

## Automatic Handling

### 1. Webhook Handler (`payment_intent.succeeded`)

The webhook automatically handles successful 3DS payments:

```php
// Located in: app/Services/StripeService.php -> handlePaymentSucceeded()
```

When a PaymentIntent succeeds:
- Checks if transaction is still pending
- Creates the transfer to the agent
- Marks transaction as completed
- Marks invoice as paid

**What triggers this**: Stripe sends a webhook when 3DS authentication completes successfully

### 2. Frontend Confirmation

After successful 3DS authentication, the frontend calls:
- Company payments: `POST /company/invoices/{invoice}/pay/confirm`
- Public payments: `POST /invoice/pay/{token}/confirm`

These endpoints:
- Verify the PaymentIntent status with Stripe
- Create the transfer to agent (if not already created)
- Complete the transaction and invoice

## Manual Reconciliation

### Via Admin Panel

1. Navigate to: **Super Admin → Pending Transactions**
   - URL: `/super-admin/transactions/pending/list`

2. View all pending transactions with:
   - Transaction details
   - Age of transaction
   - Related invoice/company/agent

3. Click **"Reconcile Pending Transactions"** to:
   - Check each pending transaction with Stripe
   - Update status based on actual payment state
   - Create missing transfers
   - Mark completed/failed appropriately

### Via Command Line

Run the reconciliation command:

```bash
# Check and reconcile transactions older than 10 minutes (default)
php artisan transactions:reconcile-pending

# Check transactions older than 5 minutes
php artisan transactions:reconcile-pending --minutes=5

# Dry run to see what would be reconciled
php artisan transactions:reconcile-pending --dry-run
```

**Recommended Schedule**: Add to your `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Run every 15 minutes to catch abandoned 3DS flows
    $schedule->command('transactions:reconcile-pending --minutes=10')
             ->everyFifteenMinutes();
}
```

## API Endpoints

### Admin Endpoints

**Get Pending Transactions**
```
GET /super-admin/transactions/pending/list
```

**Reconcile Pending Transactions**
```
POST /super-admin/transactions/pending/reconcile
Body: { "older_than_minutes": 10 }
```

**Check Specific Transaction Status**
```
GET /super-admin/transactions/{transaction}/check-status
```

### Company/Payment Confirmation Endpoints

**Confirm Company Payment After 3DS**
```
POST /company/invoices/{invoice}/pay/confirm
Body: { "payment_intent_id": "pi_xxx" }
```

**Confirm Public Payment After 3DS**
```
POST /invoice/pay/{token}/confirm
Body: { "payment_intent_id": "pi_xxx" }
```

## Transaction Model Helper Methods

New helper methods in `App\Models\Transaction`:

```php
// Check if transaction is awaiting 3DS authentication
$transaction->isAwaitingAuthentication();

// Get human-readable status
$transaction->getStatusWithDetails();
// Returns: "Pending - Awaiting 3D Secure Authentication"

// Check if old enough to reconcile
$transaction->shouldReconcile($minutesOld = 10);
```

## Monitoring & Alerts

### What to Monitor

1. **Pending transactions older than 30 minutes**
   - May indicate webhook delivery issues
   - May indicate user abandoned payment

2. **Pending transactions without `stripe_payment_intent_id`**
   - Indicates incomplete payment flow
   - Should be investigated manually

3. **Multiple failed reconciliation attempts**
   - May indicate Stripe API issues
   - May indicate network problems

### Recommended Alerts

Set up alerts for:
- Pending transactions older than 1 hour
- Failed transfers (`transfer_failed` status)
- Reconciliation command failures

## Troubleshooting

### Transaction Stuck as Pending

**Check 1: Verify Payment Intent Status**
```bash
# Via admin panel
1. Go to Pending Transactions
2. Click "Check Status" next to the transaction

# Via Stripe Dashboard
1. Go to stripe.com/dashboard
2. Search for the Payment Intent ID
3. Check the status and events
```

**Check 2: Check Webhook Delivery**
- Go to Stripe Dashboard → Developers → Webhooks
- Find `payment_intent.succeeded` events
- Check if they were delivered successfully
- Retry failed webhooks if needed

**Check 3: Manual Reconciliation**
```bash
php artisan transactions:reconcile-pending --minutes=1
```

### Transfer Failed After Successful Payment

If payment succeeded but transfer failed (status: `transfer_failed`):

1. Check agent's Connect account status:
   - Is onboarding complete?
   - Are transfers enabled?
   - Is account in good standing?

2. Check platform balance:
   - Does your platform account have sufficient balance?

3. Retry the transfer manually:
   - Contact support or manually create transfer via Stripe Dashboard

### User Closed Browser During 3DS

**No action needed** - The webhook will handle it automatically:
1. User authenticates with bank
2. Authentication succeeds
3. Stripe sends `payment_intent.succeeded` webhook
4. Your webhook handler creates the transfer
5. Transaction marked as completed

**If webhook fails**:
- Reconciliation command will catch it within 15 minutes (if scheduled)
- Or run manual reconciliation

## Best Practices

### 1. Always Use Webhooks
- Configure webhooks in Stripe Dashboard
- Listen for `payment_intent.succeeded`
- Listen for `payment_intent.payment_failed`
- Listen for `transfer.failed`

### 2. Schedule Regular Reconciliation
```php
// In app/Console/Kernel.php
$schedule->command('transactions:reconcile-pending --minutes=10')
         ->everyFifteenMinutes();
```

### 3. Monitor Pending Transactions
- Check daily for stuck transactions
- Alert on transactions older than 1 hour
- Investigate any `transfer_failed` transactions immediately

### 4. Test 3DS Flow
- Use Stripe test cards that trigger 3DS
- Test card: `4000002500003155` (requires 3DS)
- Verify webhook handling
- Verify frontend confirmation
- Test abandonment scenarios

## Stripe Test Cards for 3DS

### Cards that Require 3DS Authentication
- `4000002500003155` - Requires authentication, succeeds
- `4000002760003184` - Requires authentication, is declined

### Testing 3DS Flows

1. **Successful 3DS Authentication**:
   - Use card `4000002500003155`
   - Complete 3DS challenge
   - Should complete payment and transfer

2. **Abandoned 3DS Flow**:
   - Use card `4000002500003155`
   - Close browser during 3DS challenge
   - Complete 3DS in another tab/window
   - Webhook should complete the transaction

3. **Failed 3DS Authentication**:
   - Use card `4000002760003184`
   - Complete 3DS challenge
   - Payment should fail
   - Transaction should be marked as failed

## Summary

Your system now has three layers of protection for 3DS payments:

1. **Frontend Confirmation**: Immediate completion if user stays on page
2. **Webhook Handling**: Automatic completion if user leaves
3. **Manual Reconciliation**: Safety net for any edge cases

**Key Takeaway**: Pending transactions due to 3DS are normal and will be handled automatically. The reconciliation process ensures nothing falls through the cracks.

## Questions or Issues?

Check logs for details:
```bash
tail -f storage/logs/laravel.log | grep -i "3ds\|payment_intent\|transfer"
```

Look for:
- `3DS payment succeeded via webhook`
- `Transfer created during reconciliation`
- `Payment requires action (3DS authentication)`

