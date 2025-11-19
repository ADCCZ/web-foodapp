<?php
// app/Controllers/RegisterController.php
require_once '../app/Models/Database.php';

class RegisterController {

    public function index() {
        require '../app/Views/register.php';
    }

    public function processRegister() {
        header('Content-Type: application/json');

        $jmeno = $_POST['jmeno'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';

        // 1. Základní validace
        if ($password !== $password_confirm) {
            echo json_encode(['success' => false, 'message' => 'Hesla se neshodují!']);
            exit;
        }

        $db = Database::getConnection();

        // 2. Ověření, zda email už neexistuje
        $stmt = $db->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Tento email už je zaregistrovaný.']);
            exit;
        }

        // 3. Vytvoření hashe a uložení
        // DEFAULTNĚ dáváme roli 'konzument'. Admina si z něj uděláš pak v databázi.
        $hash = password_hash($password, PASSWORD_BCRYPT);

        try {
            $stmt = $db->prepare("INSERT INTO users (email, password, jmeno, role, is_approved) VALUES (?, ?, ?, 'konzument', 1)");
            $stmt->execute([$email, $hash, $jmeno]);

            echo json_encode(['success' => true, 'message' => 'Registrace úspěšná! Nyní se můžete přihlásit.']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Chyba databáze: ' . $e->getMessage()]);
        }
        exit;
    }
}
?>