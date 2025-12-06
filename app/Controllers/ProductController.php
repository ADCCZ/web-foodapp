<?php

require_once '../app/Models/Database.php';
require_once '../app/Models/Product.php';
require_once '../app/Helpers/TwigHelper.php';

/**
 * ProductController
 * Handles product display and management
 */
class ProductController {
    private $productModel;
    private $twig;

    public function __construct() {
        $this->productModel = new Product();
        $this->twig = TwigHelper::getTwig();
    }

    /**
     * Display all products
     * Main product listing page
     */
    public function index() {
        // Get all products from database
        $products = $this->productModel->getAllProducts();

        // Render products page
        echo $this->twig->render('products.twig', [
            'products' => $products,
            'session' => $_SESSION
        ]);
    }

    /**
     * Display single product detail
     * @param int $productId Product ID from GET parameter
     */
    public function detail() {
        if (!isset($_GET['id'])) {
            header('Location: ?page=products');
            exit;
        }

        $productId = (int)$_GET['id'];
        $product = $this->productModel->getProductById($productId);

        if (!$product) {
            header('Location: ?page=products');
            exit;
        }

        echo $this->twig->render('product_detail.twig', [
            'product' => $product,
            'session' => $_SESSION
        ]);
    }
}
