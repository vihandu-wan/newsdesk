# NSBM NewsDesk – Campus News Management System

WAD Batch 26.1 IFSL final project (Topic 1). Built with **HTML, CSS, JavaScript, PHP (PDO) and MySQL**.

- **Live site:** _add your InfinityFree link here_
- **Demo video:** _add your YouTube (unlisted) link here_

## Features

| Admin | Student / user |
|---|---|
| Secure login, separate admin panel | Register, login, edit profile, change password, delete account |
| Create / edit / delete / publish articles with images | Browse latest news (slider, featured story, breaking ticker) |
| Featured + breaking-news flags | Search by keyword, category and date range |
| Manage categories (CRUD) | Filter home page by today / week / month / category |
| Approve or reject student & club submissions with notes | Submit articles for approval, edit / withdraw pending ones |
| Manage announcements (CRUD, pin, publish, archive) | Comment on articles, delete own comments |
| Moderate comments (approve / reject / hide / delete) | Bookmark articles (AJAX), saved-articles page |
| Manage users (role, activate / deactivate, delete) | Follow categories and authors, personal news feed |
| Readership & engagement reports (chart, top 10, CSV, print-to-PDF) | Personal dashboard |

Security: password hashing (`password_hash`), prepared statements everywhere (no SQL injection),
`htmlspecialchars` on all output (no XSS), CSRF tokens on every form, session-ID regeneration on login,
upload type/size checks, PHP execution blocked in `/uploads`, secrets kept out of Git.

## Run locally (XAMPP)

1. Copy the `newsdesk` folder to `C:\xampp\htdocs\newsdesk`.
2. Start **Apache** and **MySQL** in XAMPP.
3. phpMyAdmin → Import `database/schema.sql`, then `database/seed.sql` (demo data).
4. Copy `config/db.example.php` → `config/db.php` (defaults already match XAMPP).
5. Open <http://localhost/newsdesk/>.

### Demo accounts

| Role | Login | Password |
|---|---|---|
| Admin | admin@nsbm.ac.lk | Admin@123 |
| Student | kasun@students.nsbm.ac.lk | Student@123 |
| Student | nimali@students.nsbm.ac.lk | Student@123 |

## Folder structure

```
newsdesk/
├── index.php, search.php, categories.php, 404.php
├── config/        db.example.php (template)  db.php (local, not in Git)
├── includes/      init.php, functions.php, header.php, footer.php, card.php
├── auth/          login.php, register.php, logout.php
├── articles/      view.php (public) + create/edit/delete (admin)
├── comments/      add.php, delete.php
├── bookmarks/     index.php, toggle.php
├── subscriptions/ index.php (feed), categories.php, authors.php
├── submissions/   create.php, edit.php, delete.php, my_submissions.php
├── announcements/ index.php
├── user/          dashboard.php, profile.php
├── admin/         index, articles, submissions, review_submission, categories,
│                  announcements, comments, users, reports
├── assets/        style.css, main.js, images
├── uploads/       uploaded images
└── database/      schema.sql, seed.sql, migration_day1.sql
```

## Database (10 tables)

`users`, `categories`, `articles`, `article_submissions`, `comments`, `bookmarks`,
`subscriptions`, `author_subscriptions`, `announcements`, `article_views`.

## Team

| Member | Main contribution |
|---|---|
| _Name (vihandu.wan)_ – Team leader | Project setup, shared includes, integration, deployment |
| _Name (nadunisilva)_ | Authentication, profile |
| _Name (udarikuruppu)_ | Home page, layout, CSS/JS, search & categories |
| _Name (Senelka Suwini)_ | Article page, comments, comment moderation |
| _Name (tharanaabey)_ | Article submissions and review workflow |
| _Name (induwara-g)_ | Admin articles, categories, announcements |
| _Name (csanduni)_ | Bookmarks, admin user management |
| _Name (chanakaudithasandaruwan)_ | Subscriptions, feed, dashboard, reports |
