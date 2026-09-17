<?php

declare(strict_types=1);

class AuthController{
     // ---------- Pantallas ----------

    public function showLogin(): void
    {
        if (auth::check()) {
            header('Location: index.php?r=inicio');
            exit;
        }

        $this->renderLogin('login');
    }

    public function showRegistro(): void
    {
        if (auth::check()) {
            header('Location: index.php?r=inicio');
            exit;
        }

        $this->renderLogin('registro');
    }

    private function renderLogin(string $activeTab): void
    {
        $errores = $_SESSION['flash_errores'] ?? [];
        $old     = $_SESSION['flash_old']     ?? [];
        $exito   = $_SESSION['flash_exito']   ?? null;

        unset($_SESSION['flash_errores'], $_SESSION['flash_old'], $_SESSION['flash_exito']);

        require __DIR__ . '/../views/auth/login.php';
    }

    // ---------- Acciones (POST) ----------

    public function registrar(): void
    {
        if (!csrf::verify($_POST['csrf_token'] ?? null)) {
            $this->volverConErrores('registro', ['El token de seguridad no es válido.'], []);
        }

        $nombre  = trim((string) ($_POST['nombre'] ?? ''));
        $email   = strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');
        $confirm = (string) ($_POST['password_confirmation'] ?? '');

        $errores = $this->validarRegistro($nombre, $email, $password, $confirm);

        $modelo = new Usuario();

        if ($errores === [] && $modelo->porEmail($email) !== null) {
            $errores[] = 'El correo ya está registrado.';
        }

        if ($errores === []) {
            $hash  = password_hash($password, PASSWORD_DEFAULT);
            $creado = $modelo->crearConCategorias($nombre, $email, $hash);

            if ($creado) {
                $_SESSION['flash_exito'] = 'Cuenta creada. Ahora inicia sesión.';
                header('Location: index.php?r=login');
                exit;
            }

            $errores[] = 'No se pudo crear la cuenta. Inténtalo de nuevo.';
        }

        $this->volverConErrores('registro', $errores, ['nombre' => $nombre, 'email' => $email]);
    }

    public function iniciarSesion(): void
    {
        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            $this->volverConErrores('login', ['El token de seguridad no es válido.'], []);
        }

        $email    = strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            $this->volverConErrores('login', ['Introduce correo y contraseña.'], ['email' => $email]);
        }

        $modelo  = new Usuario();
        $usuario = $modelo->porEmail($email);

        if ($usuario === null || !password_verify($password, $usuario['password_hash'])) {
            $this->volverConErrores('login', ['Correo o contraseña incorrectos.'], ['email' => $email]);
        }

        auth::login((int) $usuario['id']);

        header('Location: index.php?r=inicio');
        exit;
    }

    public function inicio(): void
    {
        if (!auth::check()) {
            header('Location: index.php?r=login');
            exit;
        }

        $usuario = (new Usuario())->porId(auth::id());

        require __DIR__ . '/../views/inicio/bienvenida.php';
    }

    public function cerrarSesion(): void
    {
        if (Csrf::verify($_POST['csrf_token'] ?? null)) {
            Auth::logout();
        }

        header('Location: index.php?r=login');
        exit;
    }

    // ---------- Ayudantes ----------

    private function validarRegistro(string $nombre, string $email, string $password, string $confirm): array
    {
        $errores = [];

        if ($nombre === '') {
            $errores[] = 'El nombre es obligatorio.';
        } elseif (strlen($nombre) > 100) {
            $errores[] = 'El nombre no puede superar 100 caracteres.';
        }

        if ($email === '') {
            $errores[] = 'El correo es obligatorio.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'Introduce un correo válido.';
        } elseif (strlen($email) > 254) {
            $errores[] = 'El correo es demasiado largo.';
        }

        if (strlen($password) < 8) {
            $errores[] = 'La contraseña debe tener al menos 8 caracteres.';
        } elseif (strlen($password) > 255) {
            $errores[] = 'La contraseña es demasiado larga.';
        }

        if ($password !== $confirm) {
            $errores[] = 'La confirmación no coincide con la contraseña.';
        }

        return $errores;
    }

    private function volverConErrores(string $ruta, array $errores, array $old): void
    {
        $_SESSION['flash_errores'] = $errores;
        $_SESSION['flash_old']     = $old;

        header('Location: index.php?r=' . $ruta);
        exit;
    }
}
