# NOBAT — Salon Appointment Platform

NOBAT is a Laravel platform for salon discovery and appointment management. It separates customer booking, salon-owner operations, and platform administration. Public salon pages can present services, prices, specialists, working hours, branding, and gallery media. Owner workflows include schedule and booking management; availability and booking behavior should be validated against the current tests before production use.

## Technology
- PHP `^8.2`, Laravel `^12.0`
- Blade, Alpine.js, Vite and project CSS
- Laravel database migrations and Eloquent
- QR-code support through `f9webltd/simple-qrcode`
- Jalali date helper in `app/Helpers/Jalali.php`

## Requirements
PHP 8.2+, Composer, Node.js/npm, and a Laravel-supported database. PHP extensions required by Laravel and Composer dependencies must be enabled.

## Local installation
```bash
git clone https://github.com/MREZA-MJDi/nobatdehi.git
cd nobatdehi
composer install
```

Copy `.env.example` to `.env` (`copy .env.example .env` in Windows CMD; `cp .env.example .env` on macOS/Linux), create a local database, and configure `DB_*` values. Review other environment variables for mail, storage, and deployment-specific services.

```bash
php artisan key:generate
php artisan migrate
npm install
npm run build
php artisan storage:link
php artisan serve
```

Open `http://127.0.0.1:8000`. For frontend hot reload, run `npm run dev` in a second terminal.

## Tests
```bash
php artisan test
```

## Booking and production safety
Verify working-hours rules, slot availability, cancellation/status transitions, and concurrent booking behavior in tests before production release. Use a disposable local database for any reset operation. Do not commit `.env`, credentials, or customer information.

## Links
- Repository: https://github.com/MREZA-MJDi/nobatdehi
- Laravel documentation: https://laravel.com/docs/12.x
