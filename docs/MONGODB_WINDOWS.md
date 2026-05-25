# MongoDB on Windows (XAMPP PHP 8.2)

## Error: `Class "MongoDB\Driver\Manager" not found`

The **PHP MongoDB extension** must be enabled in `C:\XAMPP221\php\php.ini`:

```ini
extension=mongodb
```

DLL: `C:\XAMPP221\php\ext\php_mongodb.dll` (PHP 8.2 **Thread Safe** x64, from [PECL mongodb 2.1.8](https://pecl.php.net/package/mongodb/2.1.8/windows)).

Verify:

```powershell
php -m | findstr mongodb
```

Restart **php artisan serve** after changing `php.ini`.

---

## Error: `connection refused ... 127.0.0.1:27017`

The **MongoDB server** must be running.

1. Open **Services** (`services.msc`)
2. Find **MongoDB Server (MongoDB)**
3. Click **Start** (Run as Administrator if needed)

Or in an **Administrator** PowerShell:

```powershell
Start-Service MongoDB
```

Then initialize the app database:

```powershell
cd c:\Users\Dell\Downloads\P221-main\P221-main
php artisan migrate --force
php artisan db:seed --force
```

`.env` defaults (database on **this** PC only):

```
MONGODB_URI=mongodb://127.0.0.1:27017
MONGODB_DATABASE=retail_pharmacy
```

---

## Database on a teammate’s laptop (your situation)

This project uses **MongoDB**, not MySQL. You do **not** create tables in phpMyAdmin. Collections are created by:

```powershell
php artisan migrate --force
php artisan db:seed --force
```

### Option A — Use your teammate’s MongoDB (same Wi‑Fi / LAN)

**On your teammate’s laptop** (where MongoDB runs):

1. Start MongoDB (`Start-Service MongoDB` as Admin, or Services app).
2. Allow remote connections — edit `mongod.cfg` (often `C:\Program Files\MongoDB\Server\8.0\bin\mongod.cfg`):
   ```yaml
   net:
     bindIp: 0.0.0.0
     port: 27017
   ```
3. Restart MongoDB service.
4. Windows Firewall → allow inbound **TCP 27017**.
5. Share their LAN IP: `ipconfig` → IPv4 (e.g. `192.168.1.45`).

**On your laptop**, edit `.env`:

```
MONGODB_URI=mongodb://192.168.1.45:27017
MONGODB_DATABASE=retail_pharmacy
```

Replace `192.168.1.45` with your teammate’s real IP. Then:

```powershell
php artisan config:clear
php artisan migrate --force
php artisan db:seed --force
```

Both of you must use the **same** `MONGODB_DATABASE` name. Run **seed once** (whoever sets up first), or you’ll duplicate demo data.

**Test connection:**

```powershell
php artisan tinker
>>> \App\Models\User::count();
```

If that returns a number, registration will work.

### Option B — MongoDB Atlas (best for teams, no teammate IP)

1. Create a free cluster at [https://www.mongodb.com/cloud/atlas](https://www.mongodb.com/cloud/atlas).
2. Create a database user + password.
3. Network Access → add your IP (or `0.0.0.0/0` for college demos only).
4. Copy the connection string, e.g.  
   `mongodb+srv://user:pass@cluster0.xxxxx.mongodb.net/retail_pharmacy`
5. Put it in **both** teammates’ `.env`:

```
MONGODB_URI=mongodb+srv://USER:PASS@cluster0.xxxxx.mongodb.net/retail_pharmacy?retryWrites=true&w=majority
MONGODB_DATABASE=retail_pharmacy
```

6. Run `php artisan migrate --force` and `php artisan db:seed --force` once.

Everyone shares one cloud database — no need for your teammate’s laptop to stay on.

### Option C — MongoDB only on your laptop

Keep:

```
MONGODB_URI=mongodb://127.0.0.1:27017
```

Install/start MongoDB locally, run migrate + seed (you already did this if terminal showed DONE). Your teammate uses **their own** `.env` pointing to Atlas or their PC — you don’t share data unless you use A or B.

---

## Can you add data manually?

Yes, with **MongoDB Compass** (GUI), but you don’t need to for this project.

| Task | What to do |
|------|------------|
| Create collections / indexes | `php artisan migrate` |
| Demo medicines, users, coupons | `php artisan db:seed` |
| Add one user by hand | Compass → database `retail_pharmacy` → collection `users` → Insert Document |

Example user document (password must be **bcrypt** — easier to use register or seed):

```json
{
  "name": "Test Customer",
  "email": "test@example.com",
  "password": "<use register page instead>",
  "role": "customer"
}
```

**Recommended:** use the app Register page or seeded accounts:

| Role | Email | Password |
|------|-------|----------|
| Customer | `customer@pharmacy.local` | `password` |
| Pharmacist | `demo@pharmacy.local` | `password` |

---

## Still seeing `connection refused` on register?

1. `php -m` must include **mongodb** (PHP extension).
2. Restart `php artisan serve` after changing `.env`.
3. `MONGODB_URI` must reach a running server (your PC, teammate IP, or Atlas).
4. Passwords on register must **match** (`password` = `password_confirmation`).
