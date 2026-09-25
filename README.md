# NOBAT — Salon Appointment Platform

**NOBAT** is a Laravel-based salon appointment platform for discovering salons, viewing services and specialists, checking available time slots, and managing bookings from dedicated dashboards.

**Project URL:** http://171.22.26.184:8081/

## What NOBAT does

NOBAT separates the experience into three main roles:

- **Customer:** discover salons, open a public salon page, choose a service/barber, view availability, book appointments, manage bookings and favorites.
- **Salon Owner:** manage salon information, services, barbers, working hours, availability, bookings, manual walk-in bookings, dashboard metrics, and public salon presentation.
- **Super Admin:** create and edit salons, manage owner accounts, activate/deactivate salons, inspect salon details, and oversee platform-level salon data.

## Main product areas

### Discover
The Discover experience is the public entry point for customers. It provides salon discovery, search, popular salon presentation, and a responsive hero with the salon visual on the left and the Persian search/content area on the right on desktop.

### Public Salon
Each salon has a public page with branding, cover and logo, services and pricing, specialist/barber information, gallery and media lightbox, working-hours/status presentation, online booking flow, and responsive mobile presentation.

Uploaded gallery media is constrained to the browser viewport when opened in the lightbox, so large source images do not force the page beyond the screen.

### Booking
Customer bookings and salon-owner manual bookings use the same availability logic so confirmed bookings block the corresponding time slot.

Manual booking supports walk-in customers without creating a NOBAT customer account and includes customer name, phone, barber, service, date, time, confirmation and notes.

### Working Hours
Working hours can be defined at salon level and, where configured, per barber. Barber-specific schedules override the salon-wide fallback for that barber, including custom closed days.

### Authentication & Session UX
Customers have an accessible logout action and a customer-specific inactivity timeout. The current default is **30 minutes of inactivity**.

Salon-owner and Super Admin authentication remain role-scoped.

## Responsive UX
The project uses responsive layouts across customer, public salon, salon-owner and Super Admin surfaces.

Recent UX hardening includes mobile-friendly salon-owner workspace, responsive manual booking, compact booking detail metadata on mobile, viewport-safe public salon media viewing, responsive Super Admin dashboard, consistent navigation and feedback states, safer back navigation, and role-aware entry points.

## Technical stack
- Laravel / PHP
- Blade
- Alpine.js
- Tailwind-style utility classes plus project CSS systems
- Vite
- relational database with Laravel migrations and seeders
- storage-backed salon/media assets

## Local development
```bash
git clone https://github.com/MREZA-MJDi/nobatdehi.git
cd nobatdehi

composer install
npm install

cp .env.example .env
php artisan key:generate

php artisan migrate

npm run dev
php artisan serve
```

## Production / server deployment
The current test server is:
`http://171.22.26.184:8081/`

Typical update flow:
```bash
cd /var/www/nobatdehi-test
git checkout main
git pull --ff-only origin main
npm run build
php artisan migrate --force
php artisan optimize:clear
```

## Notes
The repository intentionally keeps feature fixes scoped where possible. UI changes should avoid unrelated role or page regressions.

Do not use destructive seed/reset commands against a production database unless the intended dataset and environment have been explicitly verified.

## Repository
GitHub: https://github.com/MREZA-MJDi/nobatdehi
