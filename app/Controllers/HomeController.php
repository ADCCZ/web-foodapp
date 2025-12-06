<?php
// app/Controllers/HomeController.php
require_once '../app/Helpers/TwigHelper.php';

class HomeController {

    public function index() {
        TwigHelper::display('home.twig', [
            'session' => $_SESSION
        ]);
    }
}
?>
