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

**Modern Gradient UI:**
- Purple-to-blue gradient background (`linear-gradient(135deg, #667eea 0%, #764ba2 100%)`)
- Glass effect cards with backdrop blur
- Bootstrap Icons throughout
- Smooth animations (fade-in-up, pulse, hover effects)
- Modern form inputs with icons
- Gradient buttons with hover effects
- Responsive layout (mobile + desktop)

**CSS Variables:**
```css
--primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
--success-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
--food-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
```

## Current Implementation Status

**✅ Implemented:**
- Modern gradient UI design with Twig templates
- HomeController with hero section and features
- Login/logout with AJAX and session management
- Registration with role selection (customer/supplier)
- Supplier approval workflow (is_approved field)
- Password hashing (bcrypt)
- SQL injection protection (PDO prepared statements)
- Responsive navbar with Bootstrap Icons
- Glass effect cards and modern forms

**⏳ In Progress / Planned:**
- Product management (CRUD)
- Shopping cart (AJAX-based)
- Order processing
- Admin panel for user/supplier approval
- File upload for product images
- Middleware for route protection

## Code Style Guidelines

**Important:**
- DO NOT use emojis in code, comments, or documentation files (.md)
- Use clear, professional language
- Keep code comments concise and technical
- Documentation should be clean and professional

## Academic Requirements

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
