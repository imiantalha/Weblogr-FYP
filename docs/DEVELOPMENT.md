# Weblogr Development Guide

## Database rebuild

For a clean local database, create an empty `weblogr` database and run the schema first:

```bash
mysql -u root -p weblogr < database/weblogr.sql
```

Then apply the versioned modernization migrations in chronological order:

```bash
mysql -u root -p weblogr < database/migrations/2026_08_21_admin_moderation.sql
mysql -u root -p weblogr < database/migrations/2026_08_21_platform_hardening.sql
```

The migrations add moderation workflow tables, audit logging, notification read state, integrity constraints, and indexes. Keep migrations in source control and never make undocumented production-only schema changes.

## PHP validation

Run syntax checks before opening a pull request:

```bash
find . -type f -name '*.php' -not -path './registration/vendor/*' -not -path './registration/smtp/*' -print0 | while IFS= read -r -d '' file; do php -l "$file" || exit 1; done
```

GitHub Actions runs the same PHP lint check on pushes and pull requests targeting `main`.

## Feature workflow

Use a dedicated branch for every substantial feature:

```text
feature/<name>
  -> focused commits
  -> pull request
  -> review and fixes
  -> merge into main
  -> close PR
```

Every project commit should include:

```text
Co-authored-by: Muhammad Talha <muhammadtalha.codes@gmail.com>
```

## Security checklist

Before merging state-changing functionality:

- Authentication is required where appropriate.
- Authorization is checked on the server.
- POST is used for mutations.
- CSRF tokens are verified.
- SQL uses prepared statements.
- User-controlled output is escaped for its output context.
- Uploaded files use MIME validation and generated filenames.
- Sensitive errors are logged without exposing internals.
- New database requirements have a migration.
- UI changes work on mobile as well as desktop.
