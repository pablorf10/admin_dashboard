<?php
// Autor: Pablo Ríos Furniet
// Licencia: Creative Commons Attribution-NonCommercial 4.0 International (CC BY-NC 4.0)
// Este archivo forma parte de un proyecto con licencia no comercial.
// Puedes ver, modificar y reutilizar este código con fines personales o educativos, siempre que menciones al autor.
// Queda prohibido el uso comercial sin autorización expresa.
// Más información: https://creativecommons.org/licenses/by-nc/4.0/

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
 
$dotenv = Dotenv::createImmutable(__DIR__ . '/');
$dotenv->load();

$request = $_SERVER['REQUEST_URI'];
$subdir = $_ENV['BASE_URL'] ?? '/';

if (strpos($request, $subdir) === 0) {
    $request = substr($request, strlen($subdir));
}
 
$segments = explode('/', trim($request, '/'));

$modulo = $segments[0] ?: 'login';

if (isset($segments[1]) && is_numeric($segments[1])) {
    $accion = 'detalle';
    $_GET['id'] = $segments[1];

    if (isset($segments[2]) && is_numeric($segments[2])) {
        $_GET['id_usuario'] = $segments[2];
    }
} else {
    $accion = $segments[1] ?? 'index';

    if (isset($segments[2])) {
        $_GET['id'] = $segments[2];
    }
}

$filepath = __DIR__ . "/src/{$modulo}/{$accion}.php";

if (file_exists($filepath)) {
    include $filepath;
} else {
    http_response_code(404);
    include __DIR__ . '/layouts/errores/404.php';
}