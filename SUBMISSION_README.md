# Retail Pharmacy Management System — Final Submission

## Live URLs (after `php artisan serve`)

| Portal | URL | Demo login |
|--------|-----|------------|
| **Staff login** | http://127.0.0.1:8000/login | See table below |
| **Customer shop** | http://127.0.0.1:8000/shop | `customer@pharmacy.local` / `password` |
| **Pharmacist** | http://127.0.0.1:8000/ | `demo@pharmacy.local` / `password` |
| **Supplier** | http://127.0.0.1:8000/ | `demo-supplier@pharmacy.local` / `password` |

On the login page, click a **demo account** button to auto-fill credentials.

## Setup (one time)

```powershell
cd P221-main
composer install
npm install
cp .env.example .env   # if needed
php artisan key:generate
# Enable ext-mongodb in php.ini, start MongoDB service
php artisan pharmacy:clone-database --fresh --migrate --seed
# OR: php artisan migrate --force && php artisan db:seed --class=Database\\Seeders\\ShopDatabaseSeeder --force
npm run build
php artisan serve
```

## Features by role

### Customer (`/shop`)
- Medicine catalog with images, search, filters
- Cart, wishlist, checkout, AI safety consultant
- Orders, prescriptions, notifications
- Same sidebar layout as staff portal

### Pharmacist (`/`)
- Dashboard, inventory, billing, sales, suppliers

### Supplier (`/`)
- Dashboard, incoming orders, inventory

## Database

MongoDB database: `retail_pharmacy_shop` (see `.env`)

Medicine images: `/public/images/medicines/*.svg`

## Tech stack

Laravel 12 · Inertia.js · Vue 3 · Tailwind CSS 4 · MongoDB
