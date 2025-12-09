<?php

namespace App\Models;

use PDO;

/**
 * Product Model
 * Handles database operations for products
 */
class Product {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    /**
     * Get all products with supplier information
     * Only shows products from approved suppliers
     * @return array List of products
     */
    public function getAllProducts() {
        $sql = "SELECT p.*, u.jmeno as supplier_name
                FROM products p
                LEFT JOIN users u ON p.supplier_id = u.user_id
                WHERE u.is_approved = 1
                ORDER BY p.product_id DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get product by ID
     * @param int $productId Product ID
     * @return array|false Product data or false
     */
    public function getProductById($productId) {
        $sql = "SELECT p.*, u.jmeno as supplier_name
                FROM products p
                LEFT JOIN users u ON p.supplier_id = u.user_id
                WHERE p.product_id = :product_id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get products by supplier ID
     * @param int $supplierId Supplier user ID
     * @return array List of products
     */
    public function getProductsBySupplierId($supplierId) {
        $sql = "SELECT * FROM products
                WHERE supplier_id = :supplier_id
                ORDER BY created_at DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':supplier_id', $supplierId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create new product
     * @param int $supplierId Supplier user ID
     * @param string $name Product name
     * @param string $description Product description
     * @param float $price Product price
     * @param string|null $image Image filename
     * @return bool Success status
     */
    public function createProduct($supplierId, $name, $description, $price, $image = null) {
        $sql = "INSERT INTO products (supplier_id, name, description, price, image)
                VALUES (:supplier_id, :name, :description, :price, :image)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':supplier_id', $supplierId, PDO::PARAM_INT);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        $stmt->bindParam(':price', $price, PDO::PARAM_STR);
        $stmt->bindParam(':image', $image, PDO::PARAM_STR);

        return $stmt->execute();
    }

    /**
     * Update product
     * @param int $productId Product ID
     * @param string $name Product name
     * @param string $description Product description
     * @param float $price Product price
     * @param string|null $image Image filename (optional)
     * @return bool Success status
     */
    public function updateProduct($productId, $name, $description, $price, $image = null) {
        if ($image) {
            $sql = "UPDATE products
                    SET name = :name, description = :description,
                        price = :price, image = :image
                    WHERE product_id = :product_id";
        } else {
            $sql = "UPDATE products
                    SET name = :name, description = :description, price = :price
                    WHERE product_id = :product_id";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        $stmt->bindParam(':price', $price, PDO::PARAM_STR);
        $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);

        if ($image) {
            $stmt->bindParam(':image', $image, PDO::PARAM_STR);
        }

        return $stmt->execute();
    }

    /**
     * Delete product
     * @param int $productId Product ID
     * @return bool Success status
     */
    public function deleteProduct($productId) {
        $sql = "DELETE FROM products WHERE product_id = :product_id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Search and filter products
     * @param string|null $searchQuery Search term for name/description
     * @param int|null $supplierId Filter by supplier ID
     * @return array List of filtered products
     */
    public function searchProducts($searchQuery = null, $supplierId = null) {
        $sql = "SELECT p.*, u.jmeno as supplier_name
                FROM products p
                LEFT JOIN users u ON p.supplier_id = u.user_id
                WHERE u.is_approved = 1";

        $params = [];

        // Add search query filter
        if ($searchQuery && trim($searchQuery) !== '') {
            $sql .= " AND (p.name LIKE :search1 OR p.description LIKE :search2)";
            $params[':search1'] = '%' . $searchQuery . '%';
            $params[':search2'] = '%' . $searchQuery . '%';
        }

        // Add supplier filter
        if ($supplierId && $supplierId > 0) {
            $sql .= " AND p.supplier_id = :supplier_id";
            $params[':supplier_id'] = $supplierId;
        }

        $sql .= " ORDER BY p.created_at DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all approved suppliers who have products
     * @return array List of suppliers
     */
    public function getAllSuppliers() {
        $sql = "SELECT DISTINCT u.user_id, u.jmeno
                FROM users u
                INNER JOIN products p ON u.user_id = p.supplier_id
                WHERE u.is_approved = 1 AND u.role = 'dodavatel'
                ORDER BY u.jmeno ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
