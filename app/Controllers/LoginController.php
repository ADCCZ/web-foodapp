<?php
// app/Controllers/LoginController.php
require_once '../app/Models/Database.php';
require_once '../app/Helpers/TwigHelper.php';

class LoginController {

    public function index() {
        // Místo require použijeme Twig
        TwigHelper::display('login.twig', [
            'session' => $_SESSION  // Předáme session data do šablony
        ]);
    }

    public function processLogin() {
        header('Content-Type: application/json');
        
        // Získáme data z AJAX požadavku
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        // 1. Připojíme se k databázi
        $db = Database::getConnection();

        // 2. Najdeme uživatele podle emailu
        // Používáme prepare() proti SQL Injection útokům!
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(); // Získáme řádek z tabulky

        // 3. Ověříme heslo
        // $user existuje A ZÁROVEŇ heslo sedí (password_verify porovná zadané heslo s hashem v DB)
        if ($user && password_verify($password, $user['password'])) {
            
            // Vše OK -> Uložíme uživatele do session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['jmeno'] = $user['jmeno'];

            echo json_encode([
                'success' => true,
                'message' => 'Přihlášení úspěšné!'
            ]);
        } else {
            // Uživatel nenalezen nebo špatné heslo
            echo json_encode([
                'success' => false,
                'message' => 'Zadali jste nesprávný email nebo heslo.'
            ]);
        }
        exit;
    }

    public function logout() {
        session_destroy();
        header('Location: ?page=home');
        exit();
    }
}
?>