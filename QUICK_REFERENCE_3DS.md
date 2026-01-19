# 3DS Pending Transactions - Quick Reference

## TL;DR

**Pending transactions due to 3DS are automatically handled by:**
1. Webhooks (instant)
2. Scheduled reconciliation (every 15 minutes)
3. Manual reconciliation (on-demand)

**You don't need to do anything - the system handles it automatically!**

---

## Quick Actions

### View Pending Transactions
```
Admin Panel → Pending (menu item)
URL: /super-admin/transactions/pending/list
```

### Reconcile All Pending Transactions
```bash
php artisan transactions:reconcile-pending
```

### Check Specific Transaction
```
Admin Panel → Pending → Click "Check Status"
```

---

## Status Meanings

| Status | Meaning | Action Needed |
|--------|---------|---------------|
| `pending` | Awaiting 3DS auth or processing | None - will auto-complete |
| `completed` | Payment and transfer successful | None |
| `failed` | Payment failed or canceled | None - inform customer |
| `transfer_failed` | Payment OK, transfer failed | **Manual intervention** |

---

## When to Worry

✅ **Normal** (No action needed):
- Pending < 30 minutes for cards
- Pending < 7 days for ACH

⚠️ **Investigate**:
- Pending > 1 hour for cards
- Status: `transfer_failed`
- Multiple old pending transactions

---

## Quick Commands

```bash
# View pending transactions
php artisan transactions:reconcile-pending --dry-run

# Reconcile pending transactions
php artisan transactions:reconcile-pending

# Reconcile transactions older than 5 minutes
php artisan transactions:reconcile-pending --minutes=5

# Watch logs for 3DS activity
tail -f storage/logs/laravel.log | grep -i "3ds\|payment_intent"
```

---

## Test Cards

| Card Number | Behavior |
|-------------|----------|
| `4000002500003155` | Requires 3DS, succeeds |
| `4000002760003184` | Requires 3DS, fails |

---

## Setup Checklist

- [ ] Configure Stripe webhooks for `payment_intent.succeeded`
- [ ] Schedule reconciliation command (every 15 minutes)
- [ ] Test with 3DS test card
- [ ] Monitor pending transactions page

---

## Emergency Actions

**Transaction stuck pending for hours?**
```bash
php artisan transactions:reconcile-pending --minutes=1
```

**Need to check Stripe directly?**
1. Copy Payment Intent ID from transaction
2. Go to: https://dashboard.stripe.com/payments
3. Search for Payment Intent ID
4. Check status and events

**Webhook not working?**
1. Check: https://dashboard.stripe.com/webhooks
2. Find your endpoint
3. Check "Recent deliveries"
4. Retry failed webhooks

---

## Key Files

| File | Purpose |
|------|---------|
| `app/Services/StripeService.php` | Webhook & reconciliation logic |
| `app/Console/Commands/ReconcilePendingTransactions.php` | CLI command |
| `resources/views/super_admin/pending_transactions.blade.php` | Admin UI |

---

## Support

**Something not working?**

1. Check logs: `tail -f storage/logs/laravel.log`
2. View pending: `/super-admin/transactions/pending/list`
3. Run reconciliation: `php artisan transactions:reconcile-pending`
4. Check Stripe Dashboard

**Still stuck?**

Read full documentation: `HANDLING_3DS_TRANSACTIONS.md`

