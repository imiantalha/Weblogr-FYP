# Weblogr

Weblogr is a Core PHP/MySQL blogging platform developed as a Final Year Project and progressively modernized into a secure, maintainable portfolio application.

## Technology

- **Backend:** Core PHP 8.x
- **Database:** MySQL / MariaDB
- **Frontend:** HTML, CSS, JavaScript
- **Mail:** PHPMailer
- **Database driver:** MySQLi
- **CI:** GitHub Actions PHP linting

## Features

### Community
- Registration, OTP/email verification and login
- Password recovery and authenticated password changes
- Profiles and profile editing
- Create, edit, publish and save drafts
- Categories, search, author filters, sorting and pagination
- Comments and comment likes
- Post likes
- Follow/unfollow and personalized following feed
- Notification center with unread state and mark-as-read actions
- Content reporting

### Administration
- Administrator dashboard and platform metrics
- User activity management
- Report moderation workflow
- Resolve/dismiss/delete reported content
- Moderation audit logging
- Protected administrator navigation

### Security
- Prepared statements for dynamic SQL
- CSRF protection on state-changing requests
- Server-side authentication and authorization
- Session rotation after authentication-sensitive operations
- Login throttling
- Secure session cookie flags
- Upload MIME validation and generated filenames
- Context-aware output escaping
- Standard HTTP security headers
- Database integrity constraints and targeted indexes

## Repository Structure

```text
Weblogr-FYP/
├── comments/          # Comments and interactions
├── database/          # Connection and fresh schema
├── docs/              # FYP documentation and development guide
├── includes/          # Shared security and notification helpers
├── posts/             # Feed, posts, drafts, profiles, moderation and notifications
├── registration/      # Authentication and account flows
├── styles/            # Shared styling
├── uploads/           # Application upload assets
├── .github/workflows/ # CI validation
├── robots.txt         # Search crawler directives
├── sitemap.php        # Deployment-aware XML sitemap
├── site.webmanifest   # Web app metadata
└── index.html         # Public landing page
```

## Requirements

- PHP 8.1+ with `mysqli` and `fileinfo` enabled
- MySQL 5.7+ / MySQL 8.x or compatible MariaDB
- Apache/XAMPP/WAMP or PHP's built-in server
- Composer
- Git

## Local Setup

### 1. Clone

```bash
git clone https://github.com/imiantalha/Weblogr-FYP.git
cd Weblogr-FYP
```

### 2. Install dependencies

The legacy Composer configuration currently lives under `registration/`:

```bash
cd registration
composer install
cd ..
```

### 3. Recreate the database

The canonical development schema is now a single clean bootstrap file. It intentionally contains no application/sample records:

```bash
mysql -u root -p weblogr < database/weblogr.sql
```

Alternatively, import `database/weblogr.sql` from phpMyAdmin. The file creates the required tables and constraints from scratch, so the separate 2026 migration files do not need to be imported for a fresh database.

### 4. Configure local database credentials

The legacy connection layer currently contains the database configuration. Use local development values only and keep production credentials outside source control.

```text
DB_HOST=localhost
DB_NAME=weblogr
DB_USER=root
DB_PASSWORD=
```

> The next architectural cleanup is to move the remaining connection/mail configuration behind environment-based configuration.

### 5. Run

With XAMPP, start Apache and MySQL and open:

```text
http://localhost/Weblogr-FYP/
```

Or with PHP's built-in server:

```bash
php -S localhost:8000
```

Open `http://localhost:8000`.

## SEO & Discoverability

The public landing page includes descriptive metadata, Open Graph and Twitter metadata, crawler directives, a branded web manifest, and Schema.org structured data identifying Weblogr and its creator, Muhammad Talha.

Before production launch, replace the deployment host used by the sitemap with the canonical public domain and submit the sitemap to Google Search Console and Bing Webmaster Tools. Do not use a localhost URL as the production canonical domain.

## Development Workflow

Substantial changes use a dedicated branch and pull request:

```text
feature/<name>
    ↓
focused commits
    ↓
Pull Request
    ↓
review / fixes
    ↓
merge into main
    ↓
close PR
```

Every project commit should include:

```text
Co-authored-by: Muhammad Talha <muhammadtalha.codes@gmail.com>
```

See [`docs/DEVELOPMENT.md`](docs/DEVELOPMENT.md) for database rebuild, validation and security checklists.

## CI

GitHub Actions validates PHP syntax for application files on pushes and pull requests targeting `main`. Vendor copies and legacy bundled SMTP libraries are excluded from the lint job.

## Architecture Roadmap

Weblogr remains Core PHP while moving toward a clearer layered architecture:

```text
Request
  ↓
Entry point / routing
  ↓
Authentication + Authorization + CSRF
  ↓
Controller
  ↓
Service
  ↓
Repository / Database
  ↓
View or JSON response
```

The remaining modernization work focuses on extracting duplicated business logic, strengthening automated tests, improving observability, and moving environment configuration out of source files.

## Academic Project

Weblogr was originally developed as a Final Year Project. The academic report remains under `docs/`, while the application continues to receive production-oriented engineering improvements.
