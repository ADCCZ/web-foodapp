# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**Délicious** (wordplay: Delicious + Delivery) is a modern food delivery ordering system being developed as a semester project for a web development course. The application features a beautiful gradient UI design and allows customers to browse products, place orders, suppliers to manage their products, and administrators to manage users and orders.

**Tech Stack:** PHP, MySQL (via PDO), Bootstrap 5, AJAX, Twig (planned), MVC architecture

**Target Roles:**
- Unprivileged users (browse products)
- Customers/Consumers (order products)
- Suppliers (add products, manage orders)
- Administrators (manage users, approve suppliers)

## Architecture

### MVC Structure

The application follows a custom MVC pattern with a **single entry point** routing system:

- **Entry Point:** `public/index.php` - All requests route through this file
- **Routing:** Simple switch-based router using `$_GET['page']` parameter (e.g., `?page=login`)
- **Controllers:** Located in `app/Controllers/`, handle business logic and call views
- **Models:** Located in `app/Models/`, handle database interactions
- **Views:** Located in `app/Views/`, render HTML (currently pure PHP, transitioning to Twig)

### Database Connection Pattern

The `Database` class (app/Models/Database.php) uses a **static singleton pattern**:
- Single PDO connection shared across the application
- Called via `Database::getConnection()`
- Configured for database: `foodapp`, user: `root`, no password (XAMPP default)
- Uses prepared statements to prevent SQL injection

### AJAX Pattern

Controllers return JSON responses for AJAX requests:
- Controllers check `$_SERVER['REQUEST_METHOD'] === 'POST'` for form submissions
- Set `header('Content-Type: application/json')` for AJAX responses
- Return `json_encode(['success' => bool, 'message' => string])`
- Views use vanilla JavaScript `fetch()` API for AJAX calls

### Session Management

- Session started in `public/index.php` via `session_start()`
- User authentication stored in `$_SESSION['user_id']`, `$_SESSION['role']`, `$_SESSION['jmeno']`
- Logout handled by `session_destroy()` in LoginController

## Development Environment

**Server:** XAMPP (Apache + MySQL) on Windows
**Document Root:** `C:\xampp\htdocs\web-foodapp`
**URL Rewriting:** `.htaccess` redirects all requests to `public/` directory
**Database:** MySQL database named `foodapp`

### Running the Application

1. Start XAMPP (Apache + MySQL)
2. Navigate to `http://localhost/web-foodapp/`
3. All requests automatically route through `public/index.php`

### Database Setup

Database credentials in `app/Models/Database.php`:
```php
$host = 'localhost';
$db = 'foodapp';
$user = 'root';
$pass = '';
```

Expected tables (based on current controllers):
- `users` - with columns: `user_id`, `email`, `password` (bcrypt hashed), `jmeno`, `role`, `is_approved`

### Error Display

Development mode enabled in `public/index.php`:
```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

## Key Conventions

### Controllers

- Named as `{Name}Controller.php`
- Require database via `require_once '../app/Models/Database.php'`
- Have `index()` method for GET requests (display view)
- Have processing methods for POST requests (handle form submissions)
- AJAX methods set JSON header and return JSON response with `exit`

### Views

- Uses **Twig template engine** (v3.22.0)
- Located in `app/Views/templates/`
- Base template: `base.twig` with modern gradient design
- Current templates: `home.twig`, `login.twig`, `register.twig`
- Include inline JavaScript for AJAX functionality in `{% block extra_js %}`
- Forms use `fetch()` API to POST to `?page={controller}` endpoints
- Bootstrap 5 + Bootstrap Icons + custom CSS (`public/css/style.css`)

### Security Measures Required

Per semester project requirements:
- **XSS Protection:** Use `htmlspecialchars()` or Twig auto-escaping
- **SQL Injection:** Use PDO prepared statements (already implemented)
- **Password Hashing:** Use `password_hash()` with `PASSWORD_BCRYPT` (already implemented)
- **File Upload Security:** Validate file types, sizes, sanitize filenames (planned)

### Routing Pattern

Add new pages to `public/index.php` switch statement:
```php
case 'newpage':
    require_once '../app/Controllers/NewPageController.php';
    $controller = new NewPageController();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->processAction();
    } else {
        $controller->index();
    }
    break;
```

## Design System

**Modern Minimalist UI:**
- Clean light background (`#f8fafc`)
- Contrasting colors without gradients
- Bootstrap Icons throughout
- Simple hover effects (no complex animations)
- Modern form inputs with icons
- Clean card design with subtle shadows
- Responsive layout (mobile + desktop)

**CSS Variables:**
```css
--primary-dark: #0f172a;
--primary-blue: #1e40af;
--accent-orange: #f97316;
--accent-red: #ef4444;
--bg-light: #f8fafc;
--bg-gray: #f1f5f9;
--text-dark: #1e293b;
--text-gray: #64748b;
```

## Current Implementation Status

**✅ Implemented:**
- Modern minimalist UI design with Twig templates
- HomeController with hero section and features
- Login/logout with AJAX and session management
- Registration with role selection (customer/supplier)
- Supplier approval workflow (is_approved field)
- Password hashing (bcrypt)
- SQL injection protection (PDO prepared statements)
- Responsive navbar with Bootstrap Icons
- Clean card design with contrasting colors
- Bootstrap 5 integration (mostly used)
- Twig template engine (used in various places)
- AJAX for login/register (used in places)
- Composer for dependency management
- Database schema with 4 tables (users, products, orders, order_items)
- Git repository

**Current Points Estimate:**
- Mandatory: ~18-20 points (missing full responsive mobile, complete design)
- Optional: ~11 points (Bootstrap 4, Twig 4, AJAX 1, Composer 1, Git 1)
- Total: ~29-31 points (before full implementation)

**⏳ In Progress / Planned:**
- Product management (CRUD with image upload)
- Shopping cart (AJAX-based)
- Order processing
- Admin panel for user/supplier approval
- File upload for product images (with unique filenames)
- Middleware for route protection
- Mobile responsive testing and fixes
- More AJAX functionality
- Code comments and documentation

## Code Style Guidelines

**Important:**
- DO NOT use emojis in code, comments, or documentation files (.md)
- Use clear, professional language
- Keep code comments concise and technical
- Documentation should be clean and professional

**Git Commit Messages:**
- Use lowercase with underscores and ampersands
- Format: `feature-description_&_additional-info`
- Examples:
  - `add-product-listing_&_product-controller`
  - `modern-gradient-ui-design_&_documentation`
  - `product-edit-functionality_&_ui-fixes`
- Keep messages concise and descriptive

## Academic Requirements & Grading

### Grading System

**Mandatory Requirements (max 25 points):**
- MVC architecture (OOP min. M and C): 1 point
- Fewer errors: 1 point
- Properly separated layers: 5 points
- Responsive design (PC): 3 points
- Responsive design (mobile, no bugs): 5 points
- Website quality (usable, fully functional): 6 points
- Design quality:
  - Normal design: 2 points
  - Nice design: 5 points
  - Advanced design: 7 points
- Password hashing (Bcrypt, Argon2): 1 point
- Attack protection (SQL Injection, XSS): 1 point

**Optional Extensions (max 34 points + 10 bonus):**
- Presentation and submission "before Christmas" (bonus): 10 points
- SuperAdmin ("admin immutability"): 4 (2) points
- Use of namespaces: 1 point
- Unique filenames after upload: 2 points
- Additional features: 1 point
- Bootstrap or equivalent:
  - Partially used: 2 points
  - Mostly used: 4 points
- JavaScript/jQuery/Angular:
  - Used in places: 1 point
  - Used in various places: 2 points
- AJAX:
  - Used in places: 1 point
  - Used in various places: 2 points
- Twig:
  - Used in places: 2 points
  - Used in various places: 4 points
- Custom REST API:
  - Parts (e.g., one URL and POST for everything): 2 points
  - Complete FullREST endpoint: 4 points
- Custom SSE (+API): 1-2 points
- WYSIWYG editor (e.g., CKEditor): 3-4 points
- NPM, Composer or equivalent: 1 point
- Docker or equivalent (installs NPM and Composer, runs database environment): 2-3 points
- Source code on GIT (gitlab, github): 1 point

**Penalties:**
- Invalid HTML (deduced invalidity is allowed, e.g., when using frameworks): 10 points
- Errors or warnings in PHP: 5-10 points
- Database has fewer than 3 tables: 10 points
- Missing file upload: 4 points
- Too simple SP assignment: up to 7 points
- Messy code, missing comments: up to 5 points

### Project Requirements

This project must meet specific semester requirements:
- Minimum 3 user roles with role-based access control
- File upload functionality
- Responsive design (PC + mobile)
- Protection against XSS and SQL injection
- Bcrypt password hashing
- Documentation (3-4 pages PDF)
- Database export scripts for installation

**Forbidden:** Using complete PHP frameworks (Nette, Symfony) - only components allowed with approval
**Recommended:** Bootstrap for responsive design, GitHub for version control (bonus points)
