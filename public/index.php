<?php
// Active l'autoload de Composer
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database; // On appelle la classe avec son namespace
use App\Core\Router;

$db = new Database();
$conn = $db->connect();


$url = $_GET['url'] ?? '';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$router = new Router();
$router->direct($url, $method);


// Début : 11 Aout 2025.