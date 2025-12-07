<?php

/**
 * User Model
 * Handles database operations for user management
 */
class User {
    private $conn;

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    /**
     * Get all users
     * @return array All users
     */
    public function getAllUsers() {
        $sql = "SELECT user_id, jmeno, email, role, is_approved, created_at
                FROM users
                ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get pending suppliers (waiting for approval)
     * @return array Unapproved suppliers
     */
    public function getPendingSuppliers() {
        $sql = "SELECT user_id, jmeno, email, created_at
                FROM users
                WHERE role = 'dodavatel' AND is_approved = 0
                ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get user by ID
     * @param int $userId
     * @return array|false User data or false
     */
    public function getUserById($userId) {
        $sql = "SELECT user_id, jmeno, email, role, is_approved, created_at
                FROM users
                WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Approve supplier
     * @param int $userId
     * @return bool Success
     */
    public function approveSupplier($userId) {
        $sql = "UPDATE users SET is_approved = 1 WHERE user_id = :user_id AND role = 'dodavatel'";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':user_id' => $userId]);
    }

    /**
     * Reject/disapprove supplier
     * @param int $userId
     * @return bool Success
     */
    public function rejectSupplier($userId) {
        $sql = "UPDATE users SET is_approved = 0 WHERE user_id = :user_id AND role = 'dodavatel'";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':user_id' => $userId]);
    }

    /**
     * Update user role
     * @param int $userId
     * @param string $role New role
     * @return bool Success
     */
    public function updateUserRole($userId, $role) {
        $allowedRoles = ['admin', 'dodavatel', 'konzument'];
        if (!in_array($role, $allowedRoles)) {
            return false;
        }

        $sql = "UPDATE users SET role = :role WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':user_id' => $userId,
            ':role' => $role
        ]);
    }

    /**
     * Delete user
     * @param int $userId
     * @return bool Success
     */
    public function deleteUser($userId) {
        $sql = "DELETE FROM users WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':user_id' => $userId]);
    }

    /**
     * Get user statistics
     * @return array Statistics
     */
    public function getUserStats() {
        $sql = "SELECT
                    COUNT(*) as total_users,
                    SUM(CASE WHEN role = 'konzument' THEN 1 ELSE 0 END) as total_customers,
                    SUM(CASE WHEN role = 'dodavatel' AND is_approved = 1 THEN 1 ELSE 0 END) as approved_suppliers,
                    SUM(CASE WHEN role = 'dodavatel' AND is_approved = 0 THEN 1 ELSE 0 END) as pending_suppliers,
                    SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as total_admins
                FROM users";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
