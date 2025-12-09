<?php

namespace App\Controllers;

use App\Models\Database;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Helpers\TwigHelper;

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
        if (!isset($_SESSION['user_id'])) {
            header('Location: ?page=login');
            exit;
        }
        if ($_SESSION['role'] !== 'admin') {
            header('Location: ?page=home');
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
        if (!isset($_SESSION['user_id'])) {
            header('Location: ?page=login');
            exit;
        }
        if ($_SESSION['role'] !== 'admin') {
            header('Location: ?page=home');
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
     * SuperAdmin protection: Only SuperAdmin can create/modify admins
     */
    public function updateRole() {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Nemáte oprávnění']);
            exit;
        }

        $userId = (int)$_POST['user_id'];
        $role = $_POST['role'];

        // Check if target user is SuperAdmin
        $targetUser = $this->userModel->getUserById($userId);
        if ($targetUser && $targetUser['is_super_admin'] == 1) {
            echo json_encode(['success' => false, 'message' => 'Nelze upravit SuperAdmina']);
            exit;
        }

        // Check if trying to create admin and current user is not SuperAdmin
        if ($role === 'admin' && !$this->userModel->isSuperAdmin($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Pouze SuperAdmin může vytvářet další administrátory']);
            exit;
        }

        // Prevent user from changing own role
        if ($userId === $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'Nemůžete změnit svou vlastní roli']);
            exit;
        }

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
     * SuperAdmin protection: SuperAdmin cannot be deleted by anyone
     */
    public function deleteUser() {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Nemáte oprávnění']);
            exit;
        }

        $userId = (int)$_POST['user_id'];

        // Prevent deleting yourself
        if ($userId === $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'Nemůžete smazat sami sebe']);
            exit;
        }

        // Check if target user is SuperAdmin
        $targetUser = $this->userModel->getUserById($userId);
        if ($targetUser && $targetUser['is_super_admin'] == 1) {
            echo json_encode(['success' => false, 'message' => 'SuperAdmin nemůže být smazán']);
            exit;
        }

        // Check if target is admin and current user is not SuperAdmin
        if ($targetUser && $targetUser['role'] === 'admin' && !$this->userModel->isSuperAdmin($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Pouze SuperAdmin může mazat další administrátory']);
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
        if (!isset($_SESSION['user_id'])) {
            header('Location: ?page=login');
            exit;
        }
        if ($_SESSION['role'] !== 'admin') {
            header('Location: ?page=home');
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
        if (!isset($_SESSION['user_id'])) {
            header('Location: ?page=login');
            exit;
        }
        if ($_SESSION['role'] !== 'admin') {
            header('Location: ?page=home');
            exit;
        }

        $products = $this->productModel->getAllProducts();

        echo $this->twig->render('admin_products.twig', [
            'session' => $_SESSION,
            'products' => $products
        ]);
    }
}
