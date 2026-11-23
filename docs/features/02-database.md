# 02 — Database Architecture, Models & Seeders

> **Phase:** 4 · **Branch:** `feature/02-database` · **Status:** Done
> **Figma screens:** Covers all entities from Sign In/Up, Dashboard, Projects, Tasks (Kanban), Chats, Meetings, Reports, Profile, and Ask AI

## 1. Overview

Establishes the complete database layer and entity domain model for the **Collabspace** platform. 

The schema faithfully represents every entity, relationship, and business attribute present in the Figma design system, featuring **internal `BIGINT` foreign keys for maximum join and index performance** coupled with **public 26-character ULIDs for non-enumerable API route binding**.

## 2. Domain Enums 🧹

Fourteen strict, type-safe PHP 8.4 Enums were introduced under `App\Enums`:

| Enum | Cases | Purpose |
|---|---|---|
| `AvailabilityStatus` | `Available`, `Busy`, `Away`, `OnLeave` | User availability state; `OnLeave` restricts task assignment |
| `ProjectType` | `SaaS`, `WebApp`, `MobileApp`, `Other` | Classification of projects |
| `ProjectPriority` | `Low`, `Medium`, `High` | Project urgency |
| `ProjectStatus` | `Active`, `OnHold`, `Completed`, `Archived` | Project lifecycle status |
| `TaskStatus` | `Todo`, `InProgress`, `InReview`, `Completed` | Kanban workflow statuses |
| `TaskPriority` | `Low`, `Medium`, `High` | Task urgency levels |
| `ConversationType` | `Direct`, `Channel` | One-on-one direct chats vs project/team channels |
| `MeetingStatus` | `Scheduled`, `Live`, `Ended`, `Cancelled` | Meeting operational state |
| `MeetingRecordingStatus` | `None`, `Processing`, `Ready`, `Failed` | Meeting recording AI pipeline state |
| `MeetingAttendeeResponse` | `Pending`, `Accepted`, `Declined` | RSVP status for attendees |
| `ReportType` | `TasksSummary`, `ProjectStatus`, `TeamPerformance`, `TimeTracking`, `MeetingsAttendance` | Detailed report types |
| `ReportStatus` | `Pending`, `Processing`, `Ready`, `Failed` | Background queued report generation state |
| `AiMessageRole` | `User`, `Assistant`, `System` | Chat roles for AI assistant |
| `AiPendingActionStatus` | `Pending`, `Confirmed`, `Rejected`, `Expired` | Write-tool confirmation state for AI assistant |

## 3. Database Schema & Migrations 🔒 ⚡

### 3.1 Migration Manifest

1. `2026_11_23_205735_create_permission_tables.php` — Spatie roles & permissions
2. `2026_11_23_205745_create_activity_log_table.php` — Spatie activity logging
3. `2026_11_23_205746_create_media_table.php` — Spatie polymorphic media library
4. `2026_11_23_210001_add_profile_fields_to_users_table.php` — Extended user profile fields (`ulid`, `google_id`, `bio`, `phone_number`, `job_title`, `availability_status`, etc.)
5. `2026_11_23_210002_create_departments_table.php` — Departments (`UI/UX`, `Frontend`, `Backend`, `Marketing`)
6. `2026_11_23_210003_create_projects_table.php` — Projects with status/deadline indexes & user `primary_project_id`
7. `2026_11_23_210004_create_project_members_table.php` — Project membership pivot with department and leader role
8. `2026_11_23_210005_create_tasks_table.php` — Kanban tasks with composite index `[project_id, status, position]` and `reviewer_id`
9. `2026_11_23_210006_create_task_assignees_table.php` — Multi-user task assignment pivot
10. `2026_11_23_210007_create_task_status_histories_table.php` — Cycle-time and workflow status transitions log
11. `2026_11_23_210008_create_time_logs_table.php` — Time tracking logs (`minutes`, `logged_on`)
12. `2026_11_23_210009_create_comments_table.php` — Polymorphic comments shared across tasks and meetings
13. `2026_11_23_210010_create_conversations_table.php` — Direct chats and project channels with `direct_key` constraint
14. `2026_11_23_210011_create_conversation_participants_table.php` — Chat participants with read tracking
15. `2026_11_23_210012_create_messages_table.php` — Messages with thread support (`parent_id`, `thread_title`) and fulltext search
16. `2026_11_23_210013_create_link_previews_table.php` — Cached link previews with SHA-256 URL hashing
17. `2026_11_23_210014_create_meetings_table.php` — Scheduled meetings with `[starts_at, ends_at]` index
18. `2026_11_23_210015_create_meeting_attendees_table.php` — Meeting attendees with RSVP status
19. `2026_11_23_210016_create_meeting_transcripts_and_summaries_table.php` — Transcripts with timestamps, multi-lingual AI summaries, and private notes
20. `2026_11_23_210017_create_reports_and_analytics_tables.php` — Generated reports, daily metrics rollups, and project progress snapshots
21. `2026_11_23_210018_create_ai_assistant_tables.php` — AI conversations, messages, user feedbacks, daily token usage quotas, and confirmation actions

## 4. Eloquent Models 🧹

Twenty-five models created under `App\Models` with `HasUlid`, complete relationships, casts, and query scopes:
- `User`, `Department`, `Project`, `ProjectMember`
- `Task`, `TaskStatusHistory`, `TimeLog`, `Comment`
- `Conversation`, `ConversationParticipant`, `Message`, `LinkPreview`
- `Meeting`, `MeetingAttendee`, `TranscriptSegment`, `MeetingSummary`, `MeetingNote`
- `Report`, `DailyMetric`, `ProjectProgressSnapshot`
- `AiConversation`, `AiMessage`, `AiMessageFeedback`, `AiUsage`, `AiPendingAction`

## 5. Realistic Seeders (Figma Faithful) 💼

Three structured seeders created under `Database\Seeders`:
1. `RolesAndPermissionsSeeder`: Seeds `admin`, `manager`, `member` roles and 14 granular permissions.
2. `DepartmentSeeder`: Seeds `UI/UX`, `Frontend`, `Backend`, `Marketing`.
3. `FigmaDataSeeder`: Faithfully seeds all elements from the Figma screenshots:
   - **User:** Mohamed Salah (`admin@example.com` / `password`), UI/UX Designer, Egypt phone `+20 123 456-9878`, 6 years of experience, bio, joined Apr 2022, admin role.
   - **Teammates:** 16 team members (Mohanad wahib, Sara Hassan, Ali Mohamed, Khalid Mousa, Mahmoud Salah, Ahmed Mohamed, Ali Hassan, Ahmed Hassan, Wahib salem, Mohamed Nadir, Omar Khaled, etc.).
   - **Projects:** `Alpha` (SaaS, High Priority, 42% progress) and `SepetGo` (WebApp, Medium Priority, 43% progress).
   - **Kanban Tasks:** 9 tasks across all columns (`Creat Wireframes`, `User Research Analysis`, `Design System Updates`, `Chat Screen Prototyping`, `Create Miro interaction`, `Mobile Responsiveness` with reviewer Ali Hassan, `Accessibility Audit`, `Login Screen`, `Survey`).
   - **Time Tracking:** 345 hours logged for Mohamed Salah matching the Figma profile card.
   - **Comments:** 8 comments per task matching the Figma comment count badges.
   - **Chats:** Channels (`UI Dashboard Project Public Channel`, `Frontend Team Channel`, `Backend Team Channel`), direct chats, and thread message (`#Screen edit`).
   - **Meetings:** "Design Review" with 5 attendees, full 6-line transcript verbatim from Figma, and AI Summary Key Points.
   - **Metrics:** `DailyMetric` and `ProjectProgressSnapshot` records for analytics charts.

## 6. Verification & Quality Gates

- `php artisan migrate:fresh --seed` runs cleanly on MySQL in **5.1s**.
- `vendor/bin/pint --test` passes with 0 issues.
- `vendor/bin/phpstan analyse --no-progress` passes at **Level 8** with 0 errors.
- `php artisan test` runs 6 tests with **52 assertions** passing 100%.
