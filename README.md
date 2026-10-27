# Snap Finder

**Gold · v1.1.2 · released as-is.** Future features and maintenance are optional.

Snap Finder is a Snapchat discovery directory you can host yourself. It includes email-verified accounts, categories, moderated listings, reporting, saved profiles, adult-access controls, and account export and deletion.

I built Snap Finder as a Forge lab and tried to market it. It wasn't gaining traction. After considering the idea and the market, I didn't feel it justified the time needed to push it further, so I decided to open-source it.

## Install

Requires PHP 8.3+, which matches the locked dependencies and CI-tested runtime. Requires PDO SQLite, mbstring, OpenSSL, fileinfo, DOM/XML, curl, Composer 2 and Node.js 22.12+ with npm. Linux, macOS and Windows with those runtimes are supported.

```sh
git clone https://github.com/loganpendragonmultiverse/snap-finder.git
cd snap-finder
php install.php
php artisan app:create-admin
php artisan serve --host=127.0.0.1 --port=8000
```

Open http://localhost:8000. Shell and PowerShell launchers are supplied. Installation creates a new encryption key and empty SQLite database. No default account is shipped. An existing .env and key are preserved; review configuration before rerunning migrations.

## Deploy

Serve public/ only. Set APP_ENV=production, APP_DEBUG=false, APP_URL to your HTTPS origin, SESSION_SECURE_COOKIE=true, CONTACT_EMAIL and Laravel SMTP settings in private .env. Grant the service user write access to storage/ and bootstrap/cache. Keep database/ private and backed up. Run php artisan storage:link for uploads. Build with npm ci && npm run build. Customize instance policies and contact details before public hosting.

MAIL_MAILER=log is a local default: reset/verification messages appear in private storage/logs. Configure SMTP before accepting public users. No email is delivered by default.

Fresh installs contain categories and no profiles. Configure email verification before publishing. Administrators manage listings and reports. Self-declared adult access is not external identity verification. Snapchat is a Snap Inc. trademark; this project is independent.

## Development

```sh
php artisan test
npm run build
```

See DEVELOPMENT.md, TESTING.md, PRIVACY.md, SECURITY.md, CHANGELOG.md and THIRD_PARTY_NOTICES.md.
Application code is MIT; attributed assets retain their own licenses.
