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
$rol = 2;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!in_array($rol, $id_rol)) {
    $_SESSION['error_message'] = 'No tienes permisos';
    header("Location: $base/usuarios");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['anadir_usuario']) && in_array($rol, $id_rol)) {
    try {
        // Variables de usuario 
        $user = $_POST['user'];
        $nombre = $_POST['nombre'];
        $apellidos = $_POST['apellidos'];
        $email = $_POST['email'];
        $dni = $_POST['dni'] ?? NULL;
        $movil = $_POST['movil'] ?? NULL;
        $puesto = $_POST['puesto'] ?? NULL;
        $oficina_obra = $_POST['oficina_obra'];
        $ubicacion = $_POST['ubicacion'];
        $extension = $_POST['extension'] ?? NULL;
        $departamento_id = $_POST['departamento'];

        // Comprobar si el usuario o email ya existe
        $query = "SELECT id FROM usuarios WHERE user = :user OR email = :email";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':user', $user);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $existing_user = $stmt->fetch();

        if ($existing_user) {
            $_SESSION['error_message'] = "Este usuario o correo ya existe.";
            header("Location: $base/usuarios/anadir");
            exit;
        }

        // Subida de la foto
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == UPLOAD_ERR_OK) {
            $foto = $_FILES['foto'];
            $extension_foto = pathinfo($foto['name'], PATHINFO_EXTENSION);
            $nombre_foto = $user . '.' . $extension_foto;
            $ruta_destino = "public/img/usuarios/$nombre_foto";

            if (!move_uploaded_file($foto['tmp_name'], $ruta_destino)) {
                $_SESSION['error_message'] = "Error al subir la foto.";
                header("Location: $base/usuarios/anadir");
                exit;
            }
        } else {
            $nombre_foto = NULL;
        }

        // Generar contraseña
        function generarContrasena()
        {
            $caracteres = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$&_";
            $longitud = 10;
            $contrasena = "";

            for ($i = 0; $i < $longitud; $i++) {
                $indice = mt_rand(0, strlen($caracteres) - 1);
                $contrasena .= $caracteres[$indice];
            }

            return $contrasena;
        }

        $contrasena = generarContrasena();
        $hashed_password = password_hash($contrasena, PASSWORD_DEFAULT);

        // Insertar el usuario en la base de datos
        $query = "INSERT INTO usuarios (user, nombre, apellidos, email, dni, password, foto, extension, movil, puesto, oficina_obra, ubicacion) VALUES (:user, :nombre, :apellidos, :email, :dni, :password, :foto, :extension, :movil, :puesto, :oficina_obra, :ubicacion)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':user', $user);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':apellidos', $apellidos);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':dni', $dni);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':foto', $nombre_foto);
        $stmt->bindParam(':extension', $extension);
        $stmt->bindParam(':movil', $movil);
        $stmt->bindParam(':puesto', $puesto);
        $stmt->bindParam(':oficina_obra', $oficina_obra);
        $stmt->bindParam(':ubicacion', $ubicacion);
        $stmt->execute();

        // Obtener el último ID de usuario insertado
        $id_usuario = $conn->lastInsertId();

        // Insertar el usuario en la tabla usuarios_departamentos
        $query = "INSERT INTO usuarios_departamentos (id_usuario, id_departamento) VALUES (:id_usuario, :id_departamento)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id_usuario', $id_usuario);
        $stmt->bindParam(':id_departamento', $departamento_id);
        $stmt->execute();

        // Configuración del servidor SMTP y envío de correo
        $mail = new PHPMailer(true);

        try {
            include 'config/mail.php';
            configureMail($mail);
            $mail->isHTML(true);
            $mail->addAddress($email);
            $mail->Subject = 'Creación de usuario | Admin Dashboard';
            $mail->CharSet = 'UTF-8';
            ob_start();
            include 'layouts/correos/nuevo_usuario.php';
            $mail->Body = ob_get_clean();

            $mail->send();
            $_SESSION['success_message'] = "Empleado añadido con éxito.";
        } catch (Exception $e) {
            $_SESSION['error_message'] = "Error al enviar el correo: " . $e->getMessage();
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Error al añadir el usuario: " . $e->getMessage();
    }

    header("Location: $base/usuarios/anadir");
    exit;
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <?php include "layouts/head.php" ?>
    <title>Añadir usuario | Admin Dashboard</title>
</head>

<body>
    <?php include('layouts/sidebar.php'); ?>
    <!-- Main Content -->
    <div class="content">
        <?php include('layouts/navbar.php'); ?>
        <main>
            <div class="header">
                <div class="left">
                    <h1>Usuarios</h1>
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

            <div class="header">
                <div class="left">
                    <ul class="breadcrumb">
                        <li><a href="<?= $base ?>/usuarios">Lista de usuarios</a></li>
                        /
                        <li><a href="" class="active">Añadir usuario</a></li>
                    </ul>
                </div>
            </div>

            <div class="bottom-data pb-4">
                <!-- Reminders -->
                <div class="reminders ">
                    <div class="header">
                        <i class='bx bx-user-plus'></i>
                        <h3>Añadir usuario</h3>
                    </div>
                    <form action="<?= $base ?>/usuarios/anadir" method="post" id="passwordForm" enctype="multipart/form-data">
                        <div class="row">

                            <div class="col-lg-4 col-md-5 col-sm-12">
                                <div class="form-group">
                                    <label for="user">Usuario *</label>
                                    <input type="text" name="user" class="form-control" placeholder="Ejemplo: PGL3" required>
                                </div>
                            </div>
                            <div class="col-lg-8 col-md-7 col-sm-12">
                                <div class="form-group">
                                    <label for="email">Correo electrónico *</label>
                                    <input type="email" name="email" placeholder="Ejemplo: correo@email.es" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-lg-5 col-md-5 col-sm-12">
                                <div class="form-group">
                                    <label for="nombre">Nombre *</label>
                                    <input type="text" name="nombre" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-lg-7 col-md-7 col-sm-12">
                                <div class="form-group">
                                    <label for="apellidos">Apellidos *</label>
                                    <input type="text" name="apellidos" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6 col-sm-12">
                                <div class="form-group">
                                    <label for="dni">Dni</label>
                                    <input type="text" name="dni" class="form-control">
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6 col-sm-12">
                                <div class="form-group">
                                    <label for="extension">Extensión</label>
                                    <input type="text" name="extension" class="form-control" placeholder="Ejemplo: 1683">
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6 col-sm-12">
                                <div class="form-group">
                                    <label for="movil">Móvil</label>
                                    <input type="text" name="movil" class="form-control">
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6 col-sm-12">
                                <div class="form-group">
                                    <label for="oficina_obra">Oficina u obra *</label>
                                    <select name="oficina_obra" class="form-control" required>
                                        <option value="" disabled selected>Seleccionar...</option>
                                        <option value="Oficina">Oficina</option>
                                        <option value="Obra">Obra</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-12 col-sm-12">
                                <div class="form-group">
                                    <label for="puesto">Puesto</label>
                                    <input type="text" name="puesto" class="form-control" placeholder="Ejemplo: Oficial de Mantenimiento">
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-12 col-sm-12">
                                <div class="form-group">
                                    <label for="ubicacion">Ubicación *</label>
                                    <select name="ubicacion" class="form-control" required>
                                        <option value="" disabled selected>Seleccionar...</option>
                                        <option value="Asturias">Asturias</option>
                                        <option value="Barcelona">Barcelona</option>
                                        <option value="Madrid">Madrid</option>
                                        <option value="Valencia">Valencia</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-12 col-sm-12">
                                <div class="form-group">
                                    <label for="departamento">Departamento *</label>
                                    <select name="departamento" class="form-control" required>
                                        <option value="" disabled selected>Seleccionar...</option> <!-- Placeholder option -->
                                        <?php
                                        // Obtener los departamentos de la base de datos
                                        $query = "SELECT id, nombre FROM departamentos";
                                        $stmt = $conn->prepare($query);
                                        $stmt->execute();
                                        $departamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                        foreach ($departamentos as $departamento) {
                                            echo '<option value="' . $departamento['id'] . '">' . $departamento['nombre'] . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-12 col-md-12 col-sm-12">
                                <div class="form-group">
                                    <label for="foto">Añadir foto</label>
                                    <input type="file" name="foto" class="input-seleccionar" id="foto">
                                </div>
                            </div>
                        </div>
                        <small class="text-muted">Se enviará un correo automáticamente al usuario con sus credenciales.</small>
                        <button type="submit" name="anadir_usuario" id="btnSubmit">Guardar</button>
                    </form>
                </div>
                <!-- End of Reminders-->
            </div>

        </main>

    </div>
    <?php include "layouts/scripts.php" ?>
</body>

</html>