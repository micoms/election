# Codebase Review — Student Council Election 2026

A complete walkthrough of every file in the project: what it does, how it connects to everything else, and things to know.

---

## Project Structure

```
election/
├── index.html                  ← Login page (entry point)
├── admin-dashboard.html        ← Full admin panel (single page, tabbed)
├── database.sql                ← Full DB schema + seed data
├── .env                        ← DB credentials + UploadThing API key
│
├── pages/
│   ├── sign-up.html            ← Voter registration
│   ├── dashboard.html          ← Voter home after login
│   ├── vote.html               ← Voting page (one position at a time)
│   ├── review.html             ← Ballot review + final submit
│   ├── tally.html              ← Live vote count (voter-facing)
│   ├── contact-admin.html      ← Static contact form (no backend)
│   └── developer.html          ← Dev team credits page
│
├── api/
│   ├── config.php              ← DB connection (shared by all API files)
│   ├── login.php               ← Auth for both admins and voters
│   ├── register.php            ← Voter self-registration
│   ├── get_data.php            ← Generic read endpoint (positions, departments, developers)
│   ├── get_settings.php        ← Public settings reader
│   ├── get_candidates.php      ← All candidates (used by admin)
│   ├── get_candidates_by_position.php  ← Candidates for one position (used by vote.html)
│   ├── get_vote_tally.php      ← Vote counts per position
│   ├── get_voter_votes.php     ← What a specific voter has voted for
│   ├── save_vote.php           ← Insert/update a single vote row
│   ├── submit_votes.php        ← Mark voter as has_voted = 1
│   ├── upload.php              ← Uploads candidate image to UploadThing (cloud)
│   ├── add_candidate.php       ← Admin: add a candidate
│   ├── delete_candidate.php    ← Admin: delete a candidate
│   ├── admin_settings.php      ← Admin: read/write settings (GET/POST)
│   ├── admin_voters.php        ← Admin: list / reset / delete voters
│   ├── admin_positions.php     ← Admin: list / add / delete / reorder positions
│   ├── admin_departments.php   ← Admin: list / add / delete departments
│   ├── admin_developers.php    ← Admin: list / add / delete developers
│   ├── admin_update_profile.php ← Admin: change name / password
│   └── admin_reset_votes.php   ← Admin: wipe all votes
│
└── css/
    ├── style.css               ← Login, sign-up, contact pages
    ├── dash.css                ← Voter sidebar layout (dashboard, vote, tally)
    ├── review.css              ← Review & submit page
    ├── tally.css               ← Tally bars
    ├── admin-dash.css          ← Admin dashboard
    └── developer.css           ← Developer credits page
```

---

## Database Schema

Seven tables. Here's what each one holds and how they relate.

### `users`
Admin accounts only. Default: `vote@vote.vt / admin123`.
- `id`, `email`, `password` (plaintext), `role` (always `'admin'`), `full_name`

### `voters`
Registered student voters.
- `id`, `email`, `password` (plaintext), `full_name`, `student_id`, `department` (stored as name string, not FK), `year`, `has_voted` (0 or 1)

### `positions`
The ballot positions (President, VP, etc.).
- `id`, `name`, `order_num` (controls voting step order), `color` (hex, used on review page)

### `candidates`
One row per candidate. Position is stored as a name string matching `positions.name`.
- `id`, `position`, `name`, `image_url` (UploadThing URL), `description`, `department`, `year`

### `votes`
One row per voter per position. The core vote record.
- `id`, `user_id` → `voters.id`, `position` (name string), `candidate_id` → `candidates.id`
- **UNIQUE KEY** `uq_voter_position (user_id, position)` — enforces one vote per position per voter, and enables the `ON DUPLICATE KEY UPDATE` upsert in `save_vote.php`

### `departments`
College/department list used in voter registration and candidate profiles.
- `id`, `name`, `code`

### `developers`
Dev team credits shown on `developer.html`.
- `id`, `name`, `role`, `year`, `department_id` → `departments.id`, `order_num`

### `settings`
Key-value store for election-wide config.

| Key | Default | Purpose |
|-----|---------|---------|
| `election_open` | `'1'` | Gates access to voting pages |
| `allow_registration` | `'1'` | Gates the sign-up page |
| `election_title` | `'Student Council Election 2026'` | Sidebar subtitle |
| `org_name` | `'Student Vote'` | Sidebar title |
| `election_year` | `'2026'` | Used in branding |
| `logo_emoji` | `'🗳️'` | Stored but now hardcoded in HTML |

---

## Authentication & Session

There is **no server-side session**. Everything lives in `localStorage` after login.

**Login flow** (`index.html` → `api/login.php`):
1. `login.php` checks `users` table first (admin), then `voters` table.
2. On success, returns `user_id`, `email`, `role`, `full_name`, `student_id`, `type`.
3. `index.html` stores all of these in `localStorage` and redirects:
   - `type === 'admin'` → `admin-dashboard.html`
   - `type === 'voter'` → `pages/dashboard.html`

**Auth guard**: Every voter page checks `localStorage.getItem('user_id')` on load. If missing, redirects to `index.html`. There is no server-side token validation — any API endpoint can be called directly with any `voter_id`.

**Logout**: `localStorage.clear()` + redirect to `index.html`.

---

## Voter Flow (Step by Step)

### 1. Sign Up — `pages/sign-up.html`
- Loads departments from `api/get_data.php?endpoint=departments`
- Checks `allow_registration` setting first; shows "closed" message if `'0'`
- POSTs to `api/register.php` with: `full_name`, `email`, `student_id`, `department_id`, `year`, `password`
- `register.php` resolves `department_id` → department name, checks for duplicate email/student_id, inserts into `voters`

### 2. Login — `index.html`
- POSTs email + password to `api/login.php`
- Stores response in `localStorage`, redirects to `pages/dashboard.html`

### 3. Dashboard — `pages/dashboard.html`
- Loads positions from `api/get_data.php?endpoint=positions`
- Builds sidebar links and position cards dynamically
- Each card shows the voter's current pick (read from `localStorage`) or "Not yet voted"
- Loads org name / election title from `api/get_settings.php` for branding

### 4. Voting — `pages/vote.html?position=President`
- Position name comes from the URL query string (`?position=...`)
- Checks `election_open` setting; shows locked message if `'0'`
- Loads candidates for that position from `api/get_candidates_by_position.php?position=...`
- Selecting a candidate writes to `localStorage`:
  - `vote-{position}` → candidate ID
  - `vote-{position}-name` → candidate name
  - `vote-{position}-image` → image URL
- Nothing is written to the DB at this stage — selections are local only
- Navigation (Back/Next) steps through positions in `order_num` order; last position goes to `review.html`

### 5. Review & Submit — `pages/review.html`
- Reads all selections from `localStorage` and displays them
- On "Submit Final Vote":
  1. Calls `api/save_vote.php` for each selected position (in parallel via `Promise.all`)
  2. Each call does an `INSERT ... ON DUPLICATE KEY UPDATE` — safe to call multiple times
  3. If any save fails, shows an error and stops
  4. On all saves succeeding, calls `api/submit_votes.php` to set `has_voted = 1`
  5. Shows success popup, clears vote keys from `localStorage`, redirects to dashboard after 3s

### 6. Tally — `pages/tally.html`
- Loads all positions, then fetches `api/get_vote_tally.php?position=...` for each
- Renders a bar chart per position showing vote counts
- Auto-refreshes every 5 seconds

---

## API Reference

### `api/config.php`
Shared by every API file via `require 'config.php'`. Reads `.env` for DB credentials, opens a `mysqli` connection, sets charset to `utf8mb4`. Disables strict mysqli exceptions so errors return `false` instead of throwing.

### `api/login.php`
`POST email, password` → checks `users` then `voters`. Returns user object or error. Passwords are stored and compared in plaintext.

### `api/register.php`
`POST full_name, email, student_id, department_id, year, password` → inserts into `voters`. Resolves `department_id` to a name string before storing.

### `api/get_data.php`
`GET ?endpoint=positions|departments|developers` → returns array under `data` key. Used by all voter pages for sidebar/navigation.

### `api/get_settings.php`
`GET` → returns all settings as a key-value object. Auto-creates the `settings` table and inserts defaults if missing. Used by voter pages for branding.

### `api/admin_settings.php`
`GET` → same as `get_settings.php` (also used by `vote.html` to check `election_open`).  
`POST key, value` → upserts a single setting. Used by admin dashboard toggles and branding form.

### `api/get_candidates_by_position.php`
`GET ?position=President` → returns `{ success, position, candidates[] }`. Used by `vote.html`.

### `api/get_candidates.php`
`GET` → returns a bare array of all candidates (no `success` wrapper). Used only by admin dashboard candidate list.

### `api/save_vote.php`
`POST voter_id, position, candidate_id` → upserts into `votes` using `INSERT ... ON DUPLICATE KEY UPDATE candidate_id = $candidate_id`. The UNIQUE KEY on `(user_id, position)` makes this safe to call multiple times — the last call wins.

### `api/submit_votes.php`
`POST voter_id` → sets `has_voted = 1` in `voters`. Success is determined by absence of a DB error (not `affected_rows`, since a no-op update on an already-voted voter returns 0 affected rows).

### `api/get_vote_tally.php`
`GET ?position=President` → LEFT JOINs `candidates` with `votes` to count votes per candidate. Returns `{ success, position, results[], total_votes }`.

### `api/get_voter_votes.php`
`GET ?voter_id=5` → returns all votes cast by that voter with candidate details. Not currently used by any page but available.

### `api/upload.php`
`POST image (file)` → uploads to UploadThing cloud storage via their v7 API (two-step: prepareUpload → PUT). Returns `{ success, url }`. Requires `UPLOADTHING_API_KEY` in `.env`. Max 4MB, images only.

### `api/add_candidate.php`
`POST position, name, image_url, description, department, year` → inserts into `candidates`.

### `api/delete_candidate.php`
`POST candidate_id` → deletes from `candidates`.

### `api/admin_voters.php`
- `GET` → list all voters
- `POST action=reset, voter_id` → deletes that voter's votes + sets `has_voted = 0`
- `POST action=delete, voter_id` → deletes votes + voter row

### `api/admin_positions.php`
- `GET` → list positions ordered by `order_num`
- `POST action=add, name, color` → inserts, auto-assigns next `order_num`
- `POST action=delete, id` → cascades: deletes votes + candidates for that position, then the position
- `POST action=reorder, id, direction=up|down` → swaps `order_num` with adjacent position

### `api/admin_departments.php`
- `GET` → list departments
- `POST action=add, name, code` → insert
- `POST action=delete, id` → delete (voters keep their department string since it's stored by name)

### `api/admin_developers.php`
- `GET` → list developers with department name joined
- `POST action=add, name, role, year, department_id` → insert, auto-assigns `order_num`
- `POST action=delete, id` → delete

### `api/admin_update_profile.php`
`POST user_id, full_name, current_password, new_password` → updates admin name and/or password. Verifies current password before allowing password change.

### `api/admin_reset_votes.php`
`POST` (no body needed) → `DELETE FROM votes` + `UPDATE voters SET has_voted = 0`. No auth check.

---

## Admin Dashboard — `admin-dashboard.html`

Single HTML file with 7 tabs. Tab content is shown/hidden via CSS class `active`. No page reloads.

| Tab | What it does |
|-----|-------------|
| **Candidates** | Add (with image upload) and delete candidates per position |
| **Positions** | Add, delete, and reorder ballot positions |
| **Departments** | Add and delete departments |
| **Voters** | View all voters with voted/pending status; reset or delete individual voters |
| **Results** | Live vote tally per position (same data as `tally.html`) |
| **Settings** | Toggle election open/closed, toggle registration, edit org name/title/year, change admin password |
| **Developers** | Add and remove dev team members shown on `developer.html` |

Auth guard: checks `localStorage.getItem('role') === 'admin'` on load. Redirects to `index.html` if not admin.

---

## Key Design Decisions & Things to Know

**Votes are stored only on final submit.** During voting, selections live in `localStorage`. The DB is only written when the voter clicks "Submit Final Vote" on `review.html`. This means if a voter closes the browser mid-flow, their picks are preserved locally but nothing is in the DB yet.

**Position names are used as the join key everywhere.** `votes.position`, `candidates.position`, and the URL param in `vote.html` all use the position name string (e.g. `"President"`). There's no FK to `positions.id`. If you rename a position, existing votes and candidates won't match it.

**`department` in `voters` is stored as a name string, not an ID.** `register.php` resolves the `department_id` to a name before inserting. Deleting a department from the admin panel doesn't affect existing voter records.

**No server-side session or token.** `voter_id` is passed in POST bodies from the frontend. Any endpoint can be called with any ID from the browser console or curl.

**Passwords are plaintext.** Both `users` and `voters` store and compare passwords as plain strings.

**Image uploads go to UploadThing.** `upload.php` uses their v7 API. The returned URL is stored in `candidates.image_url`. Requires `UPLOADTHING_API_KEY` in `.env`.

**`contact-admin.html` is a static mockup.** The form has no submit handler and no backend. The button is wrapped in an `<a href="../index.html">` tag, so clicking it just navigates back to login.

**`get_candidates.php` returns a bare array**, not the `{ success, data }` envelope used by every other endpoint. The admin dashboard accounts for this, but it's inconsistent.

**`logo_emoji` is hardcoded as 🗳️ in all HTML files.** The setting still exists in the DB and `get_settings.php` still returns it, but no page reads it anymore.
