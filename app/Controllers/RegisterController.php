<?php

namespace App\Controllers;

use App\Models\Database;
use App\Helpers\TwigHelper;

class RegisterController {

    /**
     * Display registration page
     */
    public function index() {
        TwigHelper::display('register.twig', [
            'session' => $_SESSION
        ]);
    }

    /**
     * Process registration form via AJAX
     * Creates new user account with bcrypt password hashing
     * Customers are auto-approved, suppliers require admin approval
     */
    public function processRegister() {
        header('Content-Type: application/json');

        // Get form data
        $jmeno = $_POST['jmeno'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';
        $role = $_POST['role'] ?? 'konzument';

        // Validate password match
        if ($password !== $password_confirm) {
            echo json_encode(['success' => false, 'message' => 'Hesla se neshodují!']);
            exit;
        }

        // Validate role (only customer or supplier allowed)
        if (!in_array($role, ['konzument', 'dodavatel'])) {
            echo json_encode(['success' => false, 'message' => 'Neplatná role!']);
            exit;
        }

        $db = Database::getConnection();

        // Check if email already exists
        $stmt = $db->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Tento email už je zaregistrovaný.']);
            exit;
        }

        // Hash password using bcrypt
        $hash = password_hash($password, PASSWORD_BCRYPT);

        // Suppliers need approval, customers are auto-approved
        $is_approved = ($role === 'konzument') ? 1 : 0;

        try {
            // Insert new user into database
            $stmt = $db->prepare("INSERT INTO users (email, password, jmeno, role, is_approved) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$email, $hash, $jmeno, $role, $is_approved]);

            // Return appropriate success message
            if ($role === 'dodavatel') {
                echo json_encode(['success' => true, 'message' => 'Registrace úspěšná! Váš účet musí schválit administrátor.']);
            } else {
                echo json_encode(['success' => true, 'message' => 'Registrace úspěšná! Nyní se můžete přihlásit.']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Chyba databáze: ' . $e->getMessage()]);
        }
        exit;
    }
}
?>