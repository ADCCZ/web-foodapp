# Databázové schéma - Délicious

## Přehled tabulek

```
users (uživatelé)
  |
  ├──> products (produkty dodavatelů)
  |      |
  |      └──> order_items (položky objednávek)
  |              ^
  |              |
  └──> orders (objednávky) ──┘
```

---

## Tabulka: users

Ukládá všechny uživatele systému (konzumenti, dodavatelé, administrátoři).

| Sloupec       | Typ          | Popis                                    |
|---------------|--------------|------------------------------------------|
| user_id       | INT (PK)     | Primární klíč, auto-increment            |
| email         | VARCHAR(255) | Email uživatele (UNIQUE)                 |
| password      | VARCHAR(255) | Bcrypt hash hesla                        |
| jmeno         | VARCHAR(100) | Jméno/název uživatele                    |
| role          | ENUM         | 'konzument', 'dodavatel', 'admin'        |
| is_approved   | TINYINT(1)   | 0 = čeká na schválení, 1 = schválený     |
| created_at    | TIMESTAMP    | Datum registrace                         |

**Indexy:**
- PRIMARY KEY: `user_id`
- UNIQUE: `email`
- INDEX: `role`, `is_approved`

**Role:**
- **konzument** - běžný zákazník, může objednávat
- **dodavatel** - může přidávat produkty, vyřizovat objednávky
- **admin** - plný přístup, schvaluje dodavatele

---

## Tabulka: products

Produkty přidané dodavateli.

| Sloupec       | Typ           | Popis                                   |
|---------------|---------------|-----------------------------------------|
| product_id    | INT (PK)      | Primární klíč, auto-increment           |
| supplier_id   | INT (FK)      | ID dodavatele → users(user_id)          |
| name          | VARCHAR(255)  | Název produktu                          |
| description   | TEXT          | Popis produktu                          |
| price         | DECIMAL(10,2) | Cena v Kč                               |
| image         | VARCHAR(255)  | Cesta k obrázku (uploads/xxx.jpg)       |
| created_at    | TIMESTAMP     | Datum přidání                           |

**Indexy:**
- PRIMARY KEY: `product_id`
- INDEX: `supplier_id`

**Foreign Keys:**
- `supplier_id` → `users(user_id)` ON DELETE CASCADE

---

## Tabulka: orders

Objednávky zákazníků.

| Sloupec       | Typ           | Popis                                   |
|---------------|---------------|-----------------------------------------|
| order_id      | INT (PK)      | Primární klíč, auto-increment           |
| customer_id   | INT (FK)      | ID zákazníka → users(user_id)           |
| status        | ENUM          | 'pending', 'processing', 'completed', 'cancelled' |
| total_price   | DECIMAL(10,2) | Celková cena objednávky                 |
| created_at    | TIMESTAMP     | Datum objednání                         |

**Indexy:**
- PRIMARY KEY: `order_id`
- INDEX: `customer_id`, `status`

**Foreign Keys:**
- `customer_id` → `users(user_id)` ON DELETE CASCADE

**Stavy objednávky:**
- **pending** - nová, čeká na zpracování
- **processing** - dodavatel ji zpracovává
- **completed** - dokončená, vyřízená
- **cancelled** - zrušená

---

## Tabulka: order_items

Položky v objednávkách (M:N vztah mezi orders a products).

| Sloupec        | Typ           | Popis                                   |
|----------------|---------------|-----------------------------------------|
| order_item_id  | INT (PK)      | Primární klíč, auto-increment           |
| order_id       | INT (FK)      | ID objednávky → orders(order_id)        |
| product_id     | INT (FK)      | ID produktu → products(product_id)      |
| quantity       | INT           | Množství kusů                           |
| price          | DECIMAL(10,2) | Cena produktu v době objednání          |

**Indexy:**
- PRIMARY KEY: `order_item_id`
- INDEX: `order_id`, `product_id`

**Foreign Keys:**
- `order_id` → `orders(order_id)` ON DELETE CASCADE
- `product_id` → `products(product_id)` ON DELETE CASCADE

**Poznámka:** Cena se ukládá v době objednání, aby historické objednávky měly správnou cenu i když se změní cena produktu.

---

## SQL dotazy - příklady

### Zobrazit všechny produkty s dodavatelem
```sql
SELECT p.*, u.jmeno AS dodavatel_jmeno
FROM products p
JOIN users u ON p.supplier_id = u.user_id
WHERE u.is_approved = 1
ORDER BY p.created_at DESC;
```

### Objednávky konkrétního zákazníka
```sql
SELECT o.*,
       COUNT(oi.order_item_id) AS pocet_polozek
FROM orders o
LEFT JOIN order_items oi ON o.order_id = oi.order_id
WHERE o.customer_id = 5
GROUP BY o.order_id
ORDER BY o.created_at DESC;
```

### Detail objednávky včetně produktů
```sql
SELECT oi.*, p.name, p.image
FROM order_items oi
JOIN products p ON oi.product_id = p.product_id
WHERE oi.order_id = 1;
```

### Nejprodávanější produkty
```sql
SELECT p.name,
       SUM(oi.quantity) AS celkem_prodano,
       COUNT(DISTINCT oi.order_id) AS pocet_objednavek
FROM products p
JOIN order_items oi ON p.product_id = oi.product_id
GROUP BY p.product_id
ORDER BY celkem_prodano DESC
LIMIT 10;
```

### Nevyřízené objednávky pro dodavatele
```sql
SELECT DISTINCT o.*
FROM orders o
JOIN order_items oi ON o.order_id = oi.order_id
JOIN products p ON oi.product_id = p.product_id
WHERE p.supplier_id = 2
  AND o.status IN ('pending', 'processing')
ORDER BY o.created_at ASC;
```

---

## Testovací data

Po importu `install.sql` máte k dispozici:

### Uživatelé (heslo pro všechny: heslo123)
- **admin@test.cz** - administrátor
- **dodavatel@test.cz** - Pizza House (schválený)
- **dodavatel2@test.cz** - Burger King (schválený)
- **dodavatel3@test.cz** - Sushi Bar (NESCHVÁLENÝ - pro testování schvalování)
- **zakaznik@test.cz** - Jan Novák
- **zakaznik2@test.cz** - Marie Svobodová

### Produkty
- 10 produktů (pizzy, burgery, sushi)

### Objednávky
- 4 objednávky v různých stavech (completed, processing, pending)
- S více položkami pro testování

---

## Migrace a změny

Pro budoucí změny schématu vytvářejte nové soubory:
- `migration_001_add_column.sql`
- `migration_002_new_table.sql`

Vždy začněte záložní kopií databáze!
