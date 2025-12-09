<?php

namespace App\Controllers;

use App\Models\Database;
use App\Models\Product;
use App\Helpers\TwigHelper;

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

        // Get all suppliers for filter
        $suppliers = $this->productModel->getAllSuppliers();

        // Render products page
        echo $this->twig->render('products.twig', [
            'products' => $products,
            'suppliers' => $suppliers,
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

    /**
     * AJAX search and filter products
     * Returns JSON response with filtered products
     */
    public function search() {
        header('Content-Type: application/json');

        // Get search parameters from POST
        $searchQuery = isset($_POST['search']) ? trim($_POST['search']) : null;
        $supplierId = isset($_POST['supplier_id']) ? (int)$_POST['supplier_id'] : null;

        // Search products using model
        $products = $this->productModel->searchProducts($searchQuery, $supplierId);

        // Generate HTML for products
        $html = '';
        if (count($products) > 0) {
            foreach ($products as $product) {
                $html .= $this->renderProductCard($product);
            }
        } else {
            $html = $this->renderNoProducts();
        }

        echo json_encode([
            'success' => true,
            'html' => $html,
            'count' => count($products)
        ]);
        exit;
    }

    /**
     * Render single product card HTML
     * @param array $product Product data
     * @return string HTML string
     */
    private function renderProductCard($product) {
        $imageHtml = '';
        if ($product['image']) {
            $imageHtml = '<img src="uploads/' . htmlspecialchars($product['image']) . '" alt="' . htmlspecialchars($product['name']) . '" class="w-100 h-100" style="object-fit: cover;">';
        } else {
            $imageHtml = '<div class="w-100 h-100 d-flex align-items-center justify-content-center"><i class="bi bi-image" style="font-size: 4rem; color: #cbd5e1;"></i></div>';
        }

        $description = strlen($product['description']) > 80
            ? substr($product['description'], 0, 80) . '...'
            : $product['description'];

        $actionButton = '';
        if (isset($_SESSION['user_id']) && in_array($_SESSION['role'], ['konzument', 'admin'])) {
            $actionButton = '<button class="btn btn-modern btn-modern-primary btn-sm" onclick="addToCart(' . $product['product_id'] . ')">
                                <i class="bi bi-cart-plus-fill"></i> Přidat do košíku
                            </button>';
        } elseif (!isset($_SESSION['user_id'])) {
            $actionButton = '<a href="?page=login" class="btn btn-modern btn-modern-secondary btn-sm">
                                <i class="bi bi-box-arrow-in-right"></i> Přihlaste se pro objednání
                            </a>';
        }

        return '<div class="col-sm-6 col-md-6 col-lg-4 mb-3">
                    <div class="card-modern h-100">
                        <div class="position-relative" style="height: 150px; overflow: hidden; background: #f1f5f9; border-radius: 12px 12px 0 0;">
                            ' . $imageHtml . '
                            <div class="position-absolute top-0 end-0 m-3">
                                <span class="badge" style="background: #f97316; font-size: 1rem; padding: 0.5rem 1rem;">
                                    ' . htmlspecialchars($product['price']) . ' Kč
                                </span>
                            </div>
                        </div>
                        <div class="card-body d-flex flex-column p-3">
                            <h6 class="fw-bold mb-2" style="color: #0f172a; font-size: 1rem;">' . htmlspecialchars($product['name']) . '</h6>
                            <p class="text-muted mb-2" style="font-size: 0.85rem;">
                                <i class="bi bi-shop" style="color: #f97316;"></i> ' . htmlspecialchars($product['supplier_name']) . '
                            </p>
                            <p class="mb-3" style="color: #64748b; font-size: 0.9rem;">
                                ' . htmlspecialchars($description) . '
                            </p>
                            <div class="d-grid mt-auto">
                                ' . $actionButton . '
                            </div>
                        </div>
                    </div>
                </div>';
    }

    /**
     * Render "no products" message HTML
     * @return string HTML string
     */
    private function renderNoProducts() {
        return '<div class="col-12">
                    <div class="card card-modern text-center p-5">
                        <i class="bi bi-inbox" style="font-size: 4rem; color: #cbd5e1;"></i>
                        <h4 class="mt-3" style="color: #64748b;">Nebyly nalezeny žádné produkty</h4>
                        <p style="color: #94a3b8;">Zkuste změnit vyhledávací kritéria.</p>
                    </div>
                </div>';
    }
}
