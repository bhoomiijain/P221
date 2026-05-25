# Customer E-Commerce Module

Online pharmacy customer portal integrated with the existing pharmacist and supplier modules.

## Access

| Role | URL | Credentials (after seed) |
|------|-----|--------------------------|
| Customer | `/shop` | `customer@pharmacy.local` / `password` |
| Pharmacist | `/` | `demo@pharmacy.local` / `password` |
| Supplier | `/` | `demo-supplier@pharmacy.local` / `password` |

## Run

```bash
cd P221-main
composer install
npm install
cp .env.example .env   # configure MongoDB
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan storage:link
npm run build
php artisan serve
```

Dev mode: `composer run dev`

## New Folders

### Backend (`app/`)

- `Http/Controllers/Api/Customer/` — REST APIs (cart, orders, AI, prescriptions)
- `Http/Controllers/Web/Customer/` — Inertia page controllers
- `Http/Middleware/CustomerMiddleware.php` — customer-only routes
- `Http/Middleware/StaffMiddleware.php` — blocks customers from staff portal
- `Models/` — CustomerAddress, CustomerCart, CustomerOrder, WishlistItem, Prescription, etc.
- `Services/CustomerCatalogService.php` — product listing & stock
- `Services/CustomerOrderService.php` — cart, checkout, coupons
- `Services/AiConsultationService.php` — rule-based safety analysis (AI-ready)
- `Services/RecommendationService.php` — featured, trending, related products

### Frontend (`resources/js/`)

- `Layouts/CustomerLayout.vue` — shop header/footer, dark mode
- `Pages/Customer/` — all customer pages
- `Components/Customer/` — MedicineCard, AiConsultantModal
- `composables/useCustomerApi.js` — API client

### Routes

- `routes/customer.php` — `/shop/*` web routes
- `routes/api.php` — `/api/customer/*` endpoints

## API Endpoints

| Method | Endpoint | Auth |
|--------|----------|------|
| GET | `/api/customer/products` | Public |
| GET | `/api/customer/products/{id}` | Public |
| GET | `/api/customer/recommendations` | Public |
| POST | `/api/customer/register` | Public |
| POST | `/api/customer/login` | Public |
| GET/POST | `/api/customer/cart` | Customer |
| GET/POST | `/api/customer/wishlist` | Customer |
| POST | `/api/customer/orders` | Customer |
| POST | `/api/customer/orders/analyze-ai` | Customer |
| GET | `/api/customer/orders/{id}/invoice` | Customer |
| POST | `/api/customer/prescriptions` | Customer |
| GET | `/api/customer/notifications` | Customer |

## Database Collections

- `customer_addresses`, `customer_carts`, `customer_cart_items`
- `wishlist_items`, `customer_orders`, `customer_order_items`
- `customer_reviews`, `prescriptions`, `customer_payments`
- `customer_notifications`, `ai_consultation_logs`, `refill_reminders`, `coupons`

Medicines collection extended with e-commerce fields: `mrp`, `brand`, `manufacturer`, `prescription_required`, ratings, etc.

## Features Implemented

1. Landing page with hero, categories, offers, testimonials
2. Customer auth (register, login, forgot password, OTP UI)
3. Dashboard with orders, wishlist, prescriptions, recommendations
4. Medicine listing with filters, sort, pagination
5. Product details with reviews, related products
6. Cart with coupons, tax, delivery, save for later
7. Wishlist (persistent in MongoDB)
8. Checkout with address, payment methods (COD/UPI/Card/Wallet)
9. **AI consultant modal** before order confirmation
10. Order tracking timeline
11. Prescription upload (image/PDF)
12. Notifications
13. Recommendations engine
14. Refill reminders API
15. Emergency page (pharmacies, ambulance UI)
16. Medicine comparison
17. Dark/light mode, responsive healthcare UI

## Future AI Integration

Replace `AiConsultationService::analyze()` with:

- OpenAI GPT-4 / Gemini API for conversational flow
- OCR on prescriptions (Google Vision, AWS Textract)
- Voice search via Web Speech API + NLP backend
- Real-time drug interaction databases (FDA, DrugBank APIs)

Architecture already stores `ai_consultation_logs` and `ocr_data` on prescriptions for this upgrade path.
