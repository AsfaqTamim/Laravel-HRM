# miniHRM

A lightweight HR management system built with Laravel 13 and Filament 3. Handles the full employee lifecycle — attendance, leaves, payroll, projects, tasks, and financial transactions — all inside a clean admin panel.

## Features

- **Employee Management** — Profiles, role assignments, department & designation management
- **Attendance Tracking** — Daily check-in/check-out with break timestamps and office hour enforcement
- **Leave Management** — Leave types, balances, apply/approve/reject workflows, holiday calendar
- **Payroll System** — Salary structures, payroll runs, automated payslip generation with PDF export
- **Project & Task Management** — Kanban-style board, task assignment, member management
- **Financial Accounting** — Chart of accounts, double-entry transactions, balance reporting
- **Role-Based Access** — Spatie permissions + Filament Shield with super_admin, employee, accountant roles
- **Dashboard & Reports** — Role-specific dashboards, attendance/payroll/leave reports, employee directory
- **Email Notifications** — Leave status updates, payslip alerts, task assignments
- **Self-Service Portal** — Employees manage their own attendance, leaves, and payslips

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Framework | Laravel 13 |
| Admin Panel | Filament 3 |
| Database | MariaDB / MySQL |
| Frontend | Tailwind CSS, Alpine.js |
| Auth & Roles | Spatie Permission + Filament Shield |
| PDF | Barryvdh DomPDF |

## Requirements

- PHP 8.2+
- Composer 2
- Node.js 18+ / npm
- MySQL 8+ or MariaDB 10+
- GD or Imagick extension (for PDF rendering)

## Installation

```bash
# Clone the repo
git clone https://github.com/arafat-web/miniHRM.git
cd miniHRM

# Install PHP dependencies
composer install

# Install frontend dependencies
npm install && npm run build

# Environment setup
cp .env.example .env
php artisan key:generate

# Configure your database in .env, then:
php artisan migrate --seed

# Create storage symlink
php artisan storage:link
```

## Default Admin

After seeding, log in with:

- **Email:** admin@admin.com
- **Password:** password

*Change these credentials immediately in production.*

## Running

```bash
php artisan serve
```

or point your web server to the `public/` directory.

## Project Structure

```
app/
├── Filament/
│   ├── Pages/          # Dashboard, profile, reports
│   ├── Resources/      # CRUD resources per entity
│   └── Widgets/        # Dashboard widgets
├── Mail/               # Email notification classes
├── Models/             # Eloquent models
├── Policies/           # Authorization policies
└── Providers/          # Service providers
database/
├── migrations/         # Schema migrations
├── factories/          # Model factories
└── seeders/            # Demo data seeders
tests/
├── Feature/            # Feature tests
├── Unit/               # Unit tests
└── playwright/         # E2E integration tests with Playwright
```

## License

MIT — use it, fork it, ship it.
