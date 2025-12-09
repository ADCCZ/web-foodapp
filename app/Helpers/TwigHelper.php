<?php
// app/Helpers/TwigHelper.php

namespace App\Helpers;

use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use Twig\Extension\DebugExtension;

/**
 * TwigHelper
 * Helper class for Twig template engine
 * Uses singleton pattern for Twig Environment instance
 */
class TwigHelper {
    private static $twig = null;

    /**
     * Get Twig Environment instance (singleton)
     * Initializes Twig with cache and debug settings
     * @return \Twig\Environment Twig instance
     */
    public static function getTwig() {
        if (self::$twig === null) {
            // Load templates from app/Views/templates directory
            $loader = new FilesystemLoader(__DIR__ . '/../Views/templates');

            // Create Twig environment with configuration
            self::$twig = new Environment($loader, [
                'cache' => __DIR__ . '/../Views/cache',  // Compiled templates cache
                'auto_reload' => true,                    // Auto-reload on template change (dev mode)
                'debug' => true,                          // Enable debug mode (dev mode)
                'autoescape' => 'html',                   // XSS protection - escape HTML by default
            ]);

            // Add debug extension for development
            self::$twig->addExtension(new DebugExtension());
        }
        return self::$twig;
    }

    /**
     * Render template and return HTML string
     * @param string $template Template filename (e.g., 'login.twig')
     * @param array $data Data to pass to template
     * @return string Rendered HTML
     */
    public static function render($template, $data = []) {
        $twig = self::getTwig();
        return $twig->render($template, $data);
    }

    /**
     * Render template and output directly
     * @param string $template Template filename
     * @param array $data Data to pass to template
     */
    public static function display($template, $data = []) {
        echo self::render($template, $data);
    }
}
?>
