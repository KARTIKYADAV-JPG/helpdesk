# Deployment Guide for Render (render.com)

This guide walks you through deploying the **HelpDesk** Laravel application on [Render](https://render.com) using Docker.

---

## 🌟 Prerequisites
1. A [Render account](https://dashboard.render.com/) linked to your GitHub account.
2. Your repository (`helpdesk`) pushed to GitHub with the latest changes (`render.yaml`, `Dockerfile`, etc.).

---

## 🚀 Option 1: 1-Click Deployment with Render Blueprint (Recommended)

Render Blueprint automatically provisions both the **Docker Web Service** and the **PostgreSQL Database** in one go.

1. Go to your [Render Dashboard](https://dashboard.render.com/).
2. In the top right corner, click **"New +"** and select **"Blueprint"**.
3. Connect your GitHub repository (`helpdesk`).
4. Render will detect `render.yaml` and display the resources it will create:
   - **Web Service:** `helpdesk` (Docker, Free Plan)
   - **Database:** `helpdesk-db` (PostgreSQL, Free Plan)
5. You will see an optional field for **`GEMINI_API_KEY`**:
   - Paste your Google Gemini API key (or leave it blank if you want to add it later).
6. Click **"Apply"**.
7. Render will automatically build the container and deploy the app!

---

## 🛠 Option 2: Manual Deployment

If you prefer to configure the Web Service manually:

### Step 1: Create a PostgreSQL Database
1. In Render Dashboard, click **"New +"** -> **"PostgreSQL"**.
2. Name: `helpdesk-db`
3. Database: `helpdesk`
4. User: `helpdesk_user`
5. Plan: **Free**
6. Click **"Create Database"**.
7. Once created, copy the **"Internal Database URL"**.

### Step 2: Create the Web Service
1. In Render Dashboard, click **"New +"** -> **"Web Service"**.
2. Select your `helpdesk` repository.
3. Configuration:
   - **Name:** `helpdesk`
   - **Region:** Same as database (e.g., Oregon)
   - **Branch:** `main`
   - **Runtime:** `Docker`
   - **Plan:** `Free`
   - **Health Check Path:** `/up`
4. In the **Environment Variables** section, add:

| Key | Value |
|---|---|
| `APP_NAME` | `HelpDesk` |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_KEY` | *(Leave empty for auto-generation, or paste `php artisan key:generate --show`)* |
| `DB_CONNECTION` | `pgsql` |
| `DATABASE_URL` | *(Paste Internal Database URL from Step 1)* |
| `SESSION_DRIVER` | `database` |
| `CACHE_STORE` | `database` |
| `QUEUE_CONNECTION` | `database` |
| `LOG_CHANNEL` | `stderr` |
| `ADMIN_NAME` | `Admin` |
| `ADMIN_EMAIL` | `admin@example.com` *(or your custom email)* |
| `ADMIN_PASSWORD` | `admin123` *(or your custom password)* |
| `SEED_DATABASE` | `true` |
| `GEMINI_API_KEY` | *(Your Gemini API key)* |
| `GEMINI_MODEL` | `gemini-2.5-flash` |

5. Click **"Create Web Service"**.

---

## 🔑 Default Login Credentials

Once your deployment completes, visit your Render URL (`https://helpdesk-xxxx.onrender.com/login`):

- **Email:** `admin@example.com` (or the value of `ADMIN_EMAIL`)
- **Password:** `admin123` (or the value of `ADMIN_PASSWORD`)

---

## ⚙️ Features Configured Automatically

- **Multi-stage Docker Build**: Node 20 compiles Tailwind CSS & Vite assets; PHP 8.4-FPM + Nginx serves production traffic.
- **PHP Extensions**: Pre-loaded with `pdo_pgsql`, `pdo_mysql`, `imap` (email ingestion), `gd`, `bcmath`, `zip`, and `opcache`.
- **Dynamic Port Mapping**: Automatically listens on Render's `$PORT` environment variable.
- **Reverse Proxy SSL Support**: Configured to trust Render proxy headers so HTTPS redirects and CSRF tokens work seamlessly.
- **Auto-migration & Health Check**: Runs `migrate --force` with automatic retry logic and monitors `/up`.
