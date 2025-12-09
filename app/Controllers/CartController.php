<?php

namespace App\Controllers;

use App\Models\Database;
use App\Models\Product;
use App\Helpers\TwigHelper;

class CartController {
    private $productModel;
    private $twig;

    public function __construct() {
        $this->productModel = new Product();
        $this->twig = TwigHelper::getTwig();
    }

    /**
     * Display shopping cart page
     */
    public function index() {
        // Initialize cart if not exists
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // Get cart items with product details
        $cartItems = $this->getCartItems();
        $total = $this->calculateTotal($cartItems);

        echo $this->twig->render('cart.twig', [
            'cartItems' => $cartItems,
            'total' => $total,
            'session' => $_SESSION
        ]);
    }

    /**
     * Add product to cart via AJAX
     */
    public function addToCart() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Musíte být přihlášeni']);
            exit;
        }

        // Check if user is customer or admin (suppliers cannot buy)
        if (!in_array($_SESSION['role'], ['konzument', 'admin'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Pouze zákazníci a administrátoři mohou přidávat produkty do košíku']);
            exit;
        }

        if (empty($_POST['product_id'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'ID produktu není zadáno']);
            exit;
        }

        $productId = (int)$_POST['product_id'];
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

        // Verify product exists
        $product = $this->productModel->getProductById($productId);
        if (!$product) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Produkt nenalezen']);
            exit;
        }

        // Initialize cart if not exists
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // Check if cart has products from different supplier
        if (!empty($_SESSION['cart'])) {
            // Get first product in cart to check supplier
            $firstProductId = array_key_first($_SESSION['cart']);
            $firstProduct = $this->productModel->getProductById($firstProductId);

            if ($firstProduct && $firstProduct['supplier_id'] != $product['supplier_id']) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Nemůžete objednávat z více restaurací najednou. Vyprázdněte košík a zkuste to znovu.',
                    'different_supplier' => true,
                    'current_supplier' => $firstProduct['supplier_name']
                ]);
                exit;
            }
        }

        // Add or update quantity
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId] += $quantity;
        } else {
            $_SESSION['cart'][$productId] = $quantity;
        }

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Produkt přidán do košíku',
            'cartCount' => $this->getCartCount()
        ]);
        exit;
    }

    /**
     * Update product quantity in cart via AJAX
     */
    public function updateQuantity() {
        if (empty($_POST['product_id']) || !isset($_POST['quantity'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Neplatná data']);
            exit;
        }

        $productId = (int)$_POST['product_id'];
        $quantity = (int)$_POST['quantity'];

        if ($quantity < 1) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Množství musí být alespoň 1']);
            exit;
        }

        if (!isset($_SESSION['cart'][$productId])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Produkt není v košíku']);
            exit;
        }

        $_SESSION['cart'][$productId] = $quantity;

        // Recalculate total
        $cartItems = $this->getCartItems();
        $total = $this->calculateTotal($cartItems);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Množství aktualizováno',
            'total' => $total
        ]);
        exit;
    }

    /**
     * Remove product from cart via AJAX
     */
    public function removeFromCart() {
        if (empty($_POST['product_id'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'ID produktu není zadáno']);
            exit;
        }

        $productId = (int)$_POST['product_id'];

        if (isset($_SESSION['cart'][$productId])) {
            unset($_SESSION['cart'][$productId]);
        }

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Produkt odebrán z košíku',
            'cartCount' => $this->getCartCount()
        ]);
        exit;
    }

    /**
     * Clear entire cart
     */
    public function clearCart() {
        $_SESSION['cart'] = [];

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Košík byl vyprázdněn'
        ]);
        exit;
    }

    /**
     * Get cart items with full product details
     * @return array Cart items with product info
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
     * Calculate total price of cart
     * @param array $cartItems
     * @return float Total price
     */
    private function calculateTotal($cartItems) {
        $total = 0;
        foreach ($cartItems as $item) {
            $total += $item['subtotal'];
        }
        return $total;
    }

    /**
     * Get total number of items in cart
     * @return int Item count
     */
    private function getCartCount() {
        if (!isset($_SESSION['cart'])) {
            return 0;
        }
        return array_sum($_SESSION['cart']);
    }
}
