<?php
$usuario = $usuario ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido | FinanzApp</title>
    <link rel="stylesheet" href="assets/css/normalize.css">
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body class="authPage">
    <main class="mainContent" id="main-content" style="grid-template-columns:1fr; max-width:640px;">
        <section class="loginSession" style="padding: var(--space-xl); border-radius:12px;">
            <h1 class="authTitle">Hola, <?= e($usuario['nombre'] ?? '') ?> 👋</h1>
            <p class="authDescription">Sesión iniciada correctamente. Aquí aparecerá tu resumen de finanzas.</p>
            <form class="authForm" method="post" action="index.php?r=logout">
                <?= Csrf::field() ?>
                <button class="primaryButton" type="submit">
                    <span class="buttonText">Cerrar sesión</span>
                </button>
            </form>
        </section>
    </main>
</body>
</html>
