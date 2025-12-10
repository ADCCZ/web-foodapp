# Implementované funkce - Hodnocení semestrální práce

Tento dokument popisuje všechny implementované funkce aplikace Délicious podle bodového hodnocení.

---

## 4.1 Povinné požadavky (25 bodů)

### MVC architektura (OOP min. pro M a C) - 1 bod ✓

**Implementace:**
- Aplikace používá plný MVC pattern
- **Models** (`app/Models/`): Database.php, User.php, Product.php, Order.php - všechny OOP třídy
- **Controllers** (`app/Controllers/`): 8 controllerů (Admin, Cart, Home, Login, Order, Product, Register, Supplier) - všechny OOP třídy s metodami index() a dalšími
- **Views** (`app/Views/templates/`): 13 Twig šablon

**Kde najít:**
- `app/Models/Database.php` - Singleton pattern pro PDO
- `app/Controllers/HomeController.php` - příklad controlleru
- `app/Views/templates/base.twig` - master layout

---

### Správně oddělené vrstvy - 5 bodů ✓

**Implementace:**
- **Router vrstva**: `public/index.php` - single entry point, switch-based router
- **Controller vrstva**: Business logika oddělená v Controllers/, žádná logika přímo v routeru
- **Model vrstva**: Databázová komunikace pouze přes Models/, PDO prepared statements
- **View vrstva**: Prezentační logika pouze v Twig šablonách, žádné echo v controllerech
- **Helper vrstva**: `app/Helpers/TwigHelper.php` - pomocné třídy
- **Autoloader**: `app/autoload.php` - automatické načítání tříd

**Princip oddělení:**
- Router -> Controller -> Model -> Database
- Controller -> View (Twig render)
- Žádné SQL dotazy v controllerech, pouze volání metod modelů
- Žádné HTML v controllerech, pouze Twig rendering

---

### Responzivní design (PC a mobil) - 8 bodů ✓

**PC verze (3 body):**
- Bootstrap 5.3 grid systém
- Flexbox layout pro karty a seznamy
- Navbar s dropdown menu
- Responzivní tabulky
- Desktop-first přístup

**Mobilní verze (5 bodů):**
- Media query `@media (max-width: 768px)` v `public/css/style.css`
- Hamburger menu (Bootstrap collapse)
- Touch-friendly buttony a odkazy (min. 44x44px)
- Zmenšené font-size pro mobil
- Stack layout místo grid na malých obrazovkách
- Optimalizované velikosti obrázků
- Responzivní checkout summary (flexbox řádky)

**Kde testovat:**
- Otevřít DevTools (F12) -> Toggle device toolbar (Ctrl+Shift+M)
- Testováno na rozlišeních: 375px (mobil), 768px (tablet), 1920px (desktop)

---

### Kvalita webu (použitelný, plně funkční) - 6 bodů ✓

**Plná funkcionalita:**
- ✓ Registrace a přihlášení funguje
- ✓ Role-based přístup (konzument, dodavatel, admin, superadmin)
- ✓ Produkty - zobrazení, filtrace podle dodavatele
- ✓ Košík - přidávání, mazání, změna množství (AJAX)
- ✓ Checkout - formulář s validací, vytvoření objednávky
- ✓ Objednávky - seznam, detail, změna stavu
- ✓ Dodavatel - CRUD produktů s upload obrázků
- ✓ Admin - správa uživatelů, schvalování dodavatelů, správa všech produktů a objednávek
- ✓ SuperAdmin - správa všech včetně adminů

**Použitelnost:**
- Intuitivní navigace (navbar)
- Breadcrumbs a zpět tlačítka
- Feedback zprávy (success/error alerts)
- Loading stavy při AJAX operacích
- Validace formulářů (required fields)

---

### Kvalita designu - 5 bodů (pěkný design) ✓

**Moderní minimalistický design:**
- Čisté světlé pozadí (#f8fafc)
- Kontrastní barevná paleta (CSS proměnné)
- Bootstrap Icons v celé aplikaci
- Čisté karty s jemnými stíny (box-shadow)
- Moderní formuláře s ikonami
- Konzistentní spacing a typography
- Hover efekty (transitions)
- Moderní tlačítka s border-radius

**CSS proměnné (`public/css/style.css`):**
```css
--primary-dark: #0f172a;
--primary-blue: #1e40af;
--accent-orange: #f97316;
--accent-red: #ef4444;
--bg-light: #f8fafc;
--text-dark: #1e293b;
```

---

### Šifrování/hashování hesel (Bcrypt, Argon2) - 1 bod ✓

**Implementace:**
- Bcrypt (PASSWORD_BCRYPT) pomocí PHP funkce `password_hash()`
- Cost factor: 10 (výchozí)

**Kde najít:**
- `app/Controllers/RegisterController.php` - řádek s `password_hash($password, PASSWORD_BCRYPT)`
- `app/Controllers/LoginController.php` - řádek s `password_verify($password, $hash)`
- `database/install.sql` - testovací uživatelé mají bcrypt hash: `$2y$10$7QaWf3USCidc2qcsUx5F4e/2GIXmUA1f9k.Eg8U7OwPW8.nvVjDOi`

**Bezpečnost:**
- Hesla nikdy nejsou uložena v plain textu
- Automatické salting díky bcrypt
- Hash nelze zpětně dekódovat

---

### Ošetření útoků (SQL Injection, XSS) - 1 bod ✓

**SQL Injection ochrana:**
- PDO prepared statements ve všech Models
- Parametry bindovány pomocí `bindParam()` nebo `execute([$params])`
- Nikde není použita přímá konkatenace SQL

**Příklad (`app/Models/User.php`):**
```php
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
```

**XSS ochrana:**
- Twig auto-escape='html' zapnuté globálně (`app/Helpers/TwigHelper.php`)
- Všechny výstupy jsou automaticky escapovány
- `{{ variable }}` je vždy bezpečné
- Raw HTML pouze tam kde je to bezpečné (`{{ content|raw }}`)

**Kde najít:**
- `app/Helpers/TwigHelper.php` - řádek s `'autoescape' => 'html'`
- `app/Models/Database.php` - všechny dotazy používají prepared statements

---

## 4.2 Volitelná rozšíření

### Předvedení a odevzdání práce "do Vánoc" - 10 bodů ✓

**Implementace:**
- Projekt předveden 9. prosince 2025 (před Vánocemi)
- Kompletní dokumentace připravena
- Databázový skript pro instalaci
- README s pokyny k instalaci

---

### SuperAdmin ("neolivnitelnost adminů") - 4 bodů ✓

**Implementace:**
- Sloupec `is_super_admin` v tabulce `users`
- SuperAdmin má `is_super_admin = 1` a `role = 'admin'`
- Účet: `superadmin@test.cz` / `heslo123`

**Oprávnění:**
- Může spravovat všechny uživatele včetně ostatních administrátorů
- Nelze smazat (ochrana v admin controlleru)
- Nejvyšší oprávnění v systému

**Kde najít:**
- `database/install.sql` - řádek s INSERT superadmina (user_id = 1)
- `app/Controllers/AdminController.php` - kontrola `is_super_admin`

---

### Unikátní názvy souborů po uploadu - 2 body ✓

**Implementace:**
- Generování unikátního názvu: `uniqid() . '_' . time() . '_' . $originalName`
- Prevence kolizí názvů souborů
- Bezpečné ukládání do `public/uploads/`

**Kde najít:**
- `app/Controllers/SupplierController.php` - metoda pro upload obrázků
- `app/Controllers/AdminController.php` - stejná logika pro admin upload

**Příklad výsledného názvu:**
```
6756a3f2b1234_1733750000_pizza.jpg
```

---

### Další vychytávky - 1 bod ✓

**Implementované vychytávky:**
- Schvalovací workflow pro dodavatele (is_approved flag)
- AJAX operace bez reload stránky (košík, produkty)
- Dynamické načítání obrázků produktů
- Breadcrumbs a smart back buttons
- Session-based košík
- Real-time feedback (success/error zprávy)
- Responsive navbar s Bootstrap Icons

---

### Bootstrap nebo ekvivalent - 4 body (většinou použit) ✓

**Implementace:**
- Bootstrap 5.3.0 (CDN)
- Bootstrap Icons
- Použito v celé aplikaci

**Komponenty použité:**
- Grid systém (container, row, col-*)
- Navbar s collapse menu
- Cards pro produkty a objednávky
- Forms (form-control, form-label)
- Buttons (btn, btn-primary, btn-secondary)
- Alerts (alert-success, alert-danger)
- Modals (pro editaci produktů)
- Tables (table, table-striped)
- Utility classes (mt-*, mb-*, d-flex, justify-content-*)

**Kde najít:**
- `app/Views/templates/base.twig` - Bootstrap CDN linky
- Všechny .twig šablony používají Bootstrap třídy

---

### JavaScript a jQuery, popř. AngularJS - 2 body (použit na různých místech webu) ✓

**Implementace:**
- Vanilla JavaScript (Fetch API)
- Žádné jQuery ani Angular (není potřeba)

**Použití:**
- AJAX operace pro košík (add, update, remove, clear)
- AJAX formuláře (login, register)
- AJAX pro správu produktů (create, update, delete)
- Dynamické updaty DOM (množství v košíku, cena)
- Event listenery (click, submit)

**Kde najít:**
- `app/Views/templates/cart.twig` - block extra_js (AJAX košík)
- `app/Views/templates/products.twig` - AJAX přidání do košíku
- `app/Views/templates/admin_products.twig` - AJAX CRUD produktů
- `app/Views/templates/login.twig` - AJAX přihlášení

---

### AJAX - 2 body (použit na různých místech webu) ✓

**Implementace:**
- Fetch API pro všechny AJAX requesty
- JSON responses z controllerů
- `Content-Type: application/json` header

**AJAX funkce:**
1. **Košík:**
   - Přidání produktu do košíku (POST)
   - Aktualizace množství (POST)
   - Smazání položky (POST)
   - Vyprázdnění košíku (POST)

2. **Produkty:**
   - Vytvoření produktu (POST)
   - Editace produktu (POST)
   - Smazání produktu (POST)

3. **Přihlášení/Registrace:**
   - Login formulář (POST)
   - Register formulář (POST)

**Příklad response (`app/Controllers/CartController.php`):**
```php
header('Content-Type: application/json');
echo json_encode(['success' => true, 'message' => 'Produkt přidán']);
exit;
```

---

### Twig - 4 body (použit na různých místech webu) ✓

**Implementace:**
- Twig 3.22.0 (přes Composer)
- 13 šablon v `app/Views/templates/`
- Auto-escape zapnuté

**Šablony:**
1. `base.twig` - master layout (navbar, footer, bloky)
2. `home.twig` - úvodní stránka
3. `login.twig` - přihlášení
4. `register.twig` - registrace
5. `products.twig` - seznam produktů
6. `cart.twig` - košík
7. `checkout.twig` - pokladna
8. `orders.twig` - seznam objednávek
9. `order_detail.twig` - detail objednávky
10. `supplier.twig` - dashboard dodavatele
11. `admin_dashboard.twig` - admin dashboard
12. `admin_users.twig` - správa uživatelů
13. `admin_products.twig` - správa produktů
14. `admin_orders.twig` - správa objednávek

**Twig features použité:**
- Template inheritance (`{% extends %}`)
- Blocks (`{% block content %}`)
- Loops (`{% for %}`)
- Conditionals (`{% if %}`)
- Filters (`{{ price|number_format }}`)
- Includes (`{% include %}`)
- Auto-escaping (bezpečnost)

**Kde najít:**
- `app/Helpers/TwigHelper.php` - inicializace Twig
- `composer.json` - Twig dependency

---

### Použít NPM, Composer či ekvivalent - 1 bod ✓

**Implementace:**
- **Composer** pro správu PHP závislostí
- `composer.json` definuje Twig dependency
- `vendor/` složka s Composer packages

**Příkazy:**
```bash
composer install  # Instalace závislostí
composer update   # Aktualizace závislostí
```

**Kde najít:**
- `composer.json` - konfigurace Composeru
- `vendor/twig/twig/` - nainstalovaný Twig

---

### Zdrojové kódy umístěny na GIT - 1 bod ✓

**Implementace:**
- Git repository inicializován
- Pravidelné commity s popisnými zprávami
- Commit message style: lowercase s underscores a ampersands

**Příklady commit messages:**
```
add-product-listing_&_product-controller
modern-gradient-ui-design_&_documentation
product-edit-functionality_&_ui-fixes
shopping-cart-implementation_&_ajax-functionality
```

**Git info:**
- Repository: C:\xampp\htdocs\web-foodapp
- Branch: main
- Commity: Pravidelné commity během vývoje

---