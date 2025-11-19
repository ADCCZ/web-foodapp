<?php
// app/Helpers/TwigHelper.php

require_once __DIR__ . '/../../vendor/autoload.php';

class TwigHelper {
    private static $twig = null;

    /**
     * Inicializace Twig enginu (singleton pattern)
     */
    public static function getTwig() {
        if (self::$twig === null) {
            $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../Views/templates');
            self::$twig = new \Twig\Environment($loader, [
                'cache' => __DIR__ . '/../Views/cache', // Pro rychlost - zkompilované šablony
                'auto_reload' => true, // Při vývoji true, na produkci false
                'debug' => true, // Pro vývoj
            ]);

            // Přidání debug extension (užitečné při vývoji)
            self::$twig->addExtension(new \Twig\Extension\DebugExtension());
        }
        return self::$twig;
    }

    /**
     * Vyrenderuje šablonu a vrátí HTML jako string
     *
     * @param string $template - název šablony (např. 'login.twig')
     * @param array $data - data pro šablonu (např. ['jmeno' => 'Petr'])
     * @return string - vygenerované HTML
     */
    public static function render($template, $data = []) {
        $twig = self::getTwig();
        return $twig->render($template, $data);
    }

    /**
     * Vyrenderuje šablonu a rovnou ji vypíše (echo)
     *
     * @param string $template
     * @param array $data
     */
    public static function display($template, $data = []) {
        echo self::render($template, $data);
    }
}
?>
