# School Exam Paper Generation System (PHP OOP)

A web-based role-driven exam management platform for schools.

## Features

### Authentication & Authorization
- Secure email/password login
- Role-based permissions:
  - **Admin**: full access
  - **User (School Head)**: paper creation/review/finalization

### Admin Capabilities
- User management (CRUD): Admin + School Head roles
- Academic hierarchy management:
  - Classes
  - Subjects under class
  - Chapters under subject
- Question bank management (CRUD-ready for create/delete and update endpoint):
  - MCQ, True/False, Fill in the Blanks, Short, Long, SLO-Based
  - Tags: class, subject, chapter, type
  - Marks, difficulty level, SLO reference
- Question filters + search + pagination
- View and print all generated papers

### School Head Capabilities
- Create exam papers by selecting class/subject/chapters
- Configure question quantity by type
- Smart random generation (no duplicate questions in same paper)
- Review and edit paper:
  - Replace question
  - Remove question
  - Reorder questions
- Finalize and print paper

## Stack
- PHP 8+
- SQLite
- Bootstrap 5

## Local setup

```bash
php scripts/setup.php
php -S 0.0.0.0:8000 -t public
```

Open: `http://localhost:8000`

## Default users
- Admin: `admin@school.local / admin12345`
- School Head: `head@school.local / head12345`

## Project structure
- `app/Controllers` – request handlers
- `app/Models` – DB interaction
- `app/Views` – templates
- `public/index.php` – front controller/router
- `scripts/setup.php` – migration + seed
