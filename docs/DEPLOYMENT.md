# Weblogr Deployment Guide

## Production deployment

**Live application:** https://weblogr.up.railway.app/

The current production deployment runs on Railway. The application should use the Railway URL as its canonical public URL through the `APP_URL` environment variable.

## Recommended runtime

- PHP 8.1+ with `mysqli` and `mbstring` enabled
- MySQL 8.x or a compatible MySQL/MariaDB release
- Apache with `.htaccess`/`mod_rewrite` support
- HTTPS in production
- Composer for dependency installation

## 1. Create the database

Import `database/weblogr.sql` into a fresh database named `weblogr` (or the name configured by `DB_NAME`). The bootstrap intentionally contains no application/sample rows.

Do not run the migration SQL files on top of the bootstrap unless you are upgrading an older installation. The bootstrap is the clean baseline.

## 2. Configure environment variables

Copy `.env.example` to `.env` for local Apache/XAMPP development. Production hosts should prefer their platform's environment-variable settings.

Set the canonical production URL:

```text
APP_URL=https://weblogr.up.railway.app
```

For Google Sign-In in production, configure the exact authorized redirect URI:

```text
https://weblogr.up.railway.app/registration/google_callback.php
```

Required database values:

- `DB_HOST`
- `DB_PORT`
- `DB_NAME`
- `DB_USER`
- `DB_PASSWORD`

For registration/password-reset email, also configure the `MAIL_*` values.

Never commit `.env` or real SMTP/database credentials.

## 3. Install PHP dependencies

From the `registration` directory:

```bash
composer install --no-dev --optimize-autoloader
```

The repository contains a Composer lock file so deployments can reproduce the tested dependency versions.

## 4. Web root

Point Apache's document root at the repository root. Do not expose the project through `file://` URLs.

For XAMPP:

```text
http://localhost/Weblogr-FYP/
```

## 5. Verify the deployment

Open the production application at:

```text
https://weblogr.up.railway.app/
```

Then verify:

```text
https://weblogr.up.railway.app/health.php
https://weblogr.up.railway.app/sitemap.php
https://weblogr.up.railway.app/robots.txt
```

A healthy application returns JSON from `/health.php` with `status: ok` and a successful database connection.

The sitemap uses `APP_URL` when configured, otherwise it derives the current request host. Set `APP_URL` to the canonical HTTPS production URL before submitting the sitemap to search engines.

## 6. Production checks

- Confirm HTTPS and the canonical hostname.
- Confirm database credentials are supplied by the host.
- Confirm SMTP uses an application password/API credential, never a normal mailbox password.
- Confirm `.env`, `.git`, database internals, dependency directories and private application routes are not directly accessible.
- Confirm PHP errors are logged server-side and not displayed to visitors.
- Test signup, OTP verification, login, logout, password reset, post creation, comments, follows, notifications and admin moderation.
- Test the empty database state before adding seed data.
