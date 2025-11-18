<?php
// Soubor: app/Models/Database.php

class Database {
    private static $connection = null;

    // Uprav si podle svého nastavení (localhost, root, heslo...)
    private static $host = 'localhost'; // nebo '127.0.0.1'
    private static $db   = 'foodapp';
    private static $user = 'root';
    private static $pass = ''; 
    private static $charset = 'utf8mb4';

    // Metoda pro získání připojení
    public static function getConnection() {
        if (self::$connection === null) {
            $dsn = "mysql:host=" . self::$host . ";dbname=" . self::$db . ";charset=" . self::$charset;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Vyhazuje chyby, abychom je viděli
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Vrací pole ['jmeno' => 'Jan']
                PDO::ATTR_EMULATE_PREPARES   => false,                  // Bezpečnost
            ];

            try {
                self::$connection = new PDO($dsn, self::$user, self::$pass, $options);
            } catch (\PDOException $e) {
                // V reálu chybu logujeme, na localhostu ji vypíšeme
                throw new \PDOException($e->getMessage(), (int)$e->getCode());
            }
        }
        return self::$connection;
    }
}
?>