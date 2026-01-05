# Utilities Billing — Laravel CSV Importer (Filament + Queues + PDF/QR)

A portfolio-ready Laravel backend that imports utility bills from CSV, creates invoices, generates payment QR codes and PDF invoices, and exposes an API secured with Laravel Sanctum + abilities and Policies. Includes a Filament admin panel for managing Customers, Imports, Invoices, and Users.

> Primary use-case: bulk import of monthly utilities (gas/electricity/heating/water/etc.) and automatic invoice generation for Romanian payments (RON/EUR).

---

## Highlights

- **CSV Import** with row-level validation and error reporting
- **Queue-driven processing** (Redis + Laravel queue worker)
- **Invoice lifecycle** (issue, mark as paid, overdue by due_date)
- **PDF invoice generation** (DomPDF) with **payment QR code** payload
- **Filament Admin Panel**
  - Resources for **Customer / Import / Invoice / User**
  - Custom widget to upload CSV
  - Invoice statistics widgets
- **API** with **Sanctum** bearer tokens + **abilities** and **Policies**
- **Docker Compose** local environment (Nginx + PHP-FPM + MySQL + Redis + queue worker + scheduler)
- **Tests** for Jobs (ImportJob, InvoiceJob)

---

## Tech Stack

- PHP 8.2
- Laravel
- Filament Admin
- MySQL 8.4
- Redis 7.x (queues/cache/session)
- Nginx + PHP-FPM
- DomPDF (PDF)
- simple-qrcode (QR)
- Docker Compose

---

## Repository Layout

This repository contains:

- `docker-compose.yml` — full local stack
- `docker/` — Nginx / PHP / Redis configuration
- `csv_import/` — Laravel application source

> Recommendation (for GitHub polish): move `csv_import/` contents to repository root so the project looks like a standard Laravel repo.

---

## Quick Start (Docker)

### 1) Requirements

- Docker + Docker Compose

### 2) Boot the stack

```bash
docker compose up -d --build
```
Services:
-	nginx → http://localhost:8080
-	php → PHP-FPM app container
-	queue → php artisan queue:work  
-	scheduler → runs php artisan schedule:run every 60s
-	db → MySQL
-	redis → Redis

### 3) Install dependencies + app key
```bash
docker compose exec php sh -lc "composer install"
docker compose exec php sh -lc "php artisan key:generate"
```
### 4) Migrate + seed
```bash
docker compose exec php sh -lc "php artisan migrate --force"
docker compose exec php sh -lc "php artisan db:seed"
```

### 5) Open the app
- API base: http://localhost:8080/api
- Filament admin: http://localhost:8080/admin (if panel path is default)

## Environment

Docker uses .env.docker (mounted via compose). Key settings:
- DB_HOST=db
- REDIS_HOST=redis
- QUEUE_CONNECTION=redis
- CACHE_STORE=redis
- SESSION_DRIVER=redis

> For production: use a separate .env with real SMTP/DB/Redis credentials and disable debug.

## CSV Format

Expected CSV header:
```csv
full_name,email,phone,house_address,apartment,gas,electricity,heating,territory,water,currency
```
Example row:
```csv
Andrei Popescu,example1@gmail.com,+40720100101,Str. Lalelelor 12,10,120.50,85.20,210.00,32.00,55.10,RON
```

Notes:
- currency: RON or EUR
- numeric fields use dot decimal (120.50). If you need comma decimals, normalize during parsing.

## Import Flow (High Level)

### Overview

1. **Upload CSV (Admin / Filament)**
   - Admin uploads a CSV file using the Filament widget.
   - The file is stored on the configured disk (e.g. `imports`) and a new `imports` record is created with status `queued`.

2. **Queue the import**
   - The application dispatches `ImportJob` to the queue (Redis).
   - The queue worker picks up the job asynchronously.

3. **Process CSV rows (ImportJob)**
   - Job marks the import as `processing`.
   - Reads the CSV from Storage by `stored_path` / `file_path`.
   - Parses rows, normalizes values (trim strings, parse floats, currency).
   - Validates each row (required fields, numeric values, customer match rules).

4. **Persist domain data**
   - For each valid row:
     - Finds or creates a Customer (typically by email/phone).
     - Creates an Invoice (period, currency, due_date, totals).
     - Creates invoice line items (gas, electricity, heating, water, etc.).
     - Generates `invoice_no` and `payment_ref` (if your flow uses them).

5. **Collect per-row errors**
   - For invalid rows:
     - Adds an error entry (row number + reason) to `imports.errors` (JSON/text).
     - Increments `rows_failed`.
   - Valid rows increment `rows_ok`.
   - Total rows increment `rows_total`.

6. **Finalize**
   - If `rows_failed = 0` → status `completed`.
   - If `rows_failed > 0` → status `completed_with_errors`.
   - On unexpected exceptions (file missing, DB failure) → status `failed` + error message.

---

### Suggested Import Status Model (optional)

- `queued` — created and waiting for queue worker
- `processing` — job started processing
- `completed` — processed successfully, no row errors
- `completed_with_errors` — processed, but some rows failed validation
- `failed` — job crashed or could not finish

Fields typically tracked:
- `rows_total`, `rows_ok`, `rows_failed`
- `errors` (JSON/text with per-row errors)
- `started_at`, `finished_at` (optional, useful for monitoring)

---

### Notes / Best Practices

- Store file paths as **relative** paths in DB (so `Storage::disk()->exists()` works).
- Dispatch jobs using **IDs** (e.g. `ImportJob::dispatch($import->id)`) instead of full Eloquent models.
- Do not fail the entire job on a single bad row; collect row-level errors for better UX.

## Invoice Generation Flow

### Overview

1. **Trigger invoice PDF generation**
   - PDF generation can be triggered:
     - automatically after a successful import (recommended), or
     - manually from Filament (e.g., “Generate PDF”), or
     - via an API endpoint (admin-only).
   - The application dispatches `InvoiceJob` to the queue (Redis) to offload PDF/QR work from the request cycle.

2. **Load invoice + required relations**
   - `InvoiceJob` loads everything needed to render the PDF template:
     - `invoice` (core fields: `invoice_no`, `period`, `currency`, `due_date`, totals)
     - `customer` (name, address, contact)
     - `items` (line items: gas/electricity/heating/water/etc.)
     - optional `import` reference (origin of invoice)

3. **Build payment reference and billing details**
   - The job prepares billing details from configuration:
     - Company name, IBAN, address, VAT (if applicable)
   - The job determines the payment reference:
     - `payment_ref` stored on the invoice (recommended), or
     - a derived value (fallback) if your design does not store it.

4. **Generate QR code payload**
   - Job assembles a QR payload string from billing + invoice data, typically including:
     - **IBAN**
     - **AMOUNT** (invoice total, formatted to 2 decimals)
     - **CURRENCY** (`RON` or `EUR`)
     - **REFERENCE** (payment reference / paymentRef)
     - **BENEFICIARY** (company name)
   - Generates a PNG QR code and converts it to Base64 so it can be embedded in the PDF template.

5. **Render PDF invoice**
   - Uses a PDF engine (e.g., DomPDF) to render `resources/views/pdf/invoice.blade.php` with:
     - `invoice`
     - `billing` (config DTO/array)
     - `paymentRef`
     - `qrBase64`
   - The output is a binary PDF buffer.

6. **Store PDF in Storage**
   - The job writes the PDF to a stable path, usually namespaced by customer and period:
     - Example: `2025-12/invoice-INV-2025-12-000001.pdf`
   - Best practice: store **relative** path in the database (e.g. `pdf_path`), not absolute filesystem paths.

7. **Persist PDF metadata on the invoice**
   - Saves:
     - `pdf_path`
     - optionally `pdf_generated_at`
     - optionally a checksum/hash if you want immutability guarantees

---

### Suggested Invoice Status Model (optional)

Keep statuses strict (finite list), and treat "overdue" as a computed state:

- `draft` — created, not issued
- `issued` — issued to customer (`issued_at` filled)
- `sent` — sent to customer (`sent_at` filled)
- `paid` — paid (`paid_at` filled)
- `canceled` — canceled/voided

Computed:
- **overdue** if `due_date < today` and status in (`issued`, `sent`)

Recommended timestamps:
- `issued_at`, `sent_at`, `paid_at`
- `due_date` (for overdue computation)

---

### PDF Access (Open / Download)

Typical approach is to provide two endpoints:

- **Open in browser (inline)**:
  - `Content-Disposition: inline`
- **Download (attachment)**:
  - `Content-Disposition: attachment`

Both endpoints should be protected with:
- `auth:sanctum` (or session auth for Filament)
- `Policy` checks (`can:view,invoice`) to ensure customers can only access their own PDFs

---

### Best Practices

- Dispatch the job using IDs:
  - `InvoiceJob::dispatch($invoice->id)` (avoid serializing full models in queue payload).
- Do not regenerate PDF on every request; generate once and cache via Storage.
- Use signed URLs or policy middleware for secure PDF access.
- Ensure totals and currency formatting are consistent (`number_format($total, 2, '.', '')`).

## API Authentication (Sanctum Bearer Token)

This project uses Laravel Sanctum personal access tokens for API authentication.

### Login

**Endpoint:** `POST /api/auth/login`

**Body:**
```json
{
  "email": "admin@example.com",
  "password": "secret"
}
```
**Response**
```
{
  "data": {
    "token": "YOUR_TOKEN_HERE",
    "token_type": "Bearer"
  }
}
```
Usage:
Include the token in every request:
```code
Authorization: Bearer YOUR_TOKEN_HERE
```

### Current user

**Endpoint:** `GET /api/auth/me`

Returns basic user info for the authenticated token.

**Headers:**
`Authorization: Bearer YOUR_TOKEN_HERE
Accept: application/json`

**Example response:**
```json
{
  "id": 1,
  "email": "admin@example.com",
  "name": "Admin User"
}
```
### Logout

**Endpoint:** `POST /api/auth/logout`

Revokes the current token.

**Headers:**
`Authorization: Bearer YOUR_TOKEN_HERE
Accept: application/json
`
**Example response:**
`{
  "status": "successful",
  "code": 200
}`
### Authorization

All protected API endpoints require a Sanctum **Bearer token**.

#### Required headers

```http
Authorization: Bearer YOUR_TOKEN_HERE
Accept: application/json
```
- Authorization: Bearer ... — the access token returned by POST /api/auth/login.
- Accept: application/json — ensures Laravel returns JSON errors instead of HTML.

  **Example (cUrl)**
  ```bash
  curl -X GET "http://localhost:8080/api/auth/me" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
  ```
> Notes
> - If the Authorization header is missing or invalid, the API responds with 401 Unauthorized.
> - If the token is valid but lacks the required ability, the API responds with 403 Forbidden.

## Access Control

This project uses a **two-layer authorization model**:

1. **Token abilities (scopes)** — validates that the API token is allowed to call a specific endpoint.
2. **Policies** — validates that the authenticated user is allowed to access a specific record (ownership checks).

### Why both layers?
- Abilities are ideal for API tokens (read-only tokens, integration tokens).
- Policies are required for correct data isolation (ownership checks).

## API Endpoints
> Adjust endpoints to match your actual routes if you renamed controllers or prefixes.

### Auth

| Method | Endpoint | Description |
|---|---|---|
| POST | `/api/auth/login` | Get bearer token |
| GET | `/api/auth/me` | Current user |
| POST | `/api/auth/logout` | Revoke current token |

### Imports

| Method | Endpoint | Access | Description |
|---|---|---|---|
| GET | `/api/imports` | Admin | List imports |
| GET | `/api/imports/{import}` | Admin | Show import details |
| POST | `/api/imports` | Admin | Create import / upload CSV |
| POST | `/api/imports/{import}/retry` | Admin | Retry import (optional) |

### Invoices

| Method | Endpoint | Access | Description |
|---|---|---|---|
| GET | `/api/invoices` | Admin | List invoices |
| GET | `/api/invoices/{invoice}` | Admin/Customer* | Show invoice (policy enforced) |
| GET | `/api/invoices/{invoice}/pdf` | Admin/Customer* | Open PDF (policy enforced) |
| GET | `/api/invoices/{invoice}/pdf/download` | Admin/Customer* | Download PDF (policy enforced) |
| POST | `/api/invoices/{invoice}/issue` | Admin | Set status to issued |
| POST | `/api/invoices/{invoice}/mark-paid` | Admin | Mark invoice paid |

\* Customer access is allowed only for invoices owned by the customer (via Policy).

### Customers

| Method | Endpoint | Access | Description |
|---|---|---|---|
| GET | `/api/customers` | Admin | List customers |
| GET | `/api/customers/{customer}` | Admin | Show customer |
| POST | `/api/customers` | Admin | Create customer |
| PUT/PATCH | `/api/customers/{customer}` | Admin | Update customer |
| DELETE | `/api/customers/{customer}` | Admin | Delete customer |

## Filament Admin Panel

Filament provides a ready-to-use admin interface for managing the application data and operational workflows.

### Included Resources

- **Users**
  - Create/edit users
  - Assign roles (Admin/Customer)
- **Customers**
  - View customer profile and contact data
  - View related invoices/imports (via relation managers)
- **Imports**
  - Upload CSV via widget/action
  - Track processing status, totals, and errors
- **Invoices**
  - View invoice details and line items
  - Open/download PDF
  - Status actions (issue / mark paid)
  - Quick statistics widgets (sent/paid/overdue)

---

### Panel Entry URL

Default (typical) Filament panel URL:

- `/dashboard`

> If you configured a custom panel path, replace `/dashboard` accordingly.

---

### How to Create Resources

Generate Filament resources for your models:

```bash
php artisan make:filament-resource User
php artisan make:filament-resource Customer
php artisan make:filament-resource Import
php artisan make:filament-resource Invoice
```

Queue & Scheduler

This project uses:
	•	Redis as queue backend
	•	A dedicated queue worker container
	•	A dedicated scheduler container to run schedule:run every minute

Queue worker

Example worker command:

php artisan queue:work --sleep=1 --tries=3 --timeout=300

Useful commands:

php artisan queue:failed
php artisan queue:retry all
php artisan queue:flush
php artisan queue:restart

Scheduler

The scheduler container executes:

php artisan schedule:run

Testing

Run tests inside the PHP container:

```bash
docker compose exec php php artisan test
```
⸻


## Troubleshooting
### 502 Bad Gateway (Nginx → PHP-FPM)
- Check docker compose logs -f nginx and docker compose logs -f php
- Ensure www.conf allows connections from docker network
- Ensure Nginx fastcgi_pass php:9000 matches the php service name and port

### Redis MISCONF (stop-writes-on-bgsave-error)
- Redis cannot write RDB snapshot (disk full / permissions). Fix disk space or disable snapshots for dev.

### Queue “old code”
- Restart worker and clear caches:
```bash
docker compose restart queue
docker compose exec php php artisan optimize:clear
docker compose exec php php artisan queue:restart
```

License

MIT
⸻
Author

Dmytro Orlov
PHP / Laravel Backend Engineer
