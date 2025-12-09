<?php

namespace App\Controllers;

use App\Models\Database;
use App\Helpers\TwigHelper;

class LoginController {

    /**
     * Display login page
     */
    public function index() {
        TwigHelper::display('login.twig', [
            'session' => $_SESSION
        ]);
    }

    /**
     * Process login form via AJAX
     * Verifies credentials and creates session
     * Uses password_verify() for secure password checking
     */
    public function processLogin() {
        header('Content-Type: application/json');

        // Get credentials from POST request
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        // Connect to database
        $db = Database::getConnection();

        // Find user by email (using prepared statement for SQL injection protection)
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        // Verify password using bcrypt
        if ($user && password_verify($password, $user['password'])) {

            // Check if supplier is approved
            if ($user['role'] === 'dodavatel' && $user['is_approved'] == 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Váš účet čeká na schválení administrátorem. Po schválení obdržíte plný přístup.',
                    'awaiting_approval' => true
                ]);
                exit;
            }

            // Authentication successful - create session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['jmeno'] = $user['jmeno'];
            $_SESSION['is_approved'] = $user['is_approved'];
            $_SESSION['is_super_admin'] = $user['is_super_admin'] ?? 0;

            echo json_encode([
                'success' => true,
                'message' => 'Přihlášení úspěšné!'
            ]);
        } else {
            // Authentication failed
            echo json_encode([
                'success' => false,
                'message' => 'Zadali jste nesprávný email nebo heslo.'
            ]);
        }
        exit;
    }

    /**
     * Logout user
     * Destroys session and redirects to homepage
     */
    public function logout() {
        session_destroy();
        header('Location: ?page=home');
        exit();
    }
}
?>