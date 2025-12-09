<?php
// app/Models/Database.php

namespace App\Models;

use PDO;
use PDOException;

/**
 * Database Connection Class
 * Singleton pattern for PDO connection
 * Uses prepared statements for SQL injection protection
 */
class Database {
    private static $connection = null;

    // Database configuration (XAMPP default settings)
    private static $host = 'localhost';
    private static $db   = 'foodapp';
    private static $user = 'root';
    private static $pass = '';
    private static $charset = 'utf8mb4';

    /**
     * Get database connection (singleton)
     * Creates new PDO connection if not exists
     * @return PDO Database connection object
     * @throws PDOException if connection fails
     */
    public static function getConnection() {
        if (self::$connection === null) {
            $dsn = "mysql:host=" . self::$host . ";dbname=" . self::$db . ";charset=" . self::$charset;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // Throw exceptions on errors
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // Return associative arrays
                PDO::ATTR_EMULATE_PREPARES   => false,                   // Use real prepared statements
            ];

            try {
                self::$connection = new PDO($dsn, self::$user, self::$pass, $options);
            } catch (PDOException $e) {
                throw new PDOException($e->getMessage(), (int)$e->getCode());
            }
        }
        return self::$connection;
    }
}
?>