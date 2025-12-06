<?php
// public/index.php

// 1. Zapnutí výpisu chyb (pro vývoj)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. Start session (aby si web pamatoval přihlášeného uživatele)
session_start();

// 3. Načtení databáze (aby byla dostupná všude)
require_once '../app/Models/Database.php';

// 4. Jednoduchý Router
// Získáme název stránky z URL (např. index.php?page=login). Pokud není, je to 'home'.
$page = $_GET['page'] ?? 'home';

// Přepínač stránek
switch ($page) {
    case 'home':
        require_once '../app/Controllers/HomeController.php';
        $controller = new HomeController();
        $controller->index();
        break;

    case 'login':
        require_once '../app/Controllers/LoginController.php';
        $controller = new LoginController();
        // Pokud byl odeslán formulář, zpracuj ho, jinak ukaž stránku
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->processLogin();
        } else {
            $controller->index();
        }
        break;

    case 'register':
        require_once '../app/Controllers/RegisterController.php';
        $controller = new RegisterController();
        // Pokud se odesílá formulář (AJAX POST)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->processRegister();
        } else {
            $controller->index();
        }
        break;

    case 'logout':
         require_once '../app/Controllers/LoginController.php';
         $controller = new LoginController();
         $controller->logout();
         break;

    default:
        echo "<h1>404 - Stránka nenalezena</h1>";
        break;
}
?>