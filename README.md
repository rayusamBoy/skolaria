 <p align="center">
  <a href="https://github.com/rayusamBoy/skolaria">
    <img src="public/images/icons/skolaria-wordmark-horizontal.svg" height="110" alt="Skolaria">
  </a>
</p>

<div align="center">

# Skolaria

## Multi-School Management Platform

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

## About

**Skolaria** is a multi-tenant, Progressive Web App (PWA) School Management System built with Laravel.

It provides a centralized platform for managing multiple independent schools while keeping their academic records, users, settings, and operational data isolated.

Skolaria is designed for organizations that operate multiple schools or branches and need centralized administration without sacrificing the independence of individual school environments.

Its architecture combines:

- Centralized school and tenant administration.
- Separate tenant databases.
- Domain-based and request-data tenant identification.
- Tenant provisioning and lifecycle management.
- Centralized academic master-data synchronization.
- Role-based access control and automatic permission management.
- Academic, financial, and administrative management.
- Tenant-aware storage, cache, sessions, and queues.

## Features

### 1. Multi-Tenancy

Skolaria uses [Tenancy for Laravel](https://tenancyforlaravel.com/) to manage independent school environments.

Each school is represented by a tenant with its own database and tenant-specific application context.

The application supports two identification strategies.

#### Domain-based identification

Each school can be accessed through its assigned domain or subdomain.

```text
school1.example.com
school2.example.com
school3.example.com
```

#### Request-data identification

Multiple schools can use a shared application URL. The application determines the active tenant from configured request data.

In the current application configuration, the tenant cookie is used for this identification mode.

```text
https://school.example.com
```

The identification strategy is controlled by:

```env
TENANCY_IDENTIFICATION_MODE=domain
```

Or:

```env
TENANCY_IDENTIFICATION_MODE=request_data
```

### 2. Central Administration

The central application manages the organization and its registered schools.

Central administration includes:

- Tenant registration and management.
- Central user management.
- Tenant database and lifecycle operations.
- Application settings.
- Central support ticket management.
- Central administrative operations.
- Master-data synchronization.
- Tenant impersonation management.
- Central permission and authorization management.

Central routes are separated from tenant routes to avoid route collisions and to maintain distinct application contexts.

### 3. Tenant Provisioning and Lifecycle Management

Tenant creation and deletion are integrated with the tenancy package's event system.

When a tenant is created, the application performs its configured provisioning workflow:

1. Creates the tenant database.
2. Runs tenant migrations.
3. Executes the configured tenant database seeder.
4. Creates tenant storage.

Tenant deletion also triggers application-specific cleanup:

- Deletes associated domains.
- Removes tenant storage.
- Deletes the tenant database.
- Removes tenant-specific student ID-card theme files.

These operations are handled through the tenant lifecycle event pipeline rather than requiring administrators to manually clean up each resource.

### 4. Master Data Synchronization

Skolaria provides a centralized master-data synchronization service.

Administrators can select a source school, choose target schools, and synchronize selected academic configurations.

The synchronization service supports:

- Selecting the source tenant.
- Selecting one or more target tenants.
- Selecting the master-data tables to synchronize.
- Maintaining table dependency order.
- Optionally locking synchronized tables.
- Excluding selected tenants from locking.

Supported master-data tables include:

- Examination categories.
- Class types.
- Classes.
- Sections.
- Grades.
- Divisions.
- Subjects.
- Subject records.
- Examinations.
- Timetable records.
- Time slots.
- Skills.

#### Master-data locking

The application stores lock information on each tenant through:

```text
locked_master_tables
```

Central administrators manage synchronization and lock status from the central context.

Within a tenant context, the application's authorization layer checks the tenant's lock configuration and restricts write permissions for locked master tables while retaining the configured read permissions.

Root-role authorization and the feature-module restrictions are handled separately by the application's Gate authorization logic.

### 5. Academic Management

The tenant application provides tools for managing school academic operations.

Implemented application areas include:

- Student records and profiles.
- Student enrollment and batch registration.
- Student identification cards.
- Class and section management.
- Subject management.
- Examination categories and examinations.
- Assessment management.
- Marks entry and batch marks processing.
- Examination tabulation.
- Grades, divisions, and results.
- Student promotion and graduation.
- Timetables and timetable records.
- Time-slot management.
- Academic statistics and reporting.

### 6. Student and User Management

Tenant administrators can manage school users and student records.

User management includes:

- User accounts and profiles.
- Staff management.
- Student accounts.
- User invitations.
- Registration approval and rejection.
- User blocking and unblocking.
- Password reset operations.
- Account preferences.
- Account security settings.

### 7. Financial and Administrative Management

Skolaria includes administrative tools for managing school operations.

These include:

- Payment records.
- Student invoices.
- Payment status and receipts.
- Notices and announcements.
- Messaging and communication.
- Library books and book requests.
- Dormitory management.
- Support tickets.
- User activity and application logs.

### 8. Support Ticket Management

Skolaria includes a support ticket workflow connecting individual schools with central support administrators.

The workflow supports:

- Ticket creation and replies.
- Ticket categories and labels.
- Ticket assignment.
- Ticket status and property updates.
- Ticket locking and unlocking.
- Ticket archiving and unarchiving.
- Central support administration.

This allows schools to receive support without requiring direct access to the central administration interface.

### 9. Authentication and Account Security

The application uses Laravel UI authentication and includes additional account-security functionality.

Implemented security features include:

- Two-factor authentication.
- Password update enforcement.
- Account recovery functionality.
- Session status and heartbeat endpoints.
- Session reauthentication.
- Browser-session management.
- User impersonation.

User impersonation is configurable:

```env
TENANCY_ALLOW_USER_IMPERSONATION=false
```

When disabled, the application prevents the impersonation initiation workflow.

### 10. Roles and Permissions

Skolaria uses:

- [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission)
- [Laravel Permission Kit](packages/laravel-permission-kit)

The application maintains separate central and tenant permission contexts.

Tenant roles include:

- Super Admin.
- Admin.
- Teacher.
- Parent.
- Student.
- Librarian.
- Accountant.

The authorization design distinguishes root-level access from ordinary role permissions.

Sensitive administrative capabilities are not automatically granted to ordinary tenant administrators.

The application also includes custom authorization rules for:

- Feature-module availability.
- Locked master tables.
- Restricted log deletion operations.
- Root-role authorization.

See [Permission Kit](packages/laravel-permission-kit/README.md) for the package implementation and configuration.

### 11. Progressive Web App

Skolaria provides Progressive Web App functionality.

The application supports an installable experience and offline access to previously cached pages.

Offline functionality is intended primarily for viewing cached content.

Operations requiring communication with the server, including creating and updating records, require an active internet connection.

### 12. Notifications and Real-Time Communication

Skolaria integrates several notification and broadcasting technologies.

| Integration | Purpose |
| --- | --- |
| Laravel Notifications | Application notifications |
| Firebase Cloud Messaging | Optional push notifications |
| Laravel Reverb | Real-time broadcasting |
| Pusher | Alternative broadcasting provider |

Firebase configuration is optional.

Real-time broadcasting can use Reverb or Pusher depending on the application's broadcasting configuration.

---

## Architecture

### Central and Tenant Contexts

Skolaria separates organization-level administration from school-level operations.

| Area | Central context | Tenant context |
| --- | --- | --- |
| Tenant management | Yes | No |
| Central users | Yes | No |
| School users | No | Yes |
| Academic records | Master-data source and synchronization | School-specific records |
| Database | Central database | Tenant database |
| Permissions | Central permissions | Tenant permissions |
| Support | Central ticket administration | School ticket operations |
| Impersonation | Initiates tenant access | Receives impersonated access |

### Database Architecture

Skolaria uses a central database and separately managed tenant databases.

```text
                    Skolaria Application
                            |
                 +----------+----------+
                 |                     |
           Central Context        Tenant Context
                 |                     |
          Central Database       Tenant Database
                 |                     |
          Tenant Records         School Records
          Central Users          School Users
          Central Settings       Academic Data
          Tenant Domains         School Settings
```

The tenancy configuration is maintained in:

```text
config/tenancy.php
```

Tenant database names are controlled through configuration:

```env
TENANT_DATABASE_PREFIX=tenant
TENANT_DATABASE_SUFFIX=
```

The application configures database managers for SQLite, MySQL/MariaDB, PostgreSQL, and SQL Server.

### Tenant-Aware Laravel Services

The application configures tenancy bootstrappers for:

- Database connections.
- Cache.
- Filesystem.
- Queues.
- Database-backed sessions.

Tenant initialization applies the corresponding context to these services. When tenancy ends, the application restores the central context.

### Tenant Storage

Tenant storage is isolated using the tenancy filesystem bootstrapper.

The application uses tenant-specific storage paths for local and public files.

Student ID-card theme directories are also included in tenant lifecycle cleanup.

### Routes

The application separates routes into central, tenant, and universal groups.

```text
routes/
├── web.php
└── tenant/
    ├── web.php
    └── universal.php
```

- `web.php` contains central application routes.
- `tenant/web.php` contains tenant-specific application routes.
- `tenant/universal.php` contains routes shared across central and tenant contexts.

The route configuration is maintained in:

```text
bootstrap/app.php
```

---

## Technology Stack

| Component | Technology |
| --- | --- |
| Backend framework | Laravel 13 |
| Programming language | PHP 8.4+ |
| Frontend | Blade |
| CSS framework | Bootstrap 4 |
| JavaScript | jQuery 3 |
| CSS preprocessing | Sass |
| Frontend build tool | Vite |
| Multi-tenancy | Stancl Tenancy |
| Authentication | Laravel UI |
| Authorization | Laravel Permission Kit |
| Role and permission storage | Spatie Laravel Permission |
| API authentication dependency | Laravel Sanctum |
| Broadcasting | Laravel Reverb / Pusher |
| Push notifications | Firebase Cloud Messaging |
| Spreadsheet processing | Laravel Excel |
| PDF generation | DomPDF |
| Activity logging | Spatie Laravel Activitylog |
| Analytics | Spatie Laravel Analytics |
| PWA | Laravel PWA |
| Testing | Pest / PHPUnit |

### Important Dependencies

The application uses a local Composer path repository for Laravel Permission Kit.

```json
{
    "type": "path",
    "url": "packages/laravel-permission-kit"
}
```

The tenancy package is currently configured through its development branch in Composer.

For reproducible deployments, retain the application's committed dependency lock file and use the versions resolved by Composer.

---

## Requirements

| Dependency | Requirement |
| --- | --- |
| PHP | 8.4 or later |
| Composer | 2.x |
| Node.js | Compatible with the project's npm dependencies |
| npm | Compatible with the project's package lock |
| Database | SQLite, MySQL/MariaDB, PostgreSQL, or SQL Server, subject to driver configuration |

### PHP Extensions

Enable the PHP extensions required by Laravel and the installed application dependencies, including:

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

The repository includes Laravel Permission Kit as a local Composer path dependency.

Make sure the package directory is present:

```text
packages/laravel-permission-kit/
```

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

Create the environment file:

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

Skolaria supports SQLite, MySQL, MariaDB, PostgreSQL, and SQL Server through its configured database managers.

For SQLite development, prepare the central database:

```bash
php artisan database:ensure-sqlite
```

For MySQL or PostgreSQL, create the central database and configure the appropriate connection in `.env`.

Review:

```text
config/database.php
config/tenancy.php
```

Also configure the tenant database prefix and suffix if required.

### 7. Run central migrations

```bash
php artisan migrate
```

### 8. Seed central application data

```bash
php artisan db:seed --class=DatabaseSeederNonTenancy
```

Tenant databases are provisioned through the tenant creation workflow, which runs the configured tenant migrations and seeder.

Avoid manually creating tenant databases outside the application's provisioning workflow unless you are performing a deliberate maintenance operation.

### 9. Start the application

```bash
php artisan serve
```

Open:

```text
http://localhost:8000
```

Complete the initial central application setup and create a tenant before accessing its school environment.

---

## Environment Configuration

Important environment variables include:

| Variable | Description |
| --- | --- |
| `APP_NAME` | Application name |
| `APP_URL` | Application base URL |
| `APP_ENV` | Application environment |
| `APP_DEBUG` | Debug mode |
| `DB_CONNECTION` | Central database connection |
| `DB_DATABASE` | Central database name or SQLite path |
| `CACHE_STORE` | Cache driver |
| `SESSION_DRIVER` | Session storage driver |
| `QUEUE_CONNECTION` | Queue driver |
| `FILESYSTEM_DISK` | Default filesystem disk |
| `TENANCY_IDENTIFICATION_MODE` | Tenant identification strategy |
| `TENANT_DATABASE_PREFIX` | Tenant database prefix |
| `TENANT_DATABASE_SUFFIX` | Tenant database suffix |
| `TENANCY_ALLOW_USER_IMPERSONATION` | Enables or disables impersonation |
| `BROADCAST_CONNECTION` | Broadcasting provider |
| `VITE_BROADCAST_CONNECTION` | Frontend broadcasting provider |
| `VITE_VAPID_KEY` | Public push notification key |

Refer to `.env.example` for the complete application configuration.

Never commit real credentials, private keys, production passwords, or service-account files.

---

## Notifications and Real-Time Communication

### Firebase Cloud Messaging

Firebase Cloud Messaging provides optional browser push notifications.

To configure Firebase:

1. Create or select a Firebase project.
2. Configure the Firebase client settings in:

   ```text
   public/assets/js/firebase-config.js
   ```

3. Configure the public VAPID key:

   ```env
   VITE_VAPID_KEY=your-public-vapid-key
   ```

4. Place the Firebase service-account credentials in:

   ```text
   storage/app/firebase/service-account-credentials.json
   ```

5. Rebuild frontend assets:

   ```bash
   npm run build
   ```

Keep service-account credentials outside the public web directory and never commit them.

Firebase push notifications are optional.

### Laravel Reverb

Reverb provides the application's WebSocket broadcasting functionality.

Configure the broadcasting connection:

```env
BROADCAST_CONNECTION=reverb
VITE_BROADCAST_CONNECTION="${BROADCAST_CONNECTION}"
```

Configure the required Reverb credentials and start the server:

```bash
php artisan reverb:start
```

For production, run Reverb under a process manager such as Supervisor or systemd and configure the reverse proxy for WebSocket traffic.

### Pusher

Pusher is an alternative broadcasting provider.

Configure:

```env
BROADCAST_CONNECTION=pusher
VITE_BROADCAST_CONNECTION="${BROADCAST_CONNECTION}"
```

Set the appropriate Pusher credentials in `.env`.

After changing the broadcasting configuration, rebuild frontend assets:

```bash
npm run build
```

Only the selected broadcasting provider should be initialized by the frontend.

---

## Development and Testing

Skolaria uses Pest and PHPUnit for automated testing.

The application tests cover custom application behavior and integrations, including tenant lifecycle operations, database isolation, authorization boundaries, and application-specific tenancy configuration.

### Run the complete test suite

```bash
php artisan test
```

### Run a specific test file

```bash
php artisan test tests/Feature/Tenancy/TenantProvisioningTest.php
```

### Run tests by name

```bash
php artisan test --filter="tenant"
```

### Code formatting

Run Laravel Pint:

```bash
vendor/bin/pint
```

### Testing strategy

Skolaria's tests focus on behavior implemented by the application rather than duplicating functionality already provided by Stancl Tenancy.

Important application-specific test areas include:

| Area | What is tested |
| --- | --- |
| Tenant provisioning | Application's tenant creation and provisioning workflow |
| Tenant deletion | Database, storage, and application-specific resource cleanup |
| Domain management | Domain creation, resolution, and deletion behavior |
| Tenant isolation | School data isolation between tenants |
| Context restoration | Returning to central context after tenant operations |
| Storage cleanup | Tenant storage and ID-card theme cleanup |
| Role boundaries | Tenant role permissions and sensitive authorization rules |
| Impersonation | Application-level impersonation feature restrictions |
| Master-data locking | Tenant-specific authorization for locked master tables |

The test suite uses SQLite for isolated tenant database testing where appropriate.

Stancl Tenancy's own package behavior is not duplicated unnecessarily in the application test suite.

---

## Project Structure

The main application directories are organized as follows:

```text
skolaria/
├── app/
│   ├── Console/
│   ├── Helpers/
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Middleware/
│   │   └── Requests/
│   ├── Jobs/
│   ├── Models/
│   ├── Providers/
│   ├── Repositories/
│   └── Services/
├── bootstrap/
├── config/
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── packages/
│   └── laravel-permission-kit/
├── public/
├── resources/
├── routes/
│   └── tenant/
├── storage/
└── tests/
    ├── Feature/
    └── Unit/
```

---

## Documentation

Additional documentation is available within the repository:

| Resource | Description |
| --- | --- |
| [Laravel Permission Kit](packages/laravel-permission-kit/README.md) | Permission discovery, synchronization, and authorization |
| [Contributing Guidelines](CONTRIBUTING.md) | Contribution process |
| [Code of Conduct](CODE_OF_CONDUCT.md) | Community standards |
| [Security Policy](SECURITY.md) | Reporting security vulnerabilities |

---

## Contributing

Contributions are welcome.

If you want to improve Skolaria, report bugs, or propose new functionality, please:

1. Review the existing implementation.
2. Open an issue for significant changes.
3. Follow the project's coding conventions.
4. Add or update tests for application-specific behavior.
5. Run the relevant test suite.
6. Run Laravel Pint before submitting changes.

For significant architectural changes, discuss the proposal before implementation.

---

## Security

Security is particularly important in a multi-tenant application.

Please do not disclose vulnerabilities through public issues.

Follow the instructions in [SECURITY.md](SECURITY.md) to report security problems responsibly.

Never use production credentials or real student information in development and testing environments.

---

## Acknowledgements

Skolaria builds upon the work of the open-source community and the Laravel ecosystem.

This project is based on the original `lav_sms` project by [4jean](https://github.com/4jean).

Special thanks to 4jean, the developers of the frameworks and libraries used in this project, and everyone who has contributed to its development.

---

## License

Skolaria is open-source software licensed under the **GNU Affero General Public License v3.0 (AGPL-3.0)**.

See the [LICENSE](LICENSE) file for the complete license terms.

---

<div align="center">

**Skolaria — One platform. Multiple schools. Centralized management.**

[GitHub Repository](https://github.com/rayusamBoy/skolaria) · [Project Website](https://skolaria.co.tz)

</div>
