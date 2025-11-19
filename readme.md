# Food App - Objednávkový systém rozvozu jídla

Webová aplikace pro objednávání a rozvoz jídla. Semestrální projekt z předmětu Webové aplikace.

## O projektu

Systém pro rozvoz potravin s více uživatelskými rolemi:
- **Nepřihlášení uživatelé** - prohlížení produktů, registrace
- **Konzumenti (zákazníci)** - objednávání produktů, správa košíku, historie objednávek
- **Dodavatelé** - správa produktů, vyřizování objednávek, statistiky
- **Administrátoři** - správa uživatelů, schvalování dodavatelů, kompletní přehled

## Technologie

- **Backend:** PHP 8.2+ (OOP, MVC architektura)
- **Frontend:** HTML5, CSS3, Bootstrap 5, JavaScript (AJAX)
- **Template Engine:** Twig 3
- **Databáze:** MySQL (přístup přes PDO)
- **Dependency Management:** Composer
- **Webserver:** Apache (XAMPP)

## Struktura projektu

```
web-foodapp/
├── app/
│   ├── Controllers/        # Controllery (MVC)
│   ├── Models/            # Modelové třídy (databáze)
│   ├── Views/
│   │   ├── templates/     # Twig šablony (.twig)
│   │   └── cache/         # Twig cache (auto-generovaný)
│   ├── Helpers/           # Pomocné třídy (TwigHelper, validace...)
│   └── Middleware/        # Middleware (autentizace, autorizace)
├── config/                # Konfigurační soubory
├── public/
│   ├── index.php          # Vstupní bod aplikace (router)
│   ├── css/               # Vlastní styly
│   ├── js/                # JavaScriptové soubory
│   └── uploads/           # Nahrané soubory (obrázky produktů)
├── vendor/                # Composer závislosti
├── database/              # SQL soubory (instalace, migrace)
├── .htaccess             # URL rewriting
├── composer.json         # Composer konfigurace
└── README.md             # Tento soubor
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

---

## Výchozí uživatelské účty

Po instalaci databáze budou dostupné tyto testovací účty:

| Role          | Email              | Heslo      | Popis                          |
|---------------|--------------------|------------|--------------------------------|
| Administrátor | admin@test.cz      | heslo123   | Plný přístup ke všemu          |
| Dodavatel     | dodavatel@test.cz  | heslo123   | Může přidávat produkty         |
| Konzument     | zakaznik@test.cz   | heslo123   | Může objednávat produkty       |

> POZOR: Po nasazení do produkce změňte všechna výchozí hesla!

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

- Šablony jsou v `app/Views/templates/`
- Po změně šablony se automaticky překompilují (díky `auto_reload: true`)
- V produkci nastavte `auto_reload: false` pro lepší výkon

Příklad použití v Controlleru:

```php
require_once '../app/Helpers/TwigHelper.php';

class ProductController {
    public function index() {
        $produkty = ...; // Načtení z databáze

        TwigHelper::display('produkty/seznam.twig', [
            'session' => $_SESSION,
            'produkty' => $produkty,
            'nazev' => 'Seznam produktů'
        ]);
    }
}
```

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

**Projekt:** Semestrální práce - Webové aplikace
**Autor:** [Vaše jméno]
**Email:** [váš email]
**Rok:** 2024/2025

---

## Checklist před odevzdáním

- [ ] Všechny požadavky ze zadání splněny
- [ ] Minimum 3 uživatelské role implementovány
- [ ] Bcrypt hashování hesel
- [ ] Ochrana proti XSS a SQL injection
- [ ] Upload souborů funkční a zabezpečený
- [ ] Responzivní design (PC + mobil)
- [ ] Databáze naplněná testovacími daty
- [ ] Dokumentace vytvořena (PDF, 3-4 strany)
- [ ] Export databáze připraven (`database/install.sql`)
- [ ] Projekt nahrán na students.kiv.zcu.cz (volitelně)
- [ ] Projekt v Git repozitáři (bonus body)
- [ ] Produkční hesla změněna
- [ ] Výpis chyb vypnutý pro produkci
