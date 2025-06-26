<?php
// Autor: Pablo Ríos Furniet
// Licencia: Creative Commons Attribution-NonCommercial 4.0 International (CC BY-NC 4.0)
// Este archivo forma parte de un proyecto con licencia no comercial.
// Puedes ver, modificar y reutilizar este código con fines personales o educativos, siempre que menciones al autor.
// Queda prohibido el uso comercial sin autorización expresa.
// Más información: https://creativecommons.org/licenses/by-nc/4.0/

session_start();
include "config/conexion.php";

if (isset($_SESSION['id_usuario'])) {
    header("Location: $base/dashboard");
    exit;
}

if (!isset($_GET['id'])) {
    $partes = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
    $token = end($partes);
} else {
    $token = $_GET['id'];
}

$token_query_admin = "SELECT * FROM usuarios WHERE token_recuperacion = :token";
$token_stmt_admin = $conn->prepare($token_query_admin);
$token_stmt_admin->bindParam(':token', $token);
$token_stmt_admin->execute();
$token_row = $token_stmt_admin->fetch();

if (!$token_row) {
    $_SESSION['error_message'] = "El token no existe o ya ha caducado.";
    header("Location: $base/login/recover_password");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = $_POST['password'];
    $password_repeat = $_POST['password_repeat'];

    if ($password !== $password_repeat) {
        $_SESSION['error_message'] = "Las contraseñas no coinciden.";
        header("Location: $base/login/reset_password/$token");
        exit();
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        if ($token_row) {
            $update_query = "UPDATE usuarios SET password = :password, token_recuperacion = NULL WHERE token_recuperacion = :token";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bindParam(':password', $hashed_password);
            $update_stmt->bindParam(':token', $token);
        }

        if ($update_stmt->execute()) {
            // Iniciar sesión del usuario
            $_SESSION['id_usuario'] = $token_row['id'];
            $_SESSION['user'] = $token_row['user'];
            $_SESSION['success_message'] = "Contraseña restablecida exitosamente.";
            header("Location: $base/dashboard");
            exit();
        } else {
            $_SESSION['error_message'] = "Error al restablecer la contraseña. Inténtelo de nuevo.";
            header("Location: $base/login/reset_password/$token");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <title>Restablecer Contraseña | Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="<?= $base ?>/public/css/login.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" type="image/png" href="<?= $base ?>/public/img/favicon.png" />
    <script src="<?= $base ?>/public/js/index.js"></script>
</head>

<body class="d-flex align-items-center p-4">
    <div class="background-container">
        <div class="background-overlay"></div>
    </div>
    <div class="container p-0" style="max-width: 500px;">
        <form action="<?= $base ?>/login/reset_password/<?= $token ?>" method="post" id="passwordForm">
            <a href="<?= $base ?>/login"><img src="<?= $base ?>/public/img/logo.png" alt="Logo Mantotal" class="img-fluid mb-3 pl-5 pr-5"></a>
            <?php if (!empty($_SESSION['success_message'])) : ?>
                <div class="text-center alert alert-success alert-dismissible fade show">
                    <?php echo $_SESSION['success_message']; ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php unset($_SESSION['success_message']);
            endif; ?>
            <?php if (!empty($_SESSION['error_message'])) : ?>
                <div class="text-center alert alert-danger alert-dismissible fade show">
                    <?php echo $_SESSION['error_message']; ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php unset($_SESSION['error_message']);
            endif; ?>
            <p>La nueva contraseña debe contener mínimo 8 caracteres, una mayúscula, un número y un caracter especial (Ejemplos. $@?!)</p>
            <div class="form-group password-container">
                <label for="password">Contraseña Nueva:</label>
                <input type="password" id="password" name="password" class="form-control" required oninput="checkPasswordStrength()">
                <i id="eyeIcon" class="bx bx-show" onclick="togglePasswordVisibility()"></i>
                <div id="passwordStrength" class="mt-2"></div>
            </div>
            <div class="form-group password-container">
                <label for="password_repeat">Repetir Contraseña:</label>
                <input type="password" id="password_repeat" name="password_repeat" class="form-control" required>
                <i id="eyeIconRepeat" class="bx bx-show" onclick="togglePasswordVisibilityRepeat()"></i>
            </div>
            <button type="submit" name="cambiar_contrasena" class="btn mt-2" id="btnSubmit">Cambiar Contraseña</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.min.js"></script>
    <script src="https://unpkg.com/boxicons@2.1.4/dist/boxicons.js"></script>
</body>

</html>