<?php
// public/index.php

// 1. Zapnutí výpisu chyb (pro vývoj)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. Start session (aby si web pamatoval přihlášeného uživatele)
session_start();

// 3. Načtení autoloaderu pro namespaces
require_once '../vendor/autoload.php';  // Composer autoloader
require_once '../app/autoload.php';     // Custom PSR-4 autoloader

// Import namespace classes
use App\Controllers\HomeController;
use App\Controllers\LoginController;
use App\Controllers\RegisterController;
use App\Controllers\ProductController;
use App\Controllers\SupplierController;
use App\Controllers\CartController;
use App\Controllers\OrderController;
use App\Controllers\AdminController;

// 4. Jednoduchý Router
// Získáme název stránky z URL (např. index.php?page=login). Pokud není, je to 'home'.
$page = $_GET['page'] ?? 'home';

// Přepínač stránek
switch ($page) {
    case 'home':
        $controller = new HomeController();
        $controller->index();
        break;

    case 'login':
        $controller = new LoginController();
        // Pokud byl odeslán formulář, zpracuj ho, jinak ukaž stránku
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->processLogin();
        } else {
            $controller->index();
        }
        break;

    case 'register':
        $controller = new RegisterController();
        // Pokud se odesílá formulář (AJAX POST)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->processRegister();
        } else {
            $controller->index();
        }
        break;

    case 'logout':
         $controller = new LoginController();
         $controller->logout();
         break;

    case 'products':
        $controller = new ProductController();

        // Handle different actions
        $action = $_GET['action'] ?? 'index';

        if ($action === 'search' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->search();
        } else {
            $controller->index();
        }
        break;

    case 'supplier':
        $controller = new SupplierController();

        // Handle different actions
        $action = $_GET['action'] ?? 'index';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($action === 'create') {
                $controller->createProduct();
            } elseif ($action === 'update') {
                $controller->updateProduct();
            } elseif ($action === 'delete') {
                $controller->deleteProduct();
            }
        } else {
            $controller->index();
        }
        break;

    case 'cart':
        $controller = new CartController();

        $action = $_GET['action'] ?? 'index';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($action === 'add') {
                $controller->addToCart();
            } elseif ($action === 'update') {
                $controller->updateQuantity();
            } elseif ($action === 'remove') {
                $controller->removeFromCart();
            } elseif ($action === 'clear') {
                $controller->clearCart();
            }
        } else {
            $controller->index();
        }
        break;

    case 'checkout':
        $controller = new OrderController();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->createOrder();
        } else {
            $controller->checkout();
        }
        break;

    case 'orders':
        $controller = new OrderController();

        $action = $_GET['action'] ?? 'index';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'update-status') {
            $controller->updateStatus();
        } elseif ($action === 'view') {
            $controller->viewOrder();
        } else {
            $controller->myOrders();
        }
        break;

    case 'admin':
        $controller = new AdminController();

        $action = $_GET['action'] ?? 'index';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($action === 'approve') {
                $controller->approveSupplier();
            } elseif ($action === 'reject') {
                $controller->rejectSupplier();
            } elseif ($action === 'update-role') {
                $controller->updateRole();
            } elseif ($action === 'delete-user') {
                $controller->deleteUser();
            }
        } else {
            if ($action === 'users') {
                $controller->users();
            } elseif ($action === 'orders') {
                $controller->allOrders();
            } elseif ($action === 'products') {
                $controller->allProducts();
            } else {
                $controller->index();
            }
        }
        break;

    default:
        echo "<h1>404 - Stránka nenalezena</h1>";
        break;
}
?>