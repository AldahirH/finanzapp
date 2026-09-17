<?php

// 1. Iniciamos la sesión ANTES DE emitir cualquier HTML.
session_start();

// 2. Cargamos las clases que usaremos.
require __DIR__ . '/../app/support/database.php';
require __DIR__ . '/../app/support/auth.php';
require __DIR__ . '/../app/support/csrf.php';
require __DIR__ . '/../app/models/usuario.php';
require __DIR__ . '/../app/controllers/authController.php';

// 3. Cargamos la tabla de rutas.
$routes = require __DIR__ . '/../routes/web.php';

// 4. Función global: espaca texto antes de mostrarlo en HTML (anti-XSS).
function e(?string $texto){
    return htmlspecialchars($texto ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// 5. Averiguamos qué ruta pidieron y con qué método.
$route = $_GET['r'] ?? 'login';
$method = $_SERVER['REQUEST_METHOD'];

// 6. Si no existe esa ruta ni ese método, terminamos con error.
if (!is_string($route) || !isset($routes[$route][$method])) {
    http_response_code(404);
    exit('No encontrado.');
}

// 7. Despachamos: creamos el controlador y llamamos al método correspondiente.
$ruta = $routes[$route][$method];
$controlador = new $ruta[0]();
$controlador->{$ruta[1]}();