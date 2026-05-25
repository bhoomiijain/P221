# Retail Pharmacy MongoDB Schema

This system is intentionally batch-first. Medicines never store stock directly.

## Collections

`users`
- `name`, `email`, `password`, `role`, `theme`
- roles: `admin`, `pharmacist`, `supplier`, `customer`

`categories`
- `name`

`medicines`
- `name`
- `category_id`
- `description`
- `search_key`
- computed stock only: total stock is `sum(batches.quantity)`

`batches`
- `medicine_id`
- `batch_number`
- `expiry_date`
- `quantity`
- `purchase_price`
- `selling_price`

`suppliers`
- `name`
- `contact_info`
- `search_key`

`purchases`
- `supplier_id`
- `purchase_date`

`purchase_items`
- `purchase_id`
- `medicine_id`
- `batch_id`
- `quantity`
- `purchase_price`

`sales`
- `user_id`
- `subtotal`
- `discount`
- `tax`
- `total_amount`
- `created_at`

`sale_items`
- `sale_id`
- `batch_id`
- `medicine_id`
- `quantity`
- `selling_price`
- `line_total`

`inventory_alerts`
- `type`
- `medicine_id`
- `batch_id`
- `message`
- `level`
- `resolved_at`

`personal_access_tokens`
- Mongo-backed Sanctum API token storage.

### Customer e-commerce (see `docs/CUSTOMER_MODULE.md`)

`customer_addresses`, `customer_carts`, `customer_cart_items`, `wishlist_items`, `customer_orders`, `customer_order_items`, `customer_reviews`, `prescriptions`, `customer_payments`, `customer_notifications`, `ai_consultation_logs`, `refill_reminders`, `coupons`

### Shop landing content

`shop_banners`, `health_tips`, `shop_testimonials`

**Recommended database name:** `retail_pharmacy_shop` — copy from `retail_pharmacy` with `php artisan pharmacy:clone-database` (see `docs/SHOP_DATABASE.md`).

## Indexes

Defined in `database/migrations/2026_04_30_000000_create_pharmacy_collections.php`.

- `medicines.category_id`
- `medicines.search_key`
- `batches.medicine_id`
- `batches.expiry_date`
- `batches.batch_number`
- compound FIFO index: `batches.medicine_id + batches.expiry_date + batches.quantity`
- unique batch identity: `batches.medicine_id + batches.batch_number`
- `suppliers.search_key`
- purchase/sale item foreign-key style lookup indexes
- alert and token lookup indexes

## Critical Rules

- FIFO sale allocation is handled by `App\Services\InventoryService::reserveFifo`.
- Atomic checkout is handled by `App\Services\BillingService::checkout`.
- Expired batches are excluded from sellable stock.
- Batch deduction re-reads the batch and blocks negative stock.
- Sale items point to `batch_id`; `medicine_id` is duplicated only as a reporting convenience.

## Runtime Notes

Enable the PHP MongoDB extension before running migrations:

```bash
php -m | grep mongodb
php artisan migrate --seed
php artisan queue:work
php artisan schedule:work
```

Default seeded users:

- `admin@pharmacy.local` / `password`
- `pharmacist@pharmacy.local` / `password`
