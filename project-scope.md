
# AI-Powered Ticket Management System

## Problem
We receive hundreds of support emails daily. Our agents manually read, classify, and respond to each ticket — which is slow and leads to impersonal, canned responses.

## Solution
Build a ticket management system in **Laravel** that uses AI to automatically classify, respond to, and route support tickets — delivering faster, more personalized responses to students while freeing up agents for complex issues.

## Tech Stack
- **Framework**: Laravel (Core MVC backend architectural framework)
- **Database**: MySQL (Relational database management system)
- **Frontend**: Blade (Native Laravel layout compilation engine) & Tailwind CSS (Utility-first structural interface layouts)
- **AI**: Gemini AI API (Natural language context processing for solution drafts and structural data mapping)

## Key Features
- **Email Ingestion**: Receive support emails and automatically create tickets.
- **AI Response Generation**: Auto-generate human-friendly draft replies based on a knowledge base.
- **Dashboard & Ticket List**: View and manage all tickets with support for filtering and sorting.
- **Ticket Detail View**: Detailed view of ticket thread, AI summary, and AI-suggested replies.
- **AI-Powered Classification**: Automatically categorize tickets upon receipt.
- **User Management (Admin only)**: Admin can create and manage agent accounts.

## Domain Model Constraints

### Ticket Statuses
A ticket must have one of the following statuses:
- **Open**
- **Resolved**
- **Closed**

### Ticket Categories
A ticket belongs to a single category:
- **General Question**
- **Technical Question**
- **Refund Request**

### User Roles
- **Admin**: Deployed with the system. Can create and manage Agent users.
- **Agent**: Created by Admin. Can view, filter, sort, and manage tickets.