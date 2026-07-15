# Implementation Plan - AI-Powered Ticket Management System

We will build a ticket management system using **Laravel** (PHP) with a **MySQL** database. The system will use the **Gemini AI API** to automatically categorize tickets, summarize histories, and draft suggested replies based on a Knowledge Base.

## User Review Required

> [!IMPORTANT]
> **MySQL Configuration**: We will configure Laravel to use MySQL. You will need a running MySQL instance and will need to update the `.env` database credentials (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).
>
> **Gemini API Key**: To use the AI features, you will need a Gemini API Key. We will configure it via `GEMINI_API_KEY` in the `.env` file.
>
> **Database Sessions**: Authentication and session state will be stored in a `sessions` table in the MySQL database (`SESSION_DRIVER=database`). This requires migrating the `sessions` table schema during setup.
>
> **Postmark API & ngrok**: We will use the Postmark API for transactional emails and inbound processing, and ngrok for routing webhooks to our local development server.

---

## Proposed Database Schema & Models

### 1. Migrations & Schema

#### `users` Table
*   `id` (Primary Key)
*   `name`
*   `email` (Unique)
*   `password`
*   `role` (Enum: `admin`, `agent`)
*   `timestamps`

#### `tickets` Table
*   `id` (Primary Key)
*   `customer_name`
*   `customer_email`
*   `subject`
*   `category` (Enum: `general_question`, `technical_question`, `refund_request`)
*   `status` (Enum: `open`, `resolved`, `closed`)
*   `assigned_agent_id` (Foreign Key -> `users.id`, nullable)
*   `timestamps`

#### `messages` Table
*   `id` (Primary Key)
*   `ticket_id` (Foreign Key -> `tickets.id`, cascade delete)
*   `sender_type` (Enum: `customer`, `agent`, `ai`)
*   `body` (Text)
*   `timestamps`

#### `knowledge_base` Table
*   `id` (Primary Key)
*   `title`
*   `content` (Text)
*   `timestamps`

#### `sessions` Table (Laravel default database session store)
*   `id` (String Primary Key)
*   `user_id` (Foreign Key -> `users.id`, nullable)
*   `ip_address`
*   `user_agent`
*   `payload` (Text)
*   `last_activity` (Integer)

---

## Proposed Architecture & Components

### 1. Backend / Routing
*   **Web Routes**:
    *   `GET /login`, `POST /login`, `POST /logout` (Breeze Auth)
    *   `GET /dashboard` (View dashboard stats)
    *   `GET /tickets` (List tickets with status/category filter)
    *   `GET /tickets/{id}` (Detail view of a ticket, history, suggested AI draft)
    *   `POST /tickets/{id}/reply` (Send reply to customer)
    *   `POST /tickets/{id}/status` (Update status)
    *   `POST /tickets/{id}/category` (Update category)
    *   `GET /agents` (Admin view to list and create agents)
    *   `POST /agents` (Admin creates agent)
    *   `GET /sandbox` (Mock email sandbox to simulate receiving an email)
    *   `POST /sandbox/receive` (Trigger incoming ticket creation and AI flow)
*   **AI Integration Class** (`app/Services/GeminiService.php`):
    *   `classifyTicket($subject, $body)`: Calls Gemini to return one of the categories.
    *   `summarizeTicketHistory($ticket)`: Calls Gemini to produce a short bulleted summary of messages.
    *   `generateDraftReply($ticket, $knowledgeBaseArticles)`: Calls Gemini to draft a reply using relevant articles.

### 2. Frontend (Views)
We will build clean, responsive, and modern Blade templates using **custom CSS** for premium styling (dark/light themes, sleek card layouts, interactive elements with Alpine.js):
*   **Dashboard View**: Overview charts of open, resolved, and closed tickets by category.
*   **Ticket Detail Screen**: Split screen layout:
    *   Left side: Chat/thread message history.
    *   Right side: AI box (Summary, classification category, and AI draft reply text area that the agent can edit and insert/send).
*   **Admin Panel**: Simple table to manage and create agents.
*   **Mock Email Sandbox**: A split-screen playground UI:
    *   Left: Form to write a "Student Email" (Name, Email, Subject, Body).
    *   Right: Log of action events showing: "Email received" $\rightarrow$ "AI categorized as Refund Request" $\rightarrow$ "Ticket #12 created" $\rightarrow$ "AI generated initial reply draft".

---

## Execution Phases & Tasks

### Phase 1: Setup & Infrastructure
- [ ] Initialize Laravel 11 project codebase.
- [ ] Configure database connection (`DB_CONNECTION=mysql`) and authentication session store (`SESSION_DRIVER=database`) in `.env`.
- [ ] Run Laravel Breeze installer to scaffold login and layout structure.

### Phase 2: Schema, Models & Seeders
- [ ] Create migration files for `tickets`, `messages`, and `knowledge_base`.
- [ ] Create the default `sessions` table migration (`php artisan session:table`).
- [ ] Implement model schemas and Eloquent relationships in `User`, `Ticket`, `Message`, and `KnowledgeBase` models.
- [ ] Create default database seeder for initial Admin (`admin@helpdesk.com`) and sample knowledge base data.

### Phase 3: Auth & Agent Management (Admin Only)
- [ ] Enforce Admin-only middleware constraints for agent management routes.
- [ ] Build Admin UI view to list current agents.
- [ ] Create standard forms for registering new agent accounts.

### Phase 4: Main Dashboards & Agent Workflow
- [ ] Style the dashboard container using custom premium CSS (light/dark themed panels).
- [ ] Build dashboard home page showing key ticketing metrics (count of tickets per status and category).
- [ ] Implement paginated ticket lists with custom filtering controls for status and category.
- [ ] Design ticket detail page featuring message threads and interactive sidebar selectors.

### Phase 5: Email Ingestion Sandbox (Mock Environment)
- [ ] Build a split-screen visual Email Ingestion Sandbox.
- [ ] Design a simple ticket creation flow that parses custom guest email forms.
- [ ] Build an interactive sandbox logging panel tracking background job progression.

### Phase 6: AI Engine & Gemini Integration
- [ ] Integrate Gemini API client service using native Laravel HTTP wrapper.
- [ ] Set up background jobs to automatically run Gemini categorization on new tickets.
- [ ] Implement text query matching to fetch relevant knowledge base context.
- [ ] Build Gemini response generator for automated draft emails and historical ticket summaries.

### Phase 7: Testing & Polishing
- [ ] Write integration test cases for ticket routing rules, admin authentication, and mock AI responses.
- [ ] Complete end-to-end user testing via sandbox and polish UI animations.

---

## Verification Plan

### Automated Verification
*   We will run Laravel tests to verify:
    *   Admin user creation and permissions.
    *   Ticket status and category constraints.
    *   AI service integration using mocked Gemini API calls.

### Manual Verification
1. Run migrations and database seeding.
2. Log in as default Admin (`admin@helpdesk.com` / `password`).
3. Open the **Mock Email Sandbox** and submit a test refund request.
4. Verify in the Agent Dashboard that the ticket:
    *   Was created successfully.
    *   Was automatically categorized as "Refund Request".
    *   Has an AI-generated summary and draft reply matching the knowledge base.
