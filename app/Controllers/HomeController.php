<?php
// app/Controllers/HomeController.php

namespace App\Controllers;

use App\Helpers\TwigHelper;

/**
 * HomeController
 * Handles the homepage display with hero section and feature cards
 */
class HomeController {

    /**
     * Display homepage
     * Shows welcome message and main features
     */
    public function index() {
        TwigHelper::display('home.twig', [
            'session' => $_SESSION
        ]);
    }
}
?>
