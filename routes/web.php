<?php

return[
    'login' => [
        'GET'  => [authController::class, 'showLogin'],
        'POST' => [authController::class, 'iniciarSesion'],
    ],
    'registro' => [
        'GET'  => [authController::class, 'showRegistro'],
        'POST' => [authController::class, 'registrar'],
    ],
    'inicio' => [
        'GET'  => [authController::class, 'inicio'],
    ],
    'logout' => [
        'POST' => [authController::class, 'cerrarSesion'],
    ],
];