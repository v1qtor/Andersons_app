# Andersons — Household Management App

A Laravel + Livewire app for managing a busy household: trip planning with
live route maps, a shared meal schedule, staff/expense tracking, and
role-based access for family members, staff, and household admins.

> [!NOTE]
> Built with **Livewire 3** (via Flux UI) — not the newer Livewire 4.

**Live demo:** [andersons.vcee.be](https://www.andersons.vcee.be/) — see
[Demo credentials](#demo-credentials) below to log in.

## Documentation

Full project documentation (UML diagrams, etc.) lives in [`/docs`](docs).

## Features

- **Trips** — plan trips with a route of checkpoints, live address/route
  previews on an embedded Google Map, document/permit attachments, and
  per-checkpoint arrival photos.
- **Checkpoints library** — a reusable, geocoded set of named locations
  shared across trips, organized into folders.
- **Meals** — meal planning and a shared meal schedule with guest RSVPs.
- **Invoices & receipts** — expense tracking with receipt uploads, paid/unpaid
  status, and role-gated visibility.
- **Schedule** — a household calendar (birthdays, tasks, unavailability).
- **Staff availability** — staff mark themselves unavailable; admins get a
  shortage alert when too many staff are out on the same day.
- **Admin panel** — manage users, birthdays, and household settings.
- **Role-based access** — Household Admin (The Andersons/Admin), Family
  Member, Staff, and Chef each see a different slice of the app, enforced
  through Laravel Policies rather than scattered role checks.
- **Real-time notifications** via Pusher/Laravel Echo.

## Tech stack

Laravel 12 · Livewire 3 (Flux UI) · Tailwind CSS 4 · Pest 4 · Pusher/Echo ·
Google Maps Embed API · Nominatim (OpenStreetMap) geocoding

## Setup

### Windows

```
copy .env.example .env
type nul > database\database.sqlite
composer install
npm install
php artisan key:generate
php artisan migrate --seed
npm run build
php artisan serve
```

### Mac / Linux

```
cp .env.example .env
touch database/database.sqlite
composer install
npm install
php artisan key:generate
php artisan migrate --seed
npm run build
php artisan serve
```

The Google Maps API key (`GOOGLE_MAPS_API_KEY` in `.env`) is optional —
without it, map previews show a graceful "unavailable" placeholder instead
of failing. Geocoding addresses to coordinates uses the free
Nominatim/OpenStreetMap API and needs no key.

### Running tests

```
php artisan test
```

## Demo credentials

All seeded accounts use the password `password`.

| Name                | Email                          | Role           |
|:--------------------|:--------------------------------|:---------------|
| Mr. & Ms. Anderson  | andersons@andersons.com        | The Andersons  |
| Emily Anderson      | emily@andersons.com            | Family Member  |
| James Anderson      | james@andersons.com            | Family Member  |
| Sophie Anderson     | sophie@andersons.com           | Family Member  |
| Tom (Gardener)      | tom.gardener@andersons.com     | Staff          |
| Tom (Handyman)      | tom.handyman@andersons.com     | Staff          |
| Mr. Oliver          | oliver@andersons.com           | Chef           |
| Laurien             | laurien@andersons.com          | Admin          |

---

*Originally built as a team project for Thomas More's Skills Integration Lab 2
course (client: Laurien Stroobants). This fork continues it as a solo
portfolio project — see the commit history for the original team's work.*
