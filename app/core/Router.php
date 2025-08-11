<?php
namespace App\Core;

use App\Controllers\HomeController;

class Router {
    public function direct($url) {
        if ($url === '' || $url === '/') {
            $controller = new HomeController();
            $controller->index();
            return;
        }

        // Autres routes à gérer ici...
    }
}
