# Weblogr

Weblogr is a Core PHP/MySQL blogging platform developed as a Final Year Project and progressively modernized into a secure, maintainable portfolio application.

**Creator:** Muhammad Talha — Software Engineer

- **Portfolio:** https://imiantalha.vercel.app/
- **GitHub:** https://github.com/imiantalha
- **Fiverr:** https://www.fiverr.com/imiantalha
- **Upwork:** https://www.upwork.com/freelancers/~0129afd82850749f05?viewMode=1

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

## SEO & Discoverability

The public site includes descriptive metadata, Open Graph and Twitter metadata, crawler directives, a branded web manifest, Schema.org structured data, creator identity links, and a deployment-aware sitemap endpoint.

The public creator profile is available at [`seo-links.html`](seo-links.html), connecting Weblogr with Muhammad Talha's portfolio, GitHub, Fiverr and Upwork profiles. Before production launch, submit `/sitemap.php` to Google Search Console and Bing Webmaster Tools after the final public domain is configured.

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
├── humans.txt         # Creator and project credits
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

```bash
cd registration
composer install
cd ..
```

### 3. Recreate the database

The canonical development schema is a single clean bootstrap file with no application/sample records:

```bash
mysql -u root -p weblogr < database/weblogr.sql
```

Alternatively, import `database/weblogr.sql` from phpMyAdmin.

### 4. Run

With XAMPP, start Apache and MySQL and open:

```text
http://localhost/Weblogr-FYP/
```

Or:

```bash
php -S localhost:8000
```

## Development Workflow

Substantial changes use a dedicated branch and pull request, followed by review and merge into `main`.

Every project commit should include:

```text
Co-authored-by: Muhammad Talha <muhammadtalha.codes@gmail.com>
```

## CI

GitHub Actions validates PHP syntax for application files on pushes and pull requests targeting `main`.

## Architecture

Weblogr remains Core PHP while moving toward a clearer layered architecture:

```text
Request → Authentication / Authorization / CSRF → Controller → Service → Repository / Database → View / JSON
```

## Academic Project

Weblogr was originally developed as a Final Year Project. The academic report remains under `docs/`, while the application continues to receive production-oriented engineering improvements.
