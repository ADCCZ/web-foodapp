# Průběh implementace - Délicious

Tento soubor sleduje postup implementace semestrální práce.

**Název aplikace:** Délicious (Delicious + Delivery)
**Typ:** Objednávkový systém pro rozvoz jídla

---

## FÁZE 1: PŘÍPRAVA PROSTŘEDÍ A STRUKTUR (DOKONČENO)

### Krok 1.1 - Instalace závislostí (DOKONČENO)
**Datum:** 19.11.2024

**Co bylo implementováno:**
- Ověřen Composer (verze 2.8.11)
- Vytvořen `composer.json` s PSR-4 autoloadingem
- Nainstalován Twig 3.22.0 + závislості (symfony/polyfill-mbstring, symfony/polyfill-ctype, symfony/deprecation-contracts)
- Vytvořena pomocná třída `TwigHelper` (`app/Helpers/TwigHelper.php`)
  - Metoda `getTwig()` - singleton pattern pro Twig engine
  - Metoda `render()` - vrací HTML string
  - Metoda `display()` - přímo vypíše HTML
- Vytvořeny základní Twig šablony:
  - `app/Views/templates/base.twig` - základní layout s Bootstrap 5, navigací, bloky pro title/content/css/js
  - `app/Views/templates/login.twig` - přihlašovací formulář (extends base.twig)
- Upraven `LoginController` pro použití Twigu
- Vytvořena struktura složek:
  - `app/Views/templates/` - pro Twig šablony
  - `app/Views/cache/` - pro Twig cache (s .gitkeep)
  - `app/Helpers/` - pro pomocné třídy
  - `app/Middleware/` - připraveno pro budoucí použití
- Vytvořen `.gitignore` (vendor, cache, uploads, IDE soubory)
- Vytvořena dokumentace

**Výsledek:** Twig je funkční a připraven k použití v celé aplikaci

**Dokumentace:**
- Vytvořen `README.md` - kompletní tutoriál pro instalaci a zprovoznění projektu na jiných počítačích
- Vytvořen `PROGRESS.md` - sledování postupu implementace
- Vytvořen `CLAUDE.md` - dokumentace pro budoucí práci s Claude Code
- Dokumentace Twig integrována do README.md

---

### Krok 1.2 - Návrh databáze (DOKONČENO)
**Datum:** 19.11.2024

**Co bylo implementováno:**
- Vytvořena složka `database/` pro SQL soubory
- Vytvořen `database/install.sql` - kompletní instalační SQL skript
- Navrženy 4 tabulky s následující strukturou:

**Tabulka `users`:**
- Ukládá všechny uživatele (konzumenti, dodavatelé, admini)
- Sloupce: user_id (PK), email (UNIQUE), password (bcrypt), jmeno, role (ENUM), is_approved, created_at
- Role: 'konzument', 'dodavatel', 'admin'
- Indexy na: role, is_approved

**Tabulka `products`:**
- Produkty přidané dodavateli
- Sloupce: product_id (PK), supplier_id (FK→users), name, description, price (DECIMAL), image, created_at
- Foreign Key s CASCADE DELETE

**Tabulka `orders`:**
- Objednávky zákazníků
- Sloupce: order_id (PK), customer_id (FK→users), status (ENUM), total_price, created_at
- Stavy: 'pending', 'processing', 'completed', 'cancelled'
- Foreign Key s CASCADE DELETE

**Tabulka `order_items`:**
- M:N vztah mezi orders a products
- Sloupce: order_item_id (PK), order_id (FK), product_id (FK), quantity, price
- Cena se ukládá z důvodu historie (pokud se změní cena produktu)

**Testovací data:**
- 6 uživatelů (1 admin, 3 dodavatelé, 2 zákazníci)
- 10 produktů (pizzy, burgery, sushi)
- 4 objednávky v různých stavech
- Všichni uživatelé mají heslo: `heslo123` (bcrypt hash)
- Jeden dodavatel neschválen (pro testování schvalování)

**Dokumentace:**
- Vytvořen `database/schema.md` - detailní popis schématu
  - Diagramy vztahů mezi tabulkami
  - Popis všech sloupců a indexů
  - Příklady SQL dotazů (nejprodávanější produkty, objednávky dodavatele, atd.)
  - Seznam testovacích účtů

**Výsledek:** Databázové schéma je kompletní a připravené k importu

---

### Krok 1.3 - Moderní design a UI (DOKONČENO)
**Datum:** 19.11.2024

**Co bylo implementováno:**
- Vytvořen vlastní moderní CSS (`public/css/style.css`)
  - Gradient pozadí (fialovo-modré)
  - Moderní karty s glass effect
  - Smooth animace (fade-in-up, pulse, hover efekty)
  - Moderní formuláře s ikonami
  - Responzivní design
  - Loading spinner animace
- Upravena `base.twig` - moderní layout
  - Přidány Bootstrap Icons
  - Moderní navigace s ikonami
  - Footer s ikonou srdce
  - Link na vlastní CSS
- Přepsána `login.twig` na moderní design
  - Gradientní header s ikonou
  - Ikony u inputů
  - Glass effect karta s demo účty
  - AJAX s loading spinnerem
- Vytvořena `register.twig` - moderní registrace
  - Výběr role (Zákazník/Dodavatel) s velkými tlačítky
  - Ikony u všech inputů
  - AJAX validace a odeslání
  - Info o schválení dodavatele
- Aktualizován `RegisterController`
  - Používá Twig
  - Zpracovává volbu role
  - Dodavatelé se registrují s `is_approved = 0`
- Vytvořena `home.twig` - moderní homepage
  - Hero sekce s velkým názvem
  - Call-to-action tlačítka
  - 3 karty s výhodami (glass effect)
  - Sekce "Jak to funguje" (4 kroky)
  - Responzivní layout
- Vytvořen `HomeController`
- **Přejmenováno na "Délicious"** - wordplay Delicious + Delivery

**Výsledek:** Aplikace má kompletní moderní UI s gradientním designem

---

## FÁZE 2: ZÁKLADNÍ FUNKCE (ČÁSTEČNĚ DOKONČENO)

### Krok 2.1 - Autentizace (ČÁSTEČNĚ DOKONČENO)
**Už implementováno:**
- LoginController s AJAX
- RegisterController s výběrem role
- Bcrypt hashování hesel
- Session management

**Zbývá:**
- Middleware pro ochranu stránek
- Kontrola schválení dodavatelů při přihlášení

### Krok 2.2 - Homepage a seznam produktů (ČÁSTEČNĚ DOKONČENO)
**Už implementováno:**
- Moderní homepage (home.twig)

**Zbývá:**
- ProductController - seznam produktů
- Zobrazení produktů s obrázky
- Filtry a vyhledávání

### Krok 2.3 - Registrace s rolí (DOKONČENO)
- Možnost vybrat roli při registraci
- Dodavatelé čekají na schválení

---

## FÁZE 3-8: DALŠÍ IMPLEMENTACE (ČEKÁ)

_(Budou přidány postupně při dokončování)_

---

## Celkový postup

**Fáze 1 - Příprava: DOKONČENO**
- [DONE] Krok 1.1 - Instalace závislostí (Composer, Twig)
- [DONE] Krok 1.2 - Návrh databáze
- [DONE] Krok 1.3 - Moderní design a UI

**Fáze 2 - Základní funkce: ČÁSTEČNĚ**
- [PARTIAL] Krok 2.1 - Autentizace (částečně)
- [PARTIAL] Krok 2.2 - Homepage (částečně)
- [DONE] Krok 2.3 - Registrace s rolí

**Zbývá implementovat:**
- [TODO] ProductController - správa produktů
- [TODO] CartController - nákupní košík
- [TODO] OrderController - objednávky
- [TODO] AdminController - administrace
- [TODO] Middleware - ochrana stránek
- [TODO] Upload obrázků produktů
- [TODO] Kompletní AJAX funkce

**Legenda:**
- [DONE] Dokončeno
- [PARTIAL] Částečně dokončeno
- [TODO] Čeká na implementaci
- [BLOCKED] Problém/Blokováno
