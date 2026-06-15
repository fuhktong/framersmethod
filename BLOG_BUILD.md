# Blog Build Tracker

## Phase 1 — Database Schema ✅
- `database/schema.sql` — `users` and `posts` tables
- `database/db.php` — PDO connection using `.env` credentials

## Phase 2 — Admin Login ✅
- `/admin/login` route with session-based auth
- Credentials from `.env` (`ADMIN_USERNAME`, `ADMIN_PASSWORD`)
- Middleware protecting all `/admin/*` routes

## Phase 3 — Blog Post Tool ✅
- `/admin/posts` — list, create, edit, delete posts
- Fields: title, slug (auto from title), excerpt, category, body, status (draft/published)
- Publish toggle

## Phase 4 — Public Post Routing ✅
- `/blog` — feed index (published posts, newest first)
- `/blog/{slug}` — individual post view
- Routing wired into `index.php`

## Phase 5 — Homepage Newsfeed ✅
- Pull 6 most recent published posts from DB
- WIP-style cards: category tag, title, excerpt, date, read more link
- Replaces current static homepage sections
