<?php

/**
 * Order Model
 * Handles database operations for orders and order items
 */
class Order {
    private $conn;

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    /**
     * Create new order with items
     * @param int $userId Customer ID
     * @param array $items Order items
     * @param float $total Total price
     * @return int|false Order ID or false on failure
     */
    public function createOrder($userId, $items, $total) {
        try {
            $this->conn->beginTransaction();

            // Insert into orders table
            $sql = "INSERT INTO orders (customer_id, total_price, status, created_at)
                    VALUES (:customer_id, :total_price, 'pending', NOW())";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':customer_id' => $userId,
                ':total_price' => $total
            ]);

            $orderId = $this->conn->lastInsertId();

            // Insert order items
            $sql = "INSERT INTO order_items (order_id, product_id, quantity, price)
                    VALUES (:order_id, :product_id, :quantity, :price)";
            $stmt = $this->conn->prepare($sql);

            foreach ($items as $item) {
                $stmt->execute([
                    ':order_id' => $orderId,
                    ':product_id' => $item['product_id'],
                    ':quantity' => $item['quantity'],
                    ':price' => $item['price']
                ]);
            }

            $this->conn->commit();
            return $orderId;

        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Error creating order: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all orders for a specific user
     * @param int $userId
     * @return array Orders with items
     */
    public function getOrdersByUserId($userId) {
        $sql = "SELECT * FROM orders
                WHERE customer_id = :customer_id
                ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':customer_id' => $userId]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get items for each order
        foreach ($orders as &$order) {
            $order['items'] = $this->getOrderItems($order['order_id']);
        }

        return $orders;
    }

    /**
     * Get orders for supplier (products they sell)
     * @param int $supplierId
     * @return array Orders containing supplier's products
     */
    public function getOrdersBySupplierId($supplierId) {
        $sql = "SELECT DISTINCT o.*, u.jmeno as customer_name, u.email as customer_email
                FROM orders o
                JOIN order_items oi ON o.order_id = oi.order_id
                JOIN products p ON oi.product_id = p.product_id
                JOIN users u ON o.customer_id = u.user_id
                WHERE p.supplier_id = :supplier_id
                ORDER BY o.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':supplier_id' => $supplierId]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get only supplier's items for each order
        foreach ($orders as &$order) {
            $order['items'] = $this->getOrderItemsBySupplierId($order['order_id'], $supplierId);
        }

        return $orders;
    }

    /**
     * Get order by ID
     * @param int $orderId
     * @return array|false Order data or false
     */
    public function getOrderById($orderId) {
        $sql = "SELECT o.*, u.jmeno as customer_name, u.email as customer_email
                FROM orders o
                JOIN users u ON o.customer_id = u.user_id
                WHERE o.order_id = :order_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':order_id' => $orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($order) {
            $order['items'] = $this->getOrderItems($orderId);
        }

        return $order;
    }

    /**
     * Get all items for an order
     * @param int $orderId
     * @return array Order items with product details
     */
    private function getOrderItems($orderId) {
        $sql = "SELECT oi.*, p.name, p.image, p.supplier_id, u.jmeno as supplier_name
                FROM order_items oi
                JOIN products p ON oi.product_id = p.product_id
                JOIN users u ON p.supplier_id = u.user_id
                WHERE oi.order_id = :order_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':order_id' => $orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get order items for specific supplier
     * @param int $orderId
     * @param int $supplierId
     * @return array Order items from this supplier
     */
    private function getOrderItemsBySupplierId($orderId, $supplierId) {
        $sql = "SELECT oi.*, p.name, p.image
                FROM order_items oi
                JOIN products p ON oi.product_id = p.product_id
                WHERE oi.order_id = :order_id AND p.supplier_id = :supplier_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':order_id' => $orderId,
            ':supplier_id' => $supplierId
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Update order status
     * @param int $orderId
     * @param string $status New status
     * @return bool Success
     */
    public function updateOrderStatus($orderId, $status) {
        $sql = "UPDATE orders SET status = :status WHERE order_id = :order_id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':order_id' => $orderId,
            ':status' => $status
        ]);
    }

    /**
     * Get all orders (for admin)
     * @return array All orders
     */
    public function getAllOrders() {
        $sql = "SELECT o.*, u.jmeno as customer_name, u.email as customer_email
                FROM orders o
                JOIN users u ON o.customer_id = u.user_id
                ORDER BY o.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($orders as &$order) {
            $order['items'] = $this->getOrderItems($order['order_id']);
        }

        return $orders;
    }
}
