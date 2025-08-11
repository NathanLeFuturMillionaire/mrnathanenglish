<?php
// Active l'autoload de Composer
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database; // On appelle la classe avec son namespace
use App\Core\Router;

$db = new Database();
$conn = $db->connect();


$url = $_GET['url'] ?? '';

$router = new Router();
$router->direct($url);
