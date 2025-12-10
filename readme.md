# Délicious - Objednávkový systém rozvozu jídla

Moderní webová aplikace pro objednávání a rozvoz jídla s elegantním designem. Semestrální projekt z předmětu Webové aplikace.

## O projektu

**Délicious** je systém pro rozvoz potravin s více uživatelskými rolemi:
- **Nepřihlášení uživatelé** - prohlížení produktů, registrace
- **Konzumenti (zákazníci)** - objednávání produktů, správa košíku, historie objednávek
- **Dodavatelé** - správa produktů, zobrazení objednávek vlastních produktů
- **Administrátoři** - správa uživatelů, schvalování dodavatelů, správa všech produktů a objednávek
- **Super Administrátor** - nejvyšší oprávnění, správa všech uživatelů včetně administrátorů, nelze smazat

## Technologie

- **Backend:** PHP 8.2.12 (OOP, MVC architektura)
- **Frontend:** HTML5, CSS3, Bootstrap 5, JavaScript (AJAX)
- **Template Engine:** Twig 3
- **Databáze:** MySQL (přístup přes PDO)
- **Dependency Management:** Composer
- **Webserver:** Apache (XAMPP)

## Funkce


- **Moderní minimalistický design** - Čisté pozadí, kontrastní barvy, elegantní layout
- **Responzivní rozhraní** - Bootstrap 5 + vlastní CSS, optimalizováno pro mobil i PC
- **Bezpečné přihlášení** - Bcrypt hashování, SQL injection a XSS ochrana
- **Košík a objednávky** - AJAX-based nákupní košík, kompletní checkout proces
- **Správa produktů** - Upload obrázků (JPG, PNG, GIF, WEBP), CRUD operace
- **Role-based přístup** - Konzumenti, Dodavatelé, Administrátoři, Super Administrátor
- **AJAX funkcionalita** - Košík, produkty, formuláře bez reload stránky
- **Schvalovací workflow** - Dodavatelé musí být schváleni administrátorem

## Struktura projektu

```
web-foodapp/
├── app/
│   ├── Controllers/        # 8 Controllerů (Admin, Cart, Home, Login, Order, Product, Register, Supplier)
│   ├── Models/            # 4 Modely (Database, User, Product, Order)
│   ├── Views/
│   │   ├── templates/     # 13 Twig šablon (base, home, login, register, products, cart, checkout, orders, order_detail, supplier, admin_*)
│   │   └── cache/         # Twig cache (auto-generovaný)
│   ├── Helpers/           # TwigHelper.php
│   └── autoload.php       # Autoloader pro třídy
├── public/
│   ├── index.php          # Vstupní bod aplikace (switch-based router)
│   ├── css/
│   │   └── style.css      # Vlastní CSS (minimalistický design, responzivní)
│   └── uploads/           # Nahrané obrázky produktů
├── vendor/                # Composer závislosti (Twig 3.22)
├── database/
│   ├── install.sql        # Kompletní instalační skript (databáze + testovací data)
│   └── schema.md          # Popis databázové struktury
├── .htaccess              # URL rewriting
├── composer.json          # Composer konfigurace
├── documentation.tex      # LaTeX dokumentace
├── documentation.pdf      # Kompilovaná dokumentace (5 stran)
├── CLAUDE.md              # Pokyny pro Claude Code
└── README.md              # Tento soubor
```

---

## Instalace a zprovoznění projektu

### Požadavky

- **PHP** 8.0 nebo vyšší
- **MySQL** 5.7+ nebo MariaDB
- **Apache** webserver s `mod_rewrite`
- **Composer** (https://getcomposer.org/)
- **XAMPP** (doporučeno pro Windows) nebo jiný LAMP/WAMP stack

### Krok 1: Klonování projektu

```bash
# Přes Git
git clone <URL_REPOZITÁŘE> C:\xampp\htdocs\web-foodapp

# Nebo stáhněte ZIP a rozbalte do C:\xampp\htdocs\web-foodapp
```

### Krok 2: Instalace závislostí

```bash
# Přejděte do složky projektu
cd C:\xampp\htdocs\web-foodapp

# Nainstalujte Composer závislosti (Twig atd.)
composer install
```

### Krok 3: Nastavení databáze

#### 3.1 Vytvoření databáze

1. Spusťte XAMPP a zapněte **Apache** a **MySQL**
2. Otevřete phpMyAdmin: http://localhost/phpmyadmin
3. Vytvořte novou databázi s názvem `foodapp`
   - Collation: `utf8mb4_general_ci`

#### 3.2 Import databáze

```sql
-- V phpMyAdmin vyberte databázi 'foodapp' a importujte soubor:
database/install.sql
```

Nebo přes příkazovou řádku:
```bash
mysql -u root -p foodapp < database/install.sql
```

#### 3.3 Konfigurace připojení

Upravte soubor `app/Models/Database.php` podle vašeho nastavení:

```php
private static $host = 'localhost';
private static $db   = 'foodapp';
private static $user = 'root';      // Váš MySQL uživatel
private static $pass = '';          // Vaše MySQL heslo
```

### Krok 4: Nastavení Apache (.htaccess)

Ujistěte se, že máte povolený `mod_rewrite` v Apache:

1. Otevřete `C:\xampp\apache\conf\httpd.conf`
2. Najděte řádek `#LoadModule rewrite_module modules/mod_rewrite.so`
3. Odkomentujte ho (odstraňte `#`)
4. Restartujte Apache v XAMPP

### Krok 5: Nastavení oprávnění (Linux/Mac)

```bash
# Nastavte práva zápisu pro cache a uploads
chmod -R 775 app/Views/cache
chmod -R 775 public/uploads

# Nastavte vlastníka na webserver (např. www-data)
chown -R www-data:www-data app/Views/cache
chown -R www-data:www-data public/uploads
```

### Krok 6: Spuštění aplikace

Otevřete prohlížeč a přejděte na:

```
http://localhost/web-foodapp/
```

Měli byste vidět moderní home page aplikace **Délicious** s čistým minimalistickým designem a možností přihlášení/registrace.

---

## Výchozí uživatelské účty

Po instalaci databáze budou dostupné tyto testovací účty (všechna hesla: **heslo123**):

| Role                 | Email                  | Heslo      | Popis                                              |
|----------------------|------------------------|------------|----------------------------------------------------|
| Super Administrátor  | superadmin@test.cz     | heslo123   | Nejvyšší oprávnění, správa všech včetně adminů     |
| Administrátor        | admin@test.cz          | heslo123   | Správa uživatelů, schvalování dodavatelů           |
| Dodavatel (schválen) | dodavatel@test.cz      | heslo123   | Pizza House - 4 produkty                           |
| Dodavatel (schválen) | dodavatel2@test.cz     | heslo123   | Burger King - 4 produkty                           |
| Dodavatel (čeká)     | dodavatel3@test.cz     | heslo123   | Sushi Bar - neschválený, nemůže se přihlásit       |
| Konzument            | zakaznik@test.cz       | heslo123   | Jan Novák - 2 testovací objednávky                 |
| Konzument            | zakaznik2@test.cz      | heslo123   | Marie Svobodová - 2 testovací objednávky           |

> **POZOR:** Po nasazení do produkce změňte všechna výchozí hesla!

---

## Vývoj

### Zapnutí výpisu chyb (development mode)

V souboru `public/index.php` jsou již nastaveny direktivy pro zobrazení chyb:

```php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
```

> **Produkce:** Před nasazením na produkční server tyto řádky **smažte** nebo nastavte na `0`.

### Práce s Twig šablonami

Šablony jsou v `app/Views/templates/` a používají template engine Twig 3.

**Vytvoření nové stránky:**

1. **Vytvořte Controller:**
```php
<?php
require_once '../app/Helpers/TwigHelper.php';

class ProductController {
    public function index() {
        $produkty = []; // Načtení z databáze

        TwigHelper::display('produkty/seznam.twig', [
            'session' => $_SESSION,
            'produkty' => $produkty,
            'nazev' => 'Seznam produktů'
        ]);
    }
}
```

2. **Vytvořte Twig šablonu** (`app/Views/templates/produkty/seznam.twig`):
```twig
{% extends "base.twig" %}

{% block title %}{{ nazev }}{% endblock %}

{% block content %}
    <h1>{{ nazev }}</h1>

    {% for produkt in produkty %}
        <div class="card card-modern">
            <h3>{{ produkt.name }}</h3>
            <p>{{ produkt.description }}</p>
            <strong>{{ produkt.price }} Kč</strong>
        </div>
    {% endfor %}
{% endblock %}
```

**Užitečné Twig konstrukce:**
```twig
{# Komentář #}
{{ promenna }}                          {# Výpis proměnné (auto-escaped) #}
{{ promenna|upper }}                     {# Filtry (UPPERCASE) #}
{% if session.user_id is defined %}     {# Podmínka #}
{% for item in items %}                 {# Cyklus #}
{% include 'components/header.twig' %}  {# Include #}
```

**Výhody Twigu:**
- Automatické escapování XSS
- Čistší syntax než PHP
- Dědičnost šablon (extends)
- Cache pro rychlost

### Přidání nové stránky

1. **Vytvořte Controller:** `app/Controllers/MojeController.php`
2. **Vytvořte View:** `app/Views/templates/moje-stranka.twig`
3. **Přidejte route** do `public/index.php`:

```php
case 'moje-stranka':
    require_once '../app/Controllers/MojeController.php';
    $controller = new MojeController();
    $controller->index();
    break;
```

### Git workflow

```bash
# Přidání změn
git add .

# Commit
git commit -m "Popis změny"

# Push do repozitáře
git push origin main
```

---

## Testování

### Testování registrace
1. Otevřete http://localhost/web-foodapp/?page=register
2. Zaregistrujte nového uživatele
3. Ověřte v databázi, že heslo je hashované (bcrypt)

### Testování přihlášení
1. Otevřete http://localhost/web-foodapp/?page=login
2. Přihlaste se testovacím účtem
3. Zkontrolujte, že session funguje

### Testování XSS ochrany
- Twig automaticky escapuje výstup přes `{{ promenna }}`
- Pro raw HTML použijte `{{ promenna|raw }}` (pouze když je to bezpečné!)

---

## Časté problémy a řešení

### Chyba: "Twig not found"
```bash
# Řešení: Přeinstalujte Composer závislosti
composer install
```

### Chyba: "Access denied for user 'root'@'localhost'"
- Zkontrolujte heslo v `app/Models/Database.php`
- Zkontrolujte, že MySQL běží v XAMPP

### Chyba 404 - stránka nenalezena
- Zkontrolujte `.htaccess` v kořenové složce
- Ověřte, že `mod_rewrite` je zapnutý v Apache

### Prázdná stránka bez chyb
- Zapněte zobrazení chyb v `public/index.php`
- Zkontrolujte PHP error log: `C:\xampp\php\logs\php_error_log`

### Cache problém - změny se nepromítají
```bash
# Smažte Twig cache
rm -rf app/Views/cache/*
# Nebo ručně smažte obsah složky app/Views/cache/ (kromě .gitkeep)
```

### Kódování češtiny (špatné znaky)
- Zkontrolujte, že databáze je `utf8mb4_general_ci`
- Zkontrolujte, že `.htaccess` obsahuje `AddDefaultCharset OFF`
- Soubory ukládejte v UTF-8 kódování (bez BOM)
- V editoru (VS Code) nastavte: `"files.encoding": "utf8"`

---

## Dokumentace a odkazy

- **Bootstrap 5:** https://getbootstrap.com/docs/5.3/
- **Twig:** https://twig.symfony.com/doc/3.x/
- **PHP PDO:** https://www.php.net/manual/en/book.pdo.php
- **Composer:** https://getcomposer.org/doc/

---

## Licence a autor

**Projekt:** Semestrální práce - Webové aplikace (KIV/WEB)
**Název aplikace:** Délicious
**Autor:** Oldřich Daš
**Email:** oldasvehla@seznam.cz
**Datum vytvoření:** 9. prosince 2025

## Design

Aplikace využívá moderní minimalistický design s:
- Čisté světlé pozadí (#f8fafc)
- Kontrastní barvy bez gradientů
- Bootstrap Icons v celé aplikaci
- Jednoduché hover efekty
- Moderní formuláře s ikonami
- Čistý kartový design s jemnými stíny
- Plně responzivní layout (mobil + desktop)
- CSS proměnné pro konzistentní barevnou paletu

---