<?php
// Autor: Pablo Ríos Furniet
// Licencia: Creative Commons Attribution-NonCommercial 4.0 International (CC BY-NC 4.0)
// Este archivo forma parte de un proyecto con licencia no comercial.
// Puedes ver, modificar y reutilizar este código con fines personales o educativos, siempre que menciones al autor.
// Queda prohibido el uso comercial sin autorización expresa.
// Más información: https://creativecommons.org/licenses/by-nc/4.0/

session_start();
include 'config/conexion.php';
include 'actions/select/datos_usuario.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['cambiar_contrasena'])) {
        $current_password = $_POST['current_password'];
        $password = $_POST['password'];
        $password_repeat = $_POST['password_repeat'];

        if ($password != $password_repeat) {
            $_SESSION['error_message'] = "Las nuevas contraseñas no coinciden.";
            header("Location: $base/perfil");
            exit;
        }

        $query = "SELECT password FROM usuarios WHERE id = :id_usuario";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id_usuario', $_SESSION['id_usuario']);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result && password_verify($current_password, $result['password'])) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $query = "UPDATE usuarios SET password = :password WHERE id = :id_usuario";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':id_usuario', $_SESSION['id_usuario']);

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Contraseña actualizada con éxito.";
            } else {
                $_SESSION['error_message'] = "Error al actualizar la contraseña.";
            }
        } else {
            $_SESSION['error_message'] = "La contraseña actual es incorrecta.";
        }

        header("Location: $base/perfil");
        exit;
    } elseif (isset($_POST['cambiar_foto'])) {
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            $foto = $_FILES['foto'];
            $extension = pathinfo($foto['name'], PATHINFO_EXTENSION);
            $nombre_foto = $usuario['user'];
            $ruta_foto = "public/img/usuarios/";

            $extensiones = ['png', 'jpg', 'jpeg'];
            foreach ($extensiones as $ext) {
                $ruta_foto_existente = $ruta_foto . $nombre_foto . '.' . $ext;
                if (file_exists($ruta_foto_existente)) {
                    unlink($ruta_foto_existente);
                }
            }

            $ruta_foto_final = $ruta_foto . $nombre_foto . '.' . $extension;
            $foto_bd = $nombre_foto . '.' . $extension;

            if (move_uploaded_file($foto['tmp_name'], $ruta_foto_final)) {
                $query = "UPDATE usuarios SET foto = :foto WHERE id = :id_usuario";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':foto', $foto_bd);
                $stmt->bindParam(':id_usuario', $_SESSION['id_usuario']);

                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "Foto actualizada con éxito.";
                } else {
                    $_SESSION['error_message'] = "Error al actualizar la foto.";
                }
            } else {
                $_SESSION['error_message'] = "Error al mover la foto subida.";
            }
        } else {
            $_SESSION['error_message'] = "Error en la subida de la foto.";
        }
        header("Location: $base/perfil");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <?php include "layouts/head.php" ?>
    <title>Perfil | Admin Dashboard</title>
</head>

<body>
    <?php include('layouts/sidebar.php'); ?>
    <!-- Main Content -->
    <div class="content">
        <?php include('layouts/navbar.php'); ?>
        <main>
            <div class="header">
                <div class="left">
                    <h1>Mi perfil</h1>
                </div>
                <div class="report">
                    <?php if (isset($_SESSION['error_message'])) { ?>
                        <div class="error-message"><?php echo $_SESSION['error_message']; ?></div>
                    <?php unset($_SESSION['error_message']);
                    } ?>
                    <?php if (isset($_SESSION['success_message'])) { ?>
                        <div class="success-message"><?php echo $_SESSION['success_message']; ?></div>
                    <?php unset($_SESSION['success_message']);
                    } ?>
                </div>
                <script>
                    document.querySelector('.report').addEventListener('click', function() {
                        document.querySelector('.report').style.display = 'none';
                    });
                </script>
            </div>

            <div class="bottom-data pb-4">

                <!-- Datos empelado -->
                <div class="reminders">
                    <div class="header">
                        <i class='bx bx-user'></i>
                        <h3>Datos usuario</h3>
                    </div>
                    <div class="row d-flex justify-content-start">
                        <div class="user col-lg-12 d-flex justify-content-start">
                            <?php if (!$usuario['foto']) { ?>
                                <img class="mb-3 mr-3" src="public/img/perfil.png">
                            <?php } else { ?>
                                <img class="mb-3 mr-3" src="public/img/usuarios/<?php echo $usuario['foto'] ?>">
                            <?php } ?>
                            <form action="<?= $base ?>/perfil" method="post" enctype="multipart/form-data" id="fotoForm">
                                <input type="file" name="foto" accept=".png, .jpg, .jpeg" style="display:none;" id="fotoInput" onchange="document.getElementById('fotoForm').submit();">
                                <button type="button" id="btnCambiar" onclick="document.getElementById('fotoInput').click();">Cambiar foto</button>
                                <input type="hidden" name="cambiar_foto" value="1">
                            </form>
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-12">
                            <strong>Usuario: </strong><?php echo $usuario['user']; ?>
                        </div>
                        <div class="col-lg-6">
                            <strong>Dni: </strong><?php echo $usuario['dni']; ?>
                        </div>
                        <div class="col-lg-12">
                            <strong>Nombre: </strong><?php echo $usuario['nombre'] . " " . $usuario['apellidos']; ?>
                        </div>
                    </div>
                    <div class="header mt-3">
                        <i class='bx bx-id-card'></i>
                        <h3>Contacto</h3>
                    </div>
                    <div class="row d-flex justify-content-start">
                        <div class="col-lg-6 col-md-12">
                            <strong>Extensión: </strong><?php echo $usuario['extension']; ?>
                        </div>
                        <div class="col-lg-6 col-md-12">
                            <strong>Móvil: </strong><?php echo $usuario['movil']; ?>
                        </div>
                        <div class="col-lg-12 col-md-12">
                            <strong>Correo electrónico: </strong><?php echo $usuario['email']; ?>
                        </div>
                    </div>
                    <div class="header mt-3">
                        <i class='bx bx-info-circle'></i>
                        <h3>Otra información</h3>
                    </div>
                    <div class="row d-flex justify-content-start align-items-center">
                        <div class="col-lg-6 col-md-12">
                            <strong>Puesto: </strong><?php echo $usuario['puesto']; ?>
                        </div>
                        <div class="col-lg-6 col-md-12">
                            <strong>Tipo: </strong><?php echo $usuario['oficina_obra']; ?>
                        </div>
                        <div class="col-lg-6 col-md-12">
                            <strong>Ubicación: </strong><?php echo $usuario['ubicacion']; ?>
                        </div>
                        <div class="col-lg-6 col-md-12">
                            <strong>Departamento: </strong><?php echo $departamentos_str; ?>
                        </div>
                    </div>
                </div>
                <!-- Fin datos usuario -->

                <!-- Cambiar contraseña -->
                <div class="reminders">
                    <div class="header">
                        <i class='bx bx-edit'></i>
                        <h3>Cambiar contraseña</h3>
                    </div>
                    <form action="<?= $base ?>/perfil" method="post" id="passwordForm">
                        <div class="form-group password-container">
                            <label for="current_password">Contraseña actual:</label>
                            <input type="password" id="current_password" name="current_password" class="form-control" required autocomplete="current-password">
                            <i id="eyeIconCurrent" class="bx bx-show" onclick="togglePasswordVisibilityCurrent()"></i>
                        </div>
                        <div class="form-group password-container">
                            <label for="password">Nueva contraseña:</label>
                            <input type="password" id="password" name="password" class="form-control" required oninput="checkPasswordStrength()" autocomplete="new-password">
                            <i id="eyeIcon" class="bx bx-show" onclick="togglePasswordVisibility()"></i>
                            <div id="passwordStrength" class="mt-2"></div>
                        </div>
                        <div class="form-group password-container">
                            <label for="password_repeat">Repetir contraseña:</label>
                            <input type="password" id="password_repeat" name="password_repeat" class="form-control" required autocomplete="new-password">
                            <i id="eyeIconRepeat" class="bx bx-show" onclick="togglePasswordVisibilityRepeat()"></i>
                        </div>
                        <small class="text-muted">Mínimo 8 dígitos, mayúscula, número y caracter especial.</small>
                        <button type="submit" name="cambiar_contrasena" class="btn" id="btnSubmit">Cambiar contraseña</button>

                    </form>
                </div>
                <!-- Fin cambiar contraseña-->
            </div>
        </main>
    </div>
    <?php include "layouts/scripts.php" ?>
    <script>
        document.getElementById('fotoInput').addEventListener('change', function() {
            document.getElementById('fotoForm').submit();
        });
    </script>
</body>

</html>