# Security Policy

## Supported version

The `main` branch is the actively maintained version of Weblogr.

## Reporting a vulnerability

Do not publish sensitive vulnerability details in a public issue. Contact the project maintainer privately with:

- A concise description of the vulnerability
- The affected endpoint/file
- Reproduction steps
- Security impact
- Suggested mitigation, if known

Never include passwords, API keys, SMTP credentials, session cookies, OTPs, or other secrets in a report.

## Security baseline

Weblogr uses server-side authentication and authorization, CSRF validation, prepared statements, output escaping, secure session cookies, login throttling, upload MIME validation, and HTTP security headers. Security-sensitive changes should be reviewed against the checklist in `docs/DEVELOPMENT.md`.
