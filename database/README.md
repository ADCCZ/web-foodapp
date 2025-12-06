# Databáze - Instalace a správa

Databáze pro aplikaci **Délicious** - objednávkový systém rozvozu jídla.

## Rychlá instalace

### Varianta 1: Přes phpMyAdmin (doporučeno pro začátečníky)

1. Spusťte XAMPP a zapněte **Apache** a **MySQL**
2. Otevřete phpMyAdmin: http://localhost/phpmyadmin
3. Klikněte na "Nová" (vlevo nahoře) pro vytvoření databáze
4. Zadejte název: `foodapp`
5. Vyberte Collation: `utf8mb4_general_ci`
6. Klikněte "Vytvořit"
7. Vyberte databázi `foodapp` (v levém menu)
8. Klikněte na záložku "Import"
9. Klikněte "Vybrat soubor" a vyberte `install.sql`
10. Klikněte "Provést" (dole na stránce)

Hotovo! Databáze je vytvořena a naplněna testovacími daty.

### Varianta 2: Přes příkazovou řádku (pro pokročilé)

```bash
# 1. Přejděte do složky projektu
cd C:\xampp\htdocs\web-foodapp

# 2. Vytvořte databázi (heslo je obvykle prázdné pro XAMPP)
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS foodapp CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"

# 3. Importujte schéma a data
mysql -u root -p foodapp < database/install.sql

# Pokud nemáte heslo (XAMPP default):
mysql -u root foodapp < database/install.sql
```

---

## Ověření instalace

Po importu zkontrolujte v phpMyAdmin:

### Měli byste vidět 4 tabulky:
- `users` (6 záznamů)
- `products` (10 záznamů)
- `orders` (4 záznamy)
- `order_items` (9 záznamů)

### Testovací účty (heslo pro všechny: heslo123):
- **admin@test.cz** - administrátor
- **dodavatel@test.cz** - Pizza House (schválený dodavatel)
- **dodavatel2@test.cz** - Burger King (schválený dodavatel)
- **dodavatel3@test.cz** - Sushi Bar (NESCHVÁLENÝ - pro testování)
- **zakaznik@test.cz** - Jan Novák (zákazník)
- **zakaznik2@test.cz** - Marie Svobodová (zákazník)

---

## Konfigurace připojení

Po instalaci databáze upravte `app/Models/Database.php`:

```php
private static $host = 'localhost';
private static $db   = 'foodapp';
private static $user = 'root';      // Změňte pokud používáte jiného uživatele
private static $pass = '';          // Změňte pokud máte heslo
```

---

## Obnovení databáze (reset)

Pokud chcete vrátit databázi do původního stavu:

### Varianta A: Smazat a znovu importovat
```bash
# Smažte databázi
mysql -u root -p -e "DROP DATABASE IF EXISTS foodapp;"

# Znovu vytvořte
mysql -u root -p -e "CREATE DATABASE foodapp CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"

# Importujte
mysql -u root -p foodapp < database/install.sql
```

### Varianta B: Přes phpMyAdmin
1. Vyberte databázi `foodapp`
2. Klikněte na záložku "Operace"
3. V sekci "Odstranit data nebo tabulky" klikněte "Smazat databázi"
4. Znovu vytvořte databázi a importujte `install.sql`

---

## Záloha databáze

Před většími změnami si vždy udělejte zálohu!

### Přes phpMyAdmin:
1. Vyberte databázi `foodapp`
2. Klikněte na "Export"
3. Metoda: Rychlá
4. Formát: SQL
5. Klikněte "Provést"

### Přes příkazovou řádku:
```bash
# Export databáze
mysqldump -u root -p foodapp > backup_foodapp_2024-11-19.sql

# Import ze zálohy
mysql -u root -p foodapp < backup_foodapp_2024-11-19.sql
```

---

## Časté problémy

### Chyba: "Access denied for user 'root'@'localhost'"
- Zkontrolujte heslo MySQL (v XAMPP je defaultně prázdné)
- Zkuste: `mysql -u root foodapp < database/install.sql` (bez -p)

### Chyba: "Unknown database 'foodapp'"
- Nejdřív vytvořte databázi:
  ```bash
  mysql -u root -e "CREATE DATABASE foodapp CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"
  ```

### Chyba: "Table already exists"
- Databáze už existuje, použijte reset (viz výše)
- Nebo smažte jen tabulky v phpMyAdmin a znovu importujte

### Špatné kódování češtiny v Windows CMD/PowerShell
**POZOR:** Pokud vidíte v Windows CMD/PowerShell rozbité české znaky (např. "Jan Nov�k"), **NEBOJTE SE!**
- Je to pouze problém zobrazení v terminálu
- Windows CMD používá zastaralé kódování místo UTF-8
- **V databázi jsou data uložena správně!**

**Jak ověřit:**
1. Otevřete phpMyAdmin (http://localhost/phpmyadmin) - měli byste vidět "Jan Novák", "Marie Svobodová"
2. Otevřete aplikaci v prohlížeči (http://localhost/web-foodapp/) - česká diakritika bude správně

**Pokud přesto vidíte rozbité znaky v aplikaci/phpMyAdmin:**
- Ujistěte se, že databáze je vytvořena s `utf8mb4_general_ci`
- Zkontrolujte v `app/Models/Database.php` že je nastaveno `charset=utf8mb4`
- HTML stránky mají `<meta charset="UTF-8">`

---

## SQL soubory v této složce

- **install.sql** - Kompletní instalace databáze (tabulky + testovací data)
- **schema.md** - Dokumentace databázového schématu
- **README.md** - Tento soubor (návod k instalaci)

Pro budoucí migrace vytvářejte nové soubory:
- `migration_001_description.sql`
- `migration_002_description.sql`

---

## Další informace

Podrobný popis tabulek, sloupců a SQL dotazů najdete v **schema.md**
