# Collabspace API 🚀

[![CI Test Suite](https://img.shields.io/badge/PHPUnit-73%20Passed%20(409%20assertions)-brightgreen.svg)](tests/)
[![Static Analysis](https://img.shields.io/badge/Larastan-Level%208%20Clean-blue.svg)](phpstan.neon)
[![Code Style](https://img.shields.io/badge/Laravel%20Pint-Strict%20PSR--12-success.svg)](vendor/bin/pint)
[![Framework](https://img.shields.io/badge/Laravel-13%20(PHP%208.4)-red.svg)](https://laravel.com)
[![License](https://img.shields.io/badge/License-MIT-black.svg)](LICENSE)

Backend REST API for **Collabspace** — an enterprise project management, collaborative workspace, and team communication platform built for competition evaluation across four core pillars: **Clean Code & Architecture**, **Security**, **Performance**, and **Business Logic**.

---

## 🏛️ The Four Pillars of Competition Evaluation

### 1. Clean Code & Architecture 🧹
- **Thin Controllers & Single-Responsibility Actions:** Controllers act strictly as HTTP orchestrators, validating incoming requests via Form Requests and delegating logic to dedicated `Action` classes (e.g. `CreateMeetingAction`, `UpdateProfileAction`, `SendMessageAction`).
- **Domain Modeling & Enums:** PHP 8.4 backed enums (`TaskStatus`, `ProjectStatus`, `MeetingStatus`, `ReportType`, `AvailabilityStatus`) eliminate magic strings and enforce strict typing.
- **Strict Static Analysis:** Validated at **Larastan Level 8** with 0 errors (`phpstan analyse --no-progress`).
- **Code Style Uniformity:** 100% formatted with **Laravel Pint** (`vendor/bin/pint --test`).
- **Route Model Binding with Public ULIDs:** Public-facing endpoints use 26-character Crockford ULIDs (`/api/v1/projects/{project}`) while internal database joins use indexing-efficient `BIGINT` primary keys.

### 2. Enterprise Security 🔒
- **Authentication & Token Expiry:** Laravel Sanctum with dynamic token lifespans (30-day persistence for remember-me vs 24-hour baseline).
- **Anti-Enumeration Protection:** Forgot password endpoints return constant generic responses regardless of user existence to prevent user enumeration attacks.
- **SSRF Protection:** External link previews (`FetchLinkPreviewJob`) parse URLs and reject private/reserved IP ranges (`127.0.0.1`, `10.0.0.0/8`, `172.16.0.0/12`, `192.168.0.0/16`, `169.254.169.254`) before issuing HTTP requests.
- **Strict Authorization Policies:** Every resource (Projects, Tasks, Conversations, Meetings, Reports, Profile) is protected by Laravel Policies verifying user roles and workspace membership.
- **International Phone Validation:** Integrated `propaganistas/laravel-phone` powered by Google's libphonenumber library (`phone:AUTO,EG,US,INTERNATIONAL`).
- **Multi-Tier Rate Limiting:** Throttles applied to login (`throttle:login`), password resets, messages (`30/min`), and AI calls.

### 3. High Performance ⚡
- **Sub-15s Test Execution:** 73 tests with 409 assertions run in under 15 seconds using in-memory SQLite and reduced Bcrypt cost factors (`BCRYPT_ROUNDS=4`).
- **Eager Loading & N+1 Prevention:** All API resources strictly check `whenLoaded()` and relation counters (`whenCounted()`) to prevent hidden lazy-loading database queries.
- **Database Index Strategy:** Compound indexes on `(user_id, created_at)`, `(project_id, position)`, `(meeting_id, language, start_second)` for sub-millisecond lookups.
- **Real-Time WebSockets via Reverb:** Event broadcasting (`MessageSent`, `MessageRead`) runs through Laravel Reverb WebSockets with queued broadcast handlers.
- **Streaming & PDF Optimization:** Reports export directly to memory buffers and public disks for low-footprint downloads.

### 4. Business Invariants & Domain Logic 💼
- **Kanban Reviewer Invariant:** Moving a task to `in_review` status strictly requires a designated `reviewer_id` (`TaskReviewerRequiredException` returning HTTP 422).
- **Dynamic Project Progress Recalculation:** `TaskObserver` automatically recalculates parent project completion percentage (`progress` 0-100%) and sets `completed_at` whenever tasks change state.
- **Deterministic 1-on-1 Chats:** Direct conversations enforce uniqueness using deterministic composite keys (`direct:min(id):max(id)`), making chat initialization completely idempotent.
- **Meeting AI Summaries:** `AiMeetingSummarizer` analyzes multi-speaker timestamped transcripts to generate executive Key Takeaways and Action Items with assignees.
- **Executive CSV & PDF Reports:** Generates print-ready PDFs using DomPDF with clean table formatting and AI narrative analysis.

---

## 📦 Tech Stack & Packages

| Component | Technology | Version | Purpose |
|---|---|---|---|
| **Framework** | Laravel | 13.x | Core Application Framework |
| **Language** | PHP | 8.4 | Type safety, property promotion, strict types |
| **Authentication** | Laravel Sanctum | 4.x | Token-based API authentication |
| **Realtime** | Laravel Reverb | 1.x | WebSocket broadcasting |
| **AI SDK** | Laravel AI | 1.x | Collabspace AI Agent & conversational memory |
| **PDF Engine** | Barryvdh DomPDF | 3.x | Executive PDF report generation |
| **Phone Validation** | Propaganistas Phone | 6.x | Google libphonenumber integration |
| **Media Handling** | Spatie MediaLibrary | 11.x | Meeting recordings & avatar management |
| **Permissions** | Spatie Permission | 8.x | Role-based access control (Admin / Member) |
| **API Docs** | Dedoc Scramble | 0.13.x | Automated OpenAPI 3.0 documentation |
| **Static Analysis** | Larastan | 3.x | Level 8 strict static analysis |
| **Code Formatter** | Laravel Pint | 1.x | Opinionated PSR-12 code style |

---

## 📁 Feature Modules & Documentation

Detailed specification documents for each phase:

1. [01 — Foundation & Project Setup](docs/features/01-foundation.md)
2. [02 — Database Architecture & Seeders](docs/features/02-database.md)
3. [03 — Authentication & Security](docs/features/03-auth.md)
4. [04 — Projects & Workspace Management](docs/features/04-projects.md)
5. [05 — Tasks, Kanban Board & Observers](docs/features/05-tasks.md)
6. [06 — Real-time Chats & Reverb WebSockets](docs/features/06-chats.md)
7. [07 — Meetings, Recordings & AI Transcripts](docs/features/07-meetings.md)
8. [08 — Dashboard, Reports (CSV/PDF) & User Profile](docs/features/08-reports-and-profile.md)
9. [09 — Collabspace AI Assistant](docs/features/09-ai-assistant.md)

---

## 🚀 Quickstart & Installation

### Requirements
- **PHP 8.4+** (with `bcmath`, `ctype`, `fileinfo`, `json`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`)
- **Composer 2+**
- **MySQL 8.0+**

### 1. Clone & Install Dependencies
```bash
git clone https://github.com/MohamedAbdelshakor/collabspace-api.git
cd collabspace-api
composer install
```

### 2. Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

Configure your database credentials in `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=collabspace
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Run Migrations & Seeders
Populates complete domain entities matching the Figma design (Mohamed Salah, Alpha Project, SepetGo, Kanban tasks, transcripts, and metrics):
```bash
php artisan migrate --seed
```

### 4. Start the Application & Reverb WebSockets
```bash
# Terminal 1: API Server
php artisan serve

# Terminal 2: Reverb WebSocket Server
php artisan reverb:start
```

---

## 🧪 Testing & Verification

Run the complete test suite (73 tests, 409 assertions):
```bash
php artisan test
```

Run code style formatter:
```bash
vendor/bin/pint --test --format agent
```

Run static analysis (Level 8):
```bash
vendor/bin/phpstan analyse --no-progress
```

---

## 📖 API Documentation & Postman

- **Interactive API Documentation (Scramble):** `http://localhost:8000/docs/api`
- **Postman Collection:** Download and import [`docs/collabspace.postman_collection.json`](docs/collabspace.postman_collection.json) to test all endpoints out-of-the-box.
- **Default Seeded Credentials:**
  - **Email:** `admin@example.com`
  - **Password:** `password`
