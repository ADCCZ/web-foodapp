<?php

require_once '../app/Models/Database.php';
require_once '../app/Models/Product.php';
require_once '../app/Helpers/TwigHelper.php';

/**
 * SupplierController
 * Handles supplier product management (add, edit, delete products)
 */
class SupplierController {
    private $productModel;
    private $twig;

    public function __construct() {
        $this->productModel = new Product();
        $this->twig = TwigHelper::getTwig();
    }

    /**
     * Display supplier dashboard with their products
     * Requires user to be logged in as supplier
     */
    public function index() {
        // Check if user is logged in and is a supplier
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'dodavatel') {
            header('Location: ?page=login');
            exit;
        }

        // Check if supplier is approved
        if (!isset($_SESSION['is_approved']) || $_SESSION['is_approved'] != 1) {
            echo $this->twig->render('supplier_waiting.twig', [
                'session' => $_SESSION
            ]);
            return;
        }

        // Get supplier's products
        $products = $this->productModel->getProductsBySupplierId($_SESSION['user_id']);

        echo $this->twig->render('supplier.twig', [
            'products' => $products,
            'session' => $_SESSION
        ]);
    }

    /**
     * Process new product creation
     * Handles image upload with unique filename
     */
    public function createProduct() {
        // Check authentication
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'dodavatel') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Nejste přihlášen jako dodavatel']);
            exit;
        }

        // Check if supplier is approved
        if (!isset($_SESSION['is_approved']) || $_SESSION['is_approved'] != 1) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Váš účet nebyl schválen administrátorem']);
            exit;
        }

        // Validate required fields
        if (empty($_POST['name']) || empty($_POST['description']) || empty($_POST['price'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Vyplňte všechna povinná pole']);
            exit;
        }

        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $price = floatval($_POST['price']);
        $imageName = null;

        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $this->handleImageUpload($_FILES['image']);

            if ($uploadResult['success']) {
                $imageName = $uploadResult['filename'];
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $uploadResult['message']]);
                exit;
            }
        }

        // Create product in database
        $success = $this->productModel->createProduct(
            $_SESSION['user_id'],
            $name,
            $description,
            $price,
            $imageName
        );

        header('Content-Type: application/json');
        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Produkt byl úspěšně přidán']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Chyba při ukládání produktu']);
        }
        exit;
    }

    /**
     * Handle image upload with validation and unique filename generation
     * @param array $file Uploaded file from $_FILES
     * @return array Result with success status, filename or error message
     */
    private function handleImageUpload($file) {
        // Validate file size (max 5MB)
        $maxSize = 5 * 1024 * 1024; // 5MB in bytes
        if ($file['size'] > $maxSize) {
            return ['success' => false, 'message' => 'Soubor je příliš velký (max 5MB)'];
        }

        // Validate file type (only images)
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = mime_content_type($file['tmp_name']);

        if (!in_array($fileType, $allowedTypes)) {
            return ['success' => false, 'message' => 'Neplatný typ souboru. Povolené: JPG, PNG, GIF, WEBP'];
        }

        // Get file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        // Generate unique filename (uniqid + timestamp + random)
        // This ensures unique filenames (+2 points requirement)
        $uniqueFilename = uniqid('product_', true) . '_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;

        // Upload directory
        $uploadDir = __DIR__ . '/../../public/uploads/';
        $uploadPath = $uploadDir . $uniqueFilename;

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            return ['success' => true, 'filename' => $uniqueFilename];
        } else {
            return ['success' => false, 'message' => 'Chyba při nahrávání souboru'];
        }
    }

    /**
     * Update existing product
     * Allows supplier to edit their product details and optionally upload new image
     */
    public function updateProduct() {
        // Check authentication
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'dodavatel') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Nejste přihlášen jako dodavatel']);
            exit;
        }

        // Validate required fields
        if (empty($_POST['product_id']) || empty($_POST['name']) || empty($_POST['description']) || empty($_POST['price'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Vyplňte všechna povinná pole']);
            exit;
        }

        $productId = (int)$_POST['product_id'];
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $price = floatval($_POST['price']);

        // Verify product belongs to this supplier
        $product = $this->productModel->getProductById($productId);

        if (!$product || $product['supplier_id'] != $_SESSION['user_id']) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Nemáte oprávnění upravit tento produkt']);
            exit;
        }

        $imageName = $product['image']; // Keep existing image by default

        // Handle new image upload if provided
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $this->handleImageUpload($_FILES['image']);

            if ($uploadResult['success']) {
                // Delete old image
                if ($product['image']) {
                    $oldImagePath = __DIR__ . '/../../public/uploads/' . $product['image'];
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
                $imageName = $uploadResult['filename'];
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $uploadResult['message']]);
                exit;
            }
        }

        // Update product in database
        $success = $this->productModel->updateProduct(
            $productId,
            $name,
            $description,
            $price,
            $imageName
        );

        header('Content-Type: application/json');
        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Produkt byl úspěšně upraven']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Chyba při ukládání změn']);
        }
        exit;
    }

    /**
     * Delete product (only supplier's own products)
     */
    public function deleteProduct() {
        // Check authentication
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'dodavatel') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Nejste přihlášen jako dodavatel']);
            exit;
        }

        if (empty($_POST['product_id'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'ID produktu není zadáno']);
            exit;
        }

        $productId = (int)$_POST['product_id'];

        // Verify product belongs to this supplier
        $product = $this->productModel->getProductById($productId);

        if (!$product || $product['supplier_id'] != $_SESSION['user_id']) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Nemáte oprávnění smazat tento produkt']);
            exit;
        }

        // Delete image file if exists
        if ($product['image']) {
            $imagePath = __DIR__ . '/../../public/uploads/' . $product['image'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        // Delete product from database
        $success = $this->productModel->deleteProduct($productId);

        header('Content-Type: application/json');
        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Produkt byl smazán']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Chyba při mazání produktu']);
        }
        exit;
    }
}
