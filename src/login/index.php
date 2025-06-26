<?php
// Autor: Pablo Ríos Furniet
// Licencia: Creative Commons Attribution-NonCommercial 4.0 International (CC BY-NC 4.0)
// Este archivo forma parte de un proyecto con licencia no comercial.
// Puedes ver, modificar y reutilizar este código con fines personales o educativos, siempre que menciones al autor.
// Queda prohibido el uso comercial sin autorización expresa.
// Más información: https://creativecommons.org/licenses/by-nc/4.0/

session_start();
include 'config/conexion.php';

$response = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['user'];
    $password = $_POST['password'];

    if (isset($_SESSION['user'])) {
        $response['redirect'] = $_SESSION['redirect_url'] ?? $base . '/dashboard';
    } else {
        $queryUsuarios = "SELECT id, user, password FROM usuarios WHERE user = :user";
        $stmtUsuarios = $conn->prepare($queryUsuarios);
        $stmtUsuarios->bindParam(':user', $user);
        $stmtUsuarios->execute();
        $rowUsuarios = $stmtUsuarios->fetch();

        if ($rowUsuarios) {
            $hashed_password = $rowUsuarios['password'];

            if (password_verify($password, $hashed_password)) {
                $_SESSION['id_usuario'] = $rowUsuarios['id'];
                $_SESSION['user'] = $rowUsuarios['user'];

                if (isset($_SESSION['redirect_url'])) {
                    $response['redirect'] = $_SESSION['redirect_url'];
                    unset($_SESSION['redirect_url']);
                } else {
                    $response['redirect'] = 'dashboard';
                }
            } else {
                $response['error_message'] = "Contraseña incorrecta. Inténtalo de nuevo.";
            }
        } else {
            $response['error_message'] = "Usuario no encontrado. Inténtalo de nuevo.";
        }
    }

    echo json_encode($response);
    exit;
} else {
    if (isset($_SESSION['user'])) {
        header('Location: ' . ($_SESSION['redirect_url'] ?? $base . '/dashboard'));
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <title>Iniciar sesión | Admin Dashboard</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="public/css/login.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="public/img/favicon.png" />
    <script src="public/js/index.js"></script>
</head>

<body class="d-flex align-items-center p-4">
    <div class="background-container">
        <div class="background-overlay"></div>
    </div>
    <div class="container p-0" style="max-width: 500px;">
        <form id="loginForm">
            <a href=""><img src="public/img/logo.png" alt="Logo Mantotal" class="img-fluid mb-3 pl-5 pr-5"></a>
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
            <div id="message-container"></div>
            <div class="form-group mt-2 password-container">
                <label for="user">Usuario:</label>
                <input type="text" id="user" name="user" class="form-control" required>
                <i id="userIcon" class="bx bx-user"></i>
            </div>
            <div class="form-group password-container">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" class="form-control" required>
                <i id="eyeIcon" class="bx bx-show" onclick="togglePasswordVisibility()"></i>
            </div>
            <button type="submit" class="btn mt-2" id="btnSubmit">Iniciar sesión</button>
            <p class="mt-4 text-center">
                <a class="enlace" href="<?= $base ?>/login/recover_password">¿Olvidaste tu contraseña?</a>
            </p>
        </form>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#loginForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    type: 'POST',
                    url: '<?= $base ?>/login',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.redirect) {
                            window.location.href = response.redirect;
                        } else if (response.error_message) {
                            $('#message-container').html('<div class="text-center alert alert-danger alert-dismissible fade show">' +
                                response.error_message +
                                '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                                '<span aria-hidden="true">&times;</span>' +
                                '</button>' +
                                '</div>');
                        }
                    }
                });
            });
        });
    </script>
</body>

</html>