# Geteman — Vehicle Gate Management System

A responsive web application for managing vehicle gate-in and gate-out operations, built on **Laravel 13** with a **Filament v5** admin panel and **Microsoft SQL Server** as the database backend.

Designed to run cleanly on both desktop and mobile, since gate operators frequently work from a tablet or phone at the gate itself rather than a desk.

---

## Features

### 🔐 Authentication
- Secure login via Filament's built-in auth panel.
- Every login creates a database-backed session record (`sessions` table), tied to the authenticated user.
- Gate In and Gate Out pages are protected — unauthenticated visitors are redirected to the login page.

### 🚗 Vehicle Gate In
- **Vehicle Number** — searchable dropdown, with the option to register a new vehicle inline if it's not yet in the system.
- **Driver Name** — searchable dropdown, same inline-creation support.
- **Driver ID** and **Phone Number** auto-populate the moment a driver is selected, pulled from the driver's saved record.
- **Date & Time In** and the **creating user** are captured automatically — not editable by the operator.
- A vehicle that is already gated in cannot be gated in a second time; the same vehicle can be gated in again freely once it has been gated out.

### 🚙 Vehicle Gate Out
- The **Vehicle Number** dropdown only lists vehicles that are *currently* gated in — vehicles already gated out, or never gated in, don't appear.
- Selecting a vehicle auto-populates **Driver Name**, **Driver ID**, and **Phone Number** from that vehicle's open gate log — no manual re-entry.
- **Date & Time Out** and the **gating-out user** are captured automatically.
- A gate log can only be gated out once; re-selecting an already-closed log has no effect on its original exit record.

### 📊 Dashboard
- Live stat cards: vehicles currently on-site, total gate-ins today, total gate-outs today.
- A searchable, paginated table of every vehicle currently on-site, with driver details and who gated them in.

---

## Tech Stack

| Layer          | Technology                          |
|----------------|--------------------------------------|
| Framework      | Laravel 13                          |
| Admin/UI       | Filament v5.7                       |
| Database       | Microsoft SQL Server (via `sqlsrv`) |
| Testing        | Pest, with Livewire testing helpers |
| Frontend       | Filament's built-in Blade + Livewire stack |

---

## Data Model

Three core tables drive the whole system:

- **`vehicles`** — `vehicle_number` (unique), make, model, colour.
- **`drivers`** — `name`, `driver_id` (unique), `phone_number`.
- **`gate_logs`** — one row per visit. `vehicle_id`, `driver_id`, `time_in`, `time_out` (nullable), `gated_in_by`, `gated_out_by`.



## Getting Started

### Prerequisites
- PHP 8.2+
- Composer
- Microsoft SQL Server (Express is fine for local dev) with the **ODBC Driver 17 for SQL Server** and the PHP `sqlsrv` / `pdo_sqlsrv` extensions enabled
- Node.js (for asset building, if you extend the frontend)

### Installation

```bash
cd geteman-app
composer install
cp .env.example .env
php artisan key:generate
```

### Database Configuration

In `.env`, set:

```env
DB_CONNECTION=sqlsrv
DB_HOST=127.0.0.1
DB_PORT=1433
DB_DATABASE=gateman
DB_USERNAME=your_sql_user
DB_PASSWORD=your_sql_password
```



### Migrate & Install Filament

```bash
php artisan migrate
php artisan filament:install --panels
php artisan make:filament-user
```

### Run

```bash
php artisan serve
```

Visit `http://localhost:8000/admin` and log in with the user you just created. Gate In and Gate Out appear in the sidebar navigation.

---

## Testing

The test suite covers authentication/session handling, both gate pages, model relationships, the "currently gated in" availability logic, and the dashboard widgets — using Pest with Livewire's component-testing helpers.

Run the full suite:

```bash
php artisan test
```

Or with Pest directly:

```bash
./vendor/bin/pest
```

### What's covered

| Test file                       | Covers |
|----------------------------------|--------|
| `AuthenticationTest`             | Login success/failure, session record creation, guest redirects on protected pages |
| `GateInTest`                     | Field validation, driver auto-populate, record creation with auto-captured time/user, duplicate gate-in prevention, re-gating after gate-out, form reset on success |
| `GateOutTest`                    | Field validation, driver auto-populate from the open log, auto-captured time-out/user, idempotency (no re-processing a closed log), form reset on success |
| `GateLogRelationsTest`           | Vehicle/driver/user relationships, Carbon casting on timestamps, cascading deletes |
| `GateLogAvailabilityTest`        | `whereNull('time_out')` filtering, `Vehicle::currentlyGatedIn()` scope correctness across multiple visit histories |
| `GateStatsOverviewTest`          | Dashboard stat accuracy (currently-in / today's in / today's out counts) |
| `CurrentlyGatedInTableTest`      | Table widget rendering, correct filtering to open logs only, search by vehicle number and driver name |

---

## Project Structure (key files)

```
app/
  Models/
    Vehicle.php
    Driver.php
    GateLog.php
  Filament/
    Pages/
      GateIn.php
      GateOut.php
    Widgets/
      GateStatsOverview.php
      CurrentlyGatedInTable.php
database/
  migrations/
    xxxx_create_vehicles_table.php
    xxxx_create_drivers_table.php
    xxxx_create_gate_logs_table.php
resources/
  views/filament/pages/
    gate-in.blade.php
    gate-out.blade.php
tests/
  Feature/
    AuthenticationTest.php
    GateInTest.php
    GateOutTest.php
    GateLogRelationsTest.php
    GateLogAvailabilityTest.php
    GateStatsOverviewTest.php
    CurrentlyGatedInTableTest.php
```



