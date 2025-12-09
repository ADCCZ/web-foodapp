<?php
/**
 * PSR-4 Autoloader
 * Automatically loads classes from App namespace
 */

spl_autoload_register(function ($class) {
    // Base namespace
    $namespace = 'App\\';

    // Base directory for App namespace
    $base_dir = __DIR__ . '/';

    // Check if class uses App namespace
    $len = strlen($namespace);
    if (strncmp($namespace, $class, $len) !== 0) {
        return;
    }

    // Get relative class name
    $relative_class = substr($class, $len);

    // Convert namespace separators to directory separators
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    // If file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});
