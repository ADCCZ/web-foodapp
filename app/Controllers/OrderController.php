<?php

namespace App\Controllers;

use App\Models\Database;
use App\Models\Order;
use App\Models\Product;
use App\Helpers\TwigHelper;

class OrderController {
    private $orderModel;
    private $productModel;
    private $twig;

    public function __construct() {
        $this->orderModel = new Order();
        $this->productModel = new Product();
        $this->twig = TwigHelper::getTwig();
    }

    /**
     * Display checkout page
     */
    public function checkout() {
        // Check if user is logged in as customer or admin
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['konzument', 'admin'])) {
            header('Location: ?page=login');
            exit;
        }

        // Check if cart is not empty
        if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
            header('Location: ?page=cart');
            exit;
        }

        // Get cart items
        $cartItems = $this->getCartItems();
        $total = $this->calculateTotal($cartItems);

        echo $this->twig->render('checkout.twig', [
            'cartItems' => $cartItems,
            'total' => $total,
            'session' => $_SESSION
        ]);
    }

    /**
     * Process order creation
     */
    public function createOrder() {
        // Check authentication (customer or admin can order)
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['konzument', 'admin'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Nejste přihlášeni jako zákazník nebo administrátor']);
            exit;
        }

        // Check cart
        if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Košík je prázdný']);
            exit;
        }

        // Get delivery information from POST
        $customerName = $_POST['customer_name'] ?? '';
        $email = $_POST['email'] ?? '';
        $deliveryAddress = $_POST['delivery_address'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $note = $_POST['note'] ?? '';

        // Validate required fields
        if (empty($customerName) || empty($email) || empty($deliveryAddress) || empty($phone)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Vyplňte všechny povinné údaje']);
            exit;
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Neplatný formát emailu']);
            exit;
        }

        // Validate note length (max 500 characters)
        if (strlen($note) > 500) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Poznámka může mít maximálně 500 znaků']);
            exit;
        }

        // Prepare order items
        $cartItems = $this->getCartItems();
        $total = $this->calculateTotal($cartItems);

        $orderItems = [];
        foreach ($cartItems as $item) {
            $orderItems[] = [
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price']
            ];
        }

        // Create order with delivery information
        $orderId = $this->orderModel->createOrder(
            $_SESSION['user_id'],
            $orderItems,
            $total,
            $customerName,
            $email,
            $deliveryAddress,
            $phone,
            $note
        );

        if ($orderId) {
            // Clear cart
            $_SESSION['cart'] = [];

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Objednávka byla úspěšně vytvořena',
                'order_id' => $orderId
            ]);
        } else {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Chyba při vytváření objednávky'
            ]);
        }
        exit;
    }

    /**
     * Display order history for customer
     */
    public function myOrders() {
        // Check authentication
        if (!isset($_SESSION['user_id'])) {
            header('Location: ?page=login');
            exit;
        }

        $orders = [];

        if (in_array($_SESSION['role'], ['konzument', 'admin'])) {
            // Customer/Admin orders - show only their own orders
            $orders = $this->orderModel->getOrdersByUserId($_SESSION['user_id']);
        } elseif ($_SESSION['role'] === 'dodavatel') {
            // Supplier orders - show orders containing their products
            $orders = $this->orderModel->getOrdersBySupplierId($_SESSION['user_id']);
        }

        echo $this->twig->render('orders.twig', [
            'orders' => $orders,
            'session' => $_SESSION
        ]);
    }

    /**
     * Display single order detail
     */
    public function viewOrder() {
        // Check authentication
        if (!isset($_SESSION['user_id'])) {
            header('Location: ?page=login');
            exit;
        }

        if (empty($_GET['id'])) {
            header('Location: ?page=orders');
            exit;
        }

        $orderId = (int)$_GET['id'];
        $order = $this->orderModel->getOrderById($orderId);

        if (!$order) {
            header('Location: ?page=orders');
            exit;
        }

        // Verify user can view this order
        // Customers can only view their own orders, Admin can view all orders
        if ($_SESSION['role'] === 'konzument' && $order['customer_id'] != $_SESSION['user_id']) {
            header('Location: ?page=orders');
            exit;
        }

        echo $this->twig->render('order_detail.twig', [
            'order' => $order,
            'session' => $_SESSION
        ]);
    }

    /**
     * Update order status (for suppliers)
     */
    public function updateStatus() {
        // Check authentication
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'dodavatel') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Nemáte oprávnění']);
            exit;
        }

        if (empty($_POST['order_id']) || empty($_POST['status'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Neplatná data']);
            exit;
        }

        $orderId = (int)$_POST['order_id'];
        $status = $_POST['status'];

        // Validate status
        $validStatuses = ['pending', 'processing', 'completed', 'cancelled'];
        if (!in_array($status, $validStatuses)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Neplatný stav']);
            exit;
        }

        $success = $this->orderModel->updateOrderStatus($orderId, $status);

        header('Content-Type: application/json');
        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Stav objednávky byl změněn']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Chyba při změně stavu']);
        }
        exit;
    }

    /**
     * Get cart items with product details
     */
    private function getCartItems() {
        if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
            return [];
        }

        $cartItems = [];
        foreach ($_SESSION['cart'] as $productId => $quantity) {
            $product = $this->productModel->getProductById($productId);
            if ($product) {
                $product['quantity'] = $quantity;
                $product['subtotal'] = $product['price'] * $quantity;
                $cartItems[] = $product;
            }
        }

        return $cartItems;
    }

    /**
     * Calculate total price
     */
    private function calculateTotal($cartItems) {
        $total = 0;
        foreach ($cartItems as $item) {
            $total += $item['subtotal'];
        }
        return $total;
    }
}
