# New shop database (`retail_pharmacy_shop`)

MongoDB uses **collections** (like tables). The new database keeps everything from the old pharmacy DB **plus** customer/shop collections.

## Collection map

| Group | Collections |
|-------|-------------|
| **Staff (existing)** | `users`, `categories`, `medicines`, `batches`, `suppliers`, `purchases`, `purchase_items`, `sales`, `sale_items`, `inventory_alerts`, `supplier_medicines`, `supplier_orders`, … |
| **Customer shop** | `customer_addresses`, `customer_carts`, `customer_cart_items`, `wishlist_items`, `customer_orders`, `customer_order_items`, `customer_reviews`, `prescriptions`, `customer_payments`, `customer_notifications`, `ai_consultation_logs`, `refill_reminders`, `coupons` |
| **Shop page content** | `shop_banners`, `health_tips`, `shop_testimonials` |

## One-command setup (copy old DB → new DB)

**1. Edit `.env`:**

```env
MONGODB_URI=mongodb://127.0.0.1:27017
MONGODB_SOURCE_DATABASE=retail_pharmacy
MONGODB_DATABASE=retail_pharmacy_shop
```

Use your teammate’s IP in `MONGODB_URI` if MongoDB runs on their laptop.

**2. Run clone + indexes + seed:**

```powershell
cd c:\Users\Dell\Downloads\P221-main\P221-main
php artisan pharmacy:clone-database --fresh --migrate --seed
php artisan config:clear
php artisan serve
```

| Flag | Meaning |
|------|---------|
| `--from=retail_pharmacy` | Source database (old) |
| `--to=retail_pharmacy_shop` | New database name |
| `--fresh` | Empty target before copy (avoids duplicates) |
| `--migrate` | Create indexes on all collections |
| `--seed` | Demo users, medicines, coupons, shop banners |

## Fresh database (no copy)

If you only want a new empty shop database:

```env
MONGODB_DATABASE=retail_pharmacy_shop
```

```powershell
php artisan migrate --force
php artisan db:seed --class=Database\\Seeders\\ShopDatabaseSeeder --force
```

## Verify in MongoDB Compass

Connect to `MONGODB_URI` → database **`retail_pharmacy_shop`** → you should see all collections above.

Test in terminal:

```powershell
php artisan tinker
>>> \App\Models\User::where('role','customer')->count();
>>> \App\Models\ShopBanner::count();
```

## Demo logins (after seed)

| Role | Email | Password |
|------|-------|----------|
| Customer | `customer@pharmacy.local` | `password` |
| Pharmacist | `demo@pharmacy.local` | `password` |
| Supplier | `demo-supplier@pharmacy.local` | `password` |

Shop: http://127.0.0.1:8000/shop
