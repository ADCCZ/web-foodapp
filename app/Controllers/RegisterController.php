<?php
// app/Controllers/RegisterController.php
require_once '../app/Models/Database.php';
require_once '../app/Helpers/TwigHelper.php';

class RegisterController {

    public function index() {
        TwigHelper::display('register.twig', [
            'session' => $_SESSION
        ]);
    }

    public function processRegister() {
        header('Content-Type: application/json');

        $jmeno = $_POST['jmeno'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';
        $role = $_POST['role'] ?? 'konzument'; // Nova: role z formulare

        // 1. Základní validace
        if ($password !== $password_confirm) {
            echo json_encode(['success' => false, 'message' => 'Hesla se neshodují!']);
            exit;
        }

        // Validace role
        if (!in_array($role, ['konzument', 'dodavatel'])) {
            echo json_encode(['success' => false, 'message' => 'Neplatná role!']);
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
        $hash = password_hash($password, PASSWORD_BCRYPT);

        // Dodavatele je třeba schválit (is_approved = 0), zákazníci jsou automaticky schváleni
        $is_approved = ($role === 'konzument') ? 1 : 0;

        try {
            $stmt = $db->prepare("INSERT INTO users (email, password, jmeno, role, is_approved) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$email, $hash, $jmeno, $role, $is_approved]);

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