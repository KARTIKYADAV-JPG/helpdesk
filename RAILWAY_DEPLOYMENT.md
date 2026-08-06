# Deployment Guide for Railway

This guide walks you through deploying the **Helpdesk** Laravel application on [Railway](https://railway.app).

---

## 🚀 Quick Start Deployment Steps

### 1. Push Code to GitHub
Ensure your latest code, including `Dockerfile`, `railway.json`, and `bootstrap/app.php` updates, is pushed to your GitHub repository.

---

### 2. Create a New Project on Railway
1. Log in to [Railway.app](https://railway.app).
2. Click **"New Project"**.
3. Select **"Deploy from GitHub repo"**.
4. Select your `helpdesk` repository.

---

### 3. Add a PostgreSQL Database
1. In your Railway project dashboard, click **"New"** -> **"Database"** -> **"Add PostgreSQL"**.
2. Railway will provision a managed PostgreSQL database.

---

### 4. Configure Environment Variables
In your Railway web service settings, navigate to the **Variables** tab and set the following environment variables:

| Variable | Recommended Value / Description |
|---|---|
| `APP_NAME` | `Helpdesk` |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_KEY` | Generate via `php artisan key:generate --show` locally and paste here |
| `APP_URL` | `https://${RAILWAY_PUBLIC_DOMAIN}` |
| `DB_CONNECTION` | `pgsql` |
| `DATABASE_URL` | `${Postgres.DATABASE_URL}` (Reference your Railway Postgres service) |
| `SESSION_DRIVER` | `database` |
| `CACHE_STORE` | `database` |
| `QUEUE_CONNECTION` | `database` |
| `FILESYSTEM_DISK` | `local` |
| `GEMINI_API_KEY` | Your Google Gemini API key |
| `GEMINI_MODEL` | `gemini-2.5-flash` |
| `SENTRY_DSN` | *(Optional)* Your Sentry DSN URL |
| `MAIL_MAILER` | `smtp` |
| `MAIL_HOST` | `smtp.gmail.com` (or your mail provider) |
| `MAIL_PORT` | `587` |
| `MAIL_USERNAME` | Your email address |
| `MAIL_PASSWORD` | App Password |
| `MAIL_ENCRYPTION` | `tls` |
| `MAIL_FROM_ADDRESS` | Your email address |
| `MAIL_FROM_NAME` | `${APP_NAME}` |

---

### 5. Domain & Networking
1. In your service settings, go to **Settings** -> **Networking** -> **Generate Domain**.
2. Railway will generate a public domain URL like `https://helpdesk-production-xxxx.up.railway.app`.

---

### 6. Initial Database Seeding (Optional)
On the first deployment, migrations run automatically via `docker-entrypoint.sh`.
If you want to seed default admin and initial sample data into your database, run via **Railway CLI** or terminal:

```bash
railway run php artisan db:seed --force
```

Alternatively, run from Railway's interactive service console.

---

## 🛠 Features Included in Deployment Setup

- **Automated Frontend Build**: Multi-stage Docker build compiles Tailwind CSS and Alpine.js assets automatically via Vite.
- **PHP Extensions Pre-installed**: PostgreSQL (`pdo_pgsql`), MySQL (`pdo_mysql`), `imap` (for email ingestion), `gd`, `bcmath`, `zip`, and `opcache`.
- **Dynamic Port Listening**: Nginx listens dynamically on Railway's `$PORT` environment variable.
- **Trust Reverse Proxies**: Configured to ensure secure HTTPS connections, CSRF tokens, asset links, and cookies work seamlessly.
- **Auto Migrations & Optimization**: Automatically executes `migrate --force` and caches configuration/routes on startup.
- **Health Check Endpoint**: Built-in health check monitored by Railway at `/up`.

---

## 🔍 Troubleshooting

- **Check Logs**: Go to **Deployments** -> **View Logs** in your Railway dashboard to view startup or runtime output.
- **Asset Issues**: If styles fail to load, ensure `APP_URL` is set to your Railway domain (`https://...`).
- **Database Connection Error**: Verify `DATABASE_URL` reference points correctly to your Railway PostgreSQL service.
