<?php

require_once __DIR__ . '/../Models/Database.php';
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Models/Order.php';
require_once __DIR__ . '/../Models/Product.php';
require_once __DIR__ . '/../Helpers/TwigHelper.php';

/**
 * AdminController
 * Handles admin operations: user management, supplier approval
 */
class AdminController {
    private $userModel;
    private $orderModel;
    private $productModel;
    private $twig;

    public function __construct() {
        $this->userModel = new User();
        $this->orderModel = new Order();
        $this->productModel = new Product();
        $this->twig = TwigHelper::getTwig();
    }

    /**
     * Admin dashboard
     */
    public function index() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header('Location: ?page=login');
            exit;
        }

        $stats = $this->userModel->getUserStats();
        $pendingSuppliers = $this->userModel->getPendingSuppliers();

        echo $this->twig->render('admin_dashboard.twig', [
            'session' => $_SESSION,
            'stats' => $stats,
            'pendingSuppliers' => $pendingSuppliers
        ]);
    }

    /**
     * User management page
     */
    public function users() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header('Location: ?page=login');
            exit;
        }

        $users = $this->userModel->getAllUsers();

        echo $this->twig->render('admin_users.twig', [
            'session' => $_SESSION,
            'users' => $users
        ]);
    }

    /**
     * Approve supplier (AJAX)
     */
    public function approveSupplier() {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Nemáte oprávnění']);
            exit;
        }

        $userId = (int)$_POST['user_id'];
        $success = $this->userModel->approveSupplier($userId);

        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Dodavatel byl schválen']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Chyba při schvalování']);
        }
        exit;
    }

    /**
     * Reject supplier (AJAX)
     */
    public function rejectSupplier() {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Nemáte oprávnění']);
            exit;
        }

        $userId = (int)$_POST['user_id'];
        $success = $this->userModel->rejectSupplier($userId);

        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Dodavatel byl zamítnut']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Chyba při zamítání']);
        }
        exit;
    }

    /**
     * Update user role (AJAX)
     */
    public function updateRole() {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Nemáte oprávnění']);
            exit;
        }

        $userId = (int)$_POST['user_id'];
        $role = $_POST['role'];

        $success = $this->userModel->updateUserRole($userId, $role);

        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Role byla změněna']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Chyba při změně role']);
        }
        exit;
    }

    /**
     * Delete user (AJAX)
     */
    public function deleteUser() {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Nemáte oprávnění']);
            exit;
        }

        $userId = (int)$_POST['user_id'];

        if ($userId === $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'Nemůžete smazat sami sebe']);
            exit;
        }

        $success = $this->userModel->deleteUser($userId);

        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Uživatel byl smazán']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Chyba při mazání uživatele']);
        }
        exit;
    }

    /**
     * All orders overview
     */
    public function allOrders() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header('Location: ?page=login');
            exit;
        }

        $orders = $this->orderModel->getAllOrders();

        echo $this->twig->render('admin_orders.twig', [
            'session' => $_SESSION,
            'orders' => $orders
        ]);
    }

    /**
     * All products overview
     */
    public function allProducts() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header('Location: ?page=login');
            exit;
        }

        $products = $this->productModel->getAllProducts();

        echo $this->twig->render('admin_products.twig', [
            'session' => $_SESSION,
            'products' => $products
        ]);
    }
}
