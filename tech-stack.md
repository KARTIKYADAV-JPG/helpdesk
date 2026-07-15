# Tech Stack

This document outlines the technical stack and architectural components for the AI-Powered Ticket Management System.

## Core Technologies

- **Laravel** – Core MVC backend architectural framework
- **PHP** – Server-side object-oriented programming language
- **MySQL** – Relational database management system
- **Blade** – Native Laravel layout compilation engine
- **Tailwind CSS** – Utility-first structural interface layouts
- **JavaScript** – Fluid UI dynamic updates and event operations
- **Postmark API** – Transactional mail delivery network and secure Inbound JSON processing streams
- **ngrok Tunneling** – Encrypted network bridging utility used to route cloud webhook requests to localized dev servers
- **Gemini AI API** – Natural language context processing for solution drafts and structural data mapping
- **Laravel Mail & Symfony Transport** – Native mail envelope composition and manual header mapping injections

## Authentication & Session Management
- **Authentication**: Laravel Breeze (Blade Stack)
- **Session Store**: Database sessions (`SESSION_DRIVER=database`)
  - All user sessions and authentication states will be stored in a `sessions` table in the MySQL database.
  - This ensures robust session tracking, easy session invalidation, and persistence across server restarts.
