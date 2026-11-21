 <p align="center">
  <a href="https://github.com/rayusamBoy/skolaria">
    <img src="public/images/icons/skolaria-wordmark-horizontal.svg" height="110" alt="Skolaria">
  </a>
</p>

<div align="center">

# Skolaria

## A Multi-School Management Platform

**One platform. Multiple schools. Centralized management.**

[![Issues](https://img.shields.io/github/issues/rayusamBoy/skolaria?style=flat-square)](https://github.com/rayusamBoy/skolaria/issues)
[![Contributors](https://img.shields.io/github/contributors/rayusamBoy/skolaria?style=flat-square)](https://github.com/rayusamBoy/skolaria/graphs/contributors)
[![Last Commit](https://img.shields.io/github/last-commit/rayusamBoy/skolaria?style=flat-square)](https://github.com/rayusamBoy/skolaria/commits)
[![PHP](https://img.shields.io/badge/PHP-8.4+-777BB4?style=flat-square&logo=php&logoColor=white)](https://www.php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com/)
[![License](https://img.shields.io/github/license/rayusamBoy/skolaria?style=flat-square)](LICENSE)

[Website](https://skolaria.co.tz) · [Documentation](#documentation) · [Report an Issue](https://github.com/rayusamBoy/skolaria/issues)

</div>

---

## About Skolaria

**Skolaria** is a multi-tenant, Progressive Web App (PWA) School Management System built with Laravel. It provides a unified platform for managing academic and administrative operations across independent schools and multi-branch educational organizations.

Unlike conventional school management applications designed around a single institution, Skolaria supports organizations that manage multiple schools through a centralized application while maintaining independent school environments.

Its architecture combines:

- Centralized organization and school management.
- Independent tenant environments.
- Multiple tenant identification strategies.
- Cross-school master data synchronization.
- Role-based access control and permission management.
- Academic and administrative management tools.

Skolaria is designed to reduce repetitive administrative work, standardize academic configurations, and simplify the management of multiple educational institutions.

## Key Capabilities

### Multi-Tenancy and Multi-School Management

Skolaria supports two tenant identification strategies:

#### Domain-based identification

Each school can be accessed through its own domain or subdomain.

```text
school1.example.com
school2.example.com
school3.example.com
```

#### Request-data identification

Multiple schools can be accessed through a single application URL. The active school is determined using request data; a tenant cookie.

```text
https://school.example.com
```

This approach is particularly useful for organizations operating multiple school branches.

### Centralized School Administration

The central application provides organization-level management capabilities, including:

- School and tenant registration.
- Tenant management.
- Central user management.
- Application settings.
- Central support ticket management.
- Administrative operations.
- Cross-school master data synchronization.

### Master Data Synchronization

Managing academic configurations independently across multiple schools can introduce inconsistencies and unnecessary administrative work.

Skolaria provides a master data synchronization service that allows administrators to select a source school, choose target schools, and synchronize selected academic configurations.

Supported academic structures include:

- Classes
- Sections
- Subjects
- Grades
- Divisions
- Examination categories

#### Example

An organization operating ten schools can configure its grading system in the main school and synchronize the configuration across the other nine schools.

Skolaria also supports master data locking through `locked_master_tables`, helping prevent local administrators from unintentionally changing standardized configurations.

### Academic Management

The tenant application provides academic management functionality, including:

- Student records and profiles.
- Student enrollment and batch import.
- Class and section management.
- Subject management.
- Examination management.
- Assessment and marks management.
- Student promotion and graduation.
- Timetable management.
- Student identification cards.
- Academic statistics and reporting.

### Administrative Management

Additional functionality includes:

- User and staff management.
- User invitations and registration approval.
- Notices and announcements.
- Messaging and communication.
- Payment records, invoices and receipts.
- Library book management and requests.
- Support tickets.
- Student records and administrative reporting.

### Application Experience

- Progressive Web App capabilities.
- Installable application experience.
- Light and dark appearance modes.
- Multiple theme skins.
- Notifications and messaging.
- Laravel Reverb broadcasting support.
- Firebase Cloud Messaging integration.
- Responsive web interface.

---

## Technology Stack

Skolaria is built using the Laravel ecosystem and complementary frontend and infrastructure packages.

| Component | Technology |
| --- | --- |
| Backend framework | Laravel 13 |
| Programming language | PHP 8.4+ |
| Frontend framework | Bootstrap 4 |
| JavaScript | jQuery 3 |
| CSS preprocessing | Sass |
| Frontend build tool | Vite |
| Multi-tenancy | Tenancy for Laravel v4 |
| Authorization | laravel-permission-kit and Spatie Laravel Permission |
| Authentication | Laravel UI |
| API authentication | Laravel Sanctum |
| Real-time broadcasting | Laravel Reverb |
| Push notifications | Firebase Cloud Messaging |
| Database | SQLite, MySQL or PostgreSQL |
| Reporting and exports | Laravel Excel |
| PDF generation | DomPDF |
| Testing | Pest and PHPUnit |

---

## Architecture

### Central and Tenant Applications

Skolaria separates organization-level functionality from school-level operations.

#### Central application

The central application manages the overall organization and its schools.

Responsibilities include:

- Tenant registration and management.
- Central users and administration.
- Organization-level settings.
- Master data synchronization.
- Central support operations.

#### Tenant application

Each tenant represents an independent school or institution.

Responsibilities include:

- Student and staff management.
- Academic administration.
- Examinations and assessments.
- Timetables and schedules.
- Payments and financial records.
- School communication.
- Reports and statistics.

### Database Architecture

Skolaria uses the Tenancy for Laravel package to manage tenant initialization and database connections.

The configuration is maintained in:

```text
config/tenancy.php
```

The application supports a central database and separately managed tenant databases, with tenant database naming controlled through configuration.

### Tenant Identification

The default identification mode is configurable through:

```env
TENANCY_IDENTIFICATION_MODE=domain
```

For request-data identification:

```env
TENANCY_IDENTIFICATION_MODE=request_data
```

Request-data identification supports:

- `tenant` cookie.

The selected identification strategy is configured in `config/tenancy.php`.

### Tenant-Aware Services

Skolaria configures tenancy bootstrappers for several Laravel services, including:

- Database connections.
- Cache.
- Filesystem.
- Queues.
- Database-backed sessions.

This allows tenant context to be applied across supported application services.

### School Switching and Impersonation

Skolaria includes tenant impersonation functionality for administrators who need to access individual school environments.

The implementation uses the tenancy package's impersonation functionality and application-level tenant switching.

Impersonation availability is configurable:

```env
TENANCY_ALLOW_USER_IMPERSONATION=false
```

Use appropriate security controls and trusted authorization checks before enabling impersonation in production.

---

## Permission and Authorization

Skolaria integrates a custom Laravel authorization package, **[laravel-permission-kit](packages/laravel-permission-kit)**.

The package is maintained within the Skolaria repository and is currently in its initial development stage.

It provides the permission generation, synchronization, and authorization functionality used by the application.

The package is publicly available as source code within this repository but has not yet been independently published on Packagist.

A standalone Composer release will be considered once the package has matured and is ready for wider adoption.

For package-specific information, development instructions, and usage details, see the [Permission Kit README](packages/laravel-permission-kit/README.md).

The package currently uses a local Composer path repository:

```json
{
    "type": "path",
    "url": "packages/laravel-permission-kit"
}
```

---

## Screenshots

### Central Application

<p align="center">
  <img src="public/images/screenshots/non_tenancy/manage-tenants-dark.png" alt="Central tenant management" width="48%">
  <img src="public/images/screenshots/non_tenancy/system-settings-light.png" alt="Central application settings" width="48%">
</p>

### Tenant Application

<p align="center">
  <img src="public/images/screenshots/tenancy/superadmin-dashboard-dark.png" alt="Tenant administrator dashboard" width="48%">
  <img src="public/images/screenshots/tenancy/teacher-batch-marks-dark.png" alt="Teacher batch marks entry" width="48%">
</p>

---

## Requirements

The following software is required for local development.

| Dependency | Minimum version |
| --- | --- |
| PHP | 8.4 |
| Composer | 2.9.1 |
| Node.js | Compatible with npm 11.6.1 |
| npm | 11.6.1 |
| Database | SQLite, MySQL or PostgreSQL |

### PHP Extensions

Ensure the following PHP extensions are enabled:

- Ctype
- cURL
- DOM
- Fileinfo
- Filter
- Hash
- Mbstring
- OpenSSL
- PCRE
- PDO
- Session
- Tokenizer
- XML

---

## Installation

### 1. Clone the repository

```bash
git clone https://github.com/rayusamBoy/skolaria.git
cd skolaria
```

### 2. Install PHP dependencies

```bash
composer install
```

The repository includes `laravel-permission-kit` as a local Composer path dependency. Keep the package directory in place when installing dependencies.

### 3. Install frontend dependencies

```bash
npm install
```

### 4. Build frontend assets

```bash
npm run build
```

For frontend development:

```bash
npm run dev
```

### 5. Configure the environment

Create your environment file:

```bash
cp .env.example .env
```

Generate the application key:

```bash
php artisan key:generate
```

Create the storage symbolic link:

```bash
php artisan storage:link
```

### 6. Configure the database

Skolaria supports SQLite, MySQL and PostgreSQL.

For the default SQLite development configuration, prepare the database:

```bash
php artisan database:ensure-sqlite
```

For MySQL or PostgreSQL, create the database and configure the appropriate connection settings in `.env`.

Review the tenant database configuration in:

```text
config/tenancy.php
```

### 7. Run migrations and seed central data

Run the central migrations:

```bash
php artisan migrate
```

Seed the central application:

```bash
php artisan db:seed --class=DatabaseSeederNonTenancy
```

Tenant database migrations and seeders should be executed through the configured tenancy workflow.

### 8. Start the application

```bash
php artisan serve
```

Open:

```text
http://localhost:8000
```

> **Important:** The central and tenant applications have different responsibilities. Complete tenant creation and initialization through the central application before accessing the tenant environment.

---

## Environment Configuration

Some important environment settings are listed below.

| Variable | Description |
| --- | --- |
| `APP_NAME` | Application name |
| `APP_URL` | Application base URL |
| `APP_ENV` | Application environment |
| `APP_DEBUG` | Debug mode; disable in production |
| `DB_CONNECTION` | Central database connection |
| `DB_DATABASE` | Central database name or SQLite path |
| `BROADCAST_CONNECTION` | Broadcasting driver |
| `QUEUE_CONNECTION` | Queue driver |
| `CACHE_STORE` | Cache driver |
| `SESSION_DRIVER` | Session storage driver |
| `FILESYSTEM_DISK` | Default filesystem disk |
| `TENANCY_IDENTIFICATION_MODE` | Tenant identification strategy |
| `TENANT_DATABASE_PREFIX` | Tenant database prefix |
| `TENANT_DATABASE_SUFFIX` | Tenant database suffix |
| `TENANCY_ALLOW_USER_IMPERSONATION` | Controls impersonation availability |
| `VITE_VAPID_KEY` | Public push notification key |

Refer to `.env.example` for the complete list of application configuration variables.

Never commit actual credentials, private keys, production passwords or service-account files to the repository.

---

## Notifications and Real-Time Communication

### Firebase Push Notifications

Firebase Cloud Messaging (FCM) provides optional push notification functionality.

To enable Firebase push notifications:

1. Set `VITE_VAPID_KEY` in your `.env` file.
2. Configure your Firebase project settings in `public/assets/js/firebase-config.js`.
3. Add your Firebase service account credentials to:

   `storage/app/firebase/service-account-credentials.json`

Firebase configuration and service-account credentials are required for push notifications to work. Keep the service-account credentials outside the public web directory and never commit them to Git.

Firebase integration is optional. Skolaria works without Firebase push notifications.

After configuring Firebase, rebuild the frontend assets:

```bash
npm run build
```

### Laravel Reverb and Pusher

Skolaria supports real-time communication through Laravel broadcasting. You can use either **Laravel Reverb** or **Pusher**.

The broadcasting driver is selected automatically based on the `BROADCAST_CONNECTION` setting in your `.env` file. You do not need to manually edit the frontend broadcasting imports.

#### Option 1: Laravel Reverb (default)

```env
BROADCAST_CONNECTION=reverb
VITE_BROADCAST_CONNECTION="${BROADCAST_CONNECTION}"
```

Configure your Reverb credentials in `.env` and start the Reverb server:

```bash
php artisan reverb:start
```

#### Option 2: Pusher

```env
BROADCAST_CONNECTION=pusher
VITE_BROADCAST_CONNECTION="${BROADCAST_CONNECTION}"
```

Configure the appropriate `PUSHER_*` environment variables and activate the Pusher credentials in:

`resources/js/pusher.js`

#### How driver selection works

The application reads `VITE_BROADCAST_CONNECTION` and automatically loads the corresponding frontend broadcasting configuration:

- `reverb` loads `resources/js/reverb.js`.
- `pusher` loads `resources/js/pusher.js`.

The `VITE_BROADCAST_CONNECTION` value mirrors `BROADCAST_CONNECTION`, so changing the broadcasting driver in `.env` automatically selects the matching frontend configuration.

After changing the broadcasting configuration, rebuild the frontend assets:

```bash
npm run build
```

**Note:** Reverb and Pusher are alternative broadcasting drivers. Only one is initialized at a time.

---

## Progressive Web App (PWA)

Skolaria provides Progressive Web App functionality, allowing users to install the application and access previously visited pages when an internet connection becomes unavailable.

### Offline Access

When users allow offline caching, previously visited pages can be cached and accessed again even when the network connection is interrupted.

Offline behavior includes:

- Accessing previously cached pages.
- Viewing available cached content without an active internet connection.
- Detecting offline connectivity.
- Disabling form submission and other server-dependent actions while offline.

For data integrity, submission buttons are automatically disabled (grayed out) when the application detects that the user is offline.

**Important:** Offline access is intended for viewing previously cached content. Creating, updating, or submitting records requires an active internet connection.

Pages that have not previously been cached may not be available offline. The offline experience also depends on browser caching policies and available device storage.

---

## Development and Testing

Contributions and development improvements are welcome.

The repository contains application tests under:

```text
tests/
├── Feature/
└── Unit/
```

Run the test suite:

```bash
php artisan test
```

Run Laravel code formatting:

```bash
vendor/bin/pint
```

For the permission package, its development dependencies and test configuration are maintained separately under:

```text
packages/laravel-permission-kit/
```

---

## Contributing

Contributions are welcome.

If you are interested in improving Skolaria, reporting bugs or proposing new functionality, please start by reviewing:

- [Contributing Guidelines](CONTRIBUTING.md)
- [Code of Conduct](CODE_OF_CONDUCT.md)
- [Security Policy](SECURITY.md)

For significant changes, consider opening an issue first to discuss the proposed implementation.

---

## Security

Security is particularly important in a multi-tenant application.

If you discover a vulnerability, please follow the instructions in [SECURITY.md](SECURITY.md) rather than publishing sensitive vulnerability details in a public issue.

Never use production credentials or real student information in development or testing environments.

---

## Acknowledgements

Skolaria builds upon the work of the open-source community and the Laravel ecosystem.

This project is based on the original `lav_sms` project by [4jean](https://github.com/4jean).

Special thanks to 4jean, the developers of the libraries and frameworks used in this project, and everyone who has contributed to its development.

---

## License

Skolaria is open-source software licensed under the **GNU Affero General Public License v3.0 (AGPL-3.0)**.

See the [LICENSE](LICENSE) file for the complete license terms.

---

<div align="center">

**Skolaria – One platform. Multiple schools. Centralized management.**

[GitHub Repository](https://github.com/rayusamBoy/skolaria) · [Project Website](https://skolaria.co.tz)

</div>
