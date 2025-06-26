<?php
// Autor: Pablo Ríos Furniet
// Licencia: Creative Commons Attribution-NonCommercial 4.0 International (CC BY-NC 4.0)
// Este archivo forma parte de un proyecto con licencia no comercial.
// Puedes ver, modificar y reutilizar este código con fines personales o educativos, siempre que menciones al autor.
// Queda prohibido el uso comercial sin autorización expresa.
// Más información: https://creativecommons.org/licenses/by-nc/4.0/

session_start();
include 'config/conexion.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'];

    $query_admin = "SELECT email FROM usuarios WHERE user = :usuario";
    $stmt_admin = $conn->prepare($query_admin);
    $stmt_admin->bindParam(':usuario', $usuario);
    $stmt_admin->execute();
    $row_admin = $stmt_admin->fetch();
    if ($row_admin) {
        $token = bin2hex(random_bytes(32));
        $enlace = $_ENV['SITE_URL']."/login/reset_password/".$token;

        $update_query_admin = "UPDATE usuarios SET token_recuperacion = :token WHERE user = :usuario";
        $update_stmt_admin = $conn->prepare($update_query_admin);
        $update_stmt_admin->bindParam(':token', $token);
        $update_stmt_admin->bindParam(':usuario', $usuario);
        $update_stmt_admin->execute();

        $mail = new PHPMailer(true);
        include 'config/mail.php';
        configureMail($mail);
        $mail->isHTML(true);
        $mail->Subject = 'Restablecer contraseña | Admin Dashboard';
        $mail->CharSet = 'UTF-8';
        $mail->addAddress($row_admin['email'] ?? $row_subcontrata['correo']);
        ob_start();
        include 'layouts/correos/recuperar_password.php';
        $mail->Body = ob_get_clean();

        $mail->send();
        $_SESSION['success_message'] = "Se ha enviado un correo al usuario para recuperar su contraseña.";
        header("Location: $base/login");
        exit();
    } else {
        $_SESSION['error_message'] = "Este usuario no existe en la base de datos";
        header("Location: $base/login/recover_password");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <title>Recuperar contraseña | Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="<?= $base ?>/public/css/login.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" type="image/png" href="<?= $base ?>/public/img/favicon.png" />
</head>


<body class="d-flex align-items-center p-4">
    <div class="background-container">
        <div class="background-overlay"></div>
    </div>
    <div class="container p-0" style="max-width: 500px;">
        <form action="<?= $base ?>/login/recover_password/" method="post">
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
            <div class="form-group">
                <label for="usuario">Nombre de usuario:</label>
                <input type="text" id="usuario" name="usuario" class="form-control" required>
            </div>
            <button type="submit" class="btn mt-2" id="btn">Enviar</button>
            <p class="mt-4 text-center">
                <a class="enlace" href="<?= $base ?>/login">Volver al inicio de sesión</a>
            </p>
        </form>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.min.js"></script>
</body>

</html>