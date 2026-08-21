# Weblogr

Weblogr is a Core PHP/MySQL blogging platform developed as a Final Year Project (FYP). It includes user authentication, email verification, password recovery, profiles, blog posts and drafts, comments, likes, following, notifications, reporting, and administrative content management.

## Technology

- **Backend:** Core PHP
- **Database:** MySQL
- **Frontend:** HTML, CSS, JavaScript
- **Mail:** PHPMailer
- **Database driver:** MySQLi

## Repository Structure

```text
Weblogr-FYP/
├── comments/          # Comments and comment interactions
├── database/          # Database connection and schema
├── docs/              # FYP report and supporting documentation
├── images/            # Application images/assets
├── posts/             # Blog, draft, feed, moderation and notification features
├── registration/      # Authentication, profiles and account flows
├── styles/            # Shared styling
├── uploads/           # Uploaded application content
├── index.html         # Public landing page
└── composer.json      # PHP dependencies
```

## Requirements

- PHP 7.4+ with the `mysqli` extension enabled
- MySQL 5.7+ or MySQL 8.x
- Apache/XAMPP/WAMP or PHP's built-in development server
- Composer
- Git

## Local Setup

### 1. Clone the repository

```bash
git clone https://github.com/imiantalha/Weblogr-FYP.git
cd Weblogr-FYP
```

### 2. Install PHP dependencies

```bash
composer install
```

If Composer dependencies are currently defined in a project subdirectory, run the command from that directory until the application is migrated to a single root Composer configuration.

### 3. Create the database

Create a MySQL database named `weblogr` and import the SQL schema provided by the project. Review the schema before importing it into an existing database because the current schema is legacy/FYP material and should not be treated as a production migration system.

Example:

```bash
mysql -u root -p weblogr < database/weblogr.sql
```

Or import the SQL through phpMyAdmin.

### 4. Configure the database

The legacy application currently reads database settings from its PHP connection file. For local development, configure the values for your machine:

```text
DB_HOST=localhost
DB_NAME=weblogr
DB_USER=root
DB_PASSWORD=
```

**Do not commit production credentials, API keys, SMTP passwords, OTPs, or other secrets.** The application should be migrated to environment-based configuration before production deployment.

### 5. Run locally

With XAMPP/WAMP, place the repository under the web server document root and open it through the local server.

Alternatively:

```bash
php -S localhost:8000
```

Then open `http://localhost:8000`.

> Some legacy modules currently assume a specific directory layout and relative paths. During the modernization work these assumptions should be removed in favor of a single application bootstrap and public entry point.

## Main Features

- User registration and login
- Email/OTP verification
- Password recovery
- User profiles
- Create, edit, publish and save blog drafts
- Categories and feed filtering
- Comments and likes
- Follow/unfollow
- Notifications
- Content reporting
- Administrative moderation

## Security Requirements

The current application is a legacy Core PHP/FYP implementation and should **not be considered production-ready** without the security modernization described in the project roadmap.

Priority security work includes:

- Prepared statements for every dynamic SQL query
- Server-side authentication and authorization middleware
- Ownership checks for post/draft/comment operations
- CSRF protection for every state-changing request
- Context-aware output escaping to prevent XSS
- Strict upload validation and generated upload filenames
- Secure password-reset tokens with expiration and one-time use
- Secure OTP generation, expiration and attempt limits
- Session regeneration and secure cookie settings
- Environment-based secrets and configuration
- Centralized exception handling and security logging
- Rate limiting for authentication and recovery endpoints

## Development Standards

When modifying the application:

1. Do not trust identifiers, roles, filenames, or other values supplied by the browser.
2. Use prepared SQL statements instead of concatenating request data into SQL.
3. Authorize the resource owner on the server before modifying or deleting data.
4. Escape database content according to its output context.
5. Keep state-changing operations behind POST/appropriate HTTP methods and CSRF protection.
6. Keep credentials and environment-specific configuration out of source control.
7. Prefer reusable services/helpers over duplicating authentication, database, validation, and response logic.
8. Add tests or validation for security-sensitive changes.

## Architecture Roadmap

The legacy application is being progressively modernized while remaining **Core PHP**. The target architecture is:

```text
Request
  ↓
Public entry point / Router
  ↓
Middleware
  ├── Authentication
  ├── Authorization
  └── CSRF
  ↓
Controller
  ↓
Service
  ↓
Repository / Database layer
  ↓
View or JSON response
```

The planned project structure is:

```text
app/
├── Controllers/
├── Services/
├── Repositories/
├── Middleware/
├── Validators/
├── Helpers/
└── Views/

config/
database/
public/
routes/
storage/
tests/
docs/
```

This structure keeps the project as Core PHP while separating HTTP handling, business logic, persistence, security, and presentation.

## Testing and CI

The modernization plan includes automated validation for:

- PHP syntax
- Authentication and authorization
- Post ownership
- CSRF protection
- Input/output security
- File upload validation
- Core blog workflows
- Database integration

GitHub Actions should run these checks for pushes and pull requests before changes are merged.

## Documentation

The `docs/` directory contains the academic FYP report, appendices, references, testing material, usability-study material, and report assembly resources.

## Academic Project

Weblogr was developed as a Final Year Project. The academic documentation is retained under `docs/`, while the application is being progressively refactored toward a secure, maintainable and production-oriented Core PHP architecture.
