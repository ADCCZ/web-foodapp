<?php
// public/index.php - Test připojení
require_once '../app/Models/Database.php';

try {
    $db = Database::getConnection();
    echo "<h1>Připojení k DB úspěšné!</h1>";
    
    // Zkusíme vytáhnout uživatele
    $stmt = $db->query("SELECT * FROM users");
    $users = $stmt->fetchAll();
    
    echo "<pre>";
    print_r($users);
    echo "</pre>";
} catch (Exception $e) {
    echo "Chyba: " . $e->getMessage();
}
?>