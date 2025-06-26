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

$query = "SELECT * FROM usuarios ORDER BY user";
$stmt = $conn->prepare($query);
$stmt->execute();
$usuarios = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_usuario'])) {
    if (in_array($rol, $id_rol)) {
        $id = $_POST['editar_id'];
        $user = $_POST['editar_user'];
        $nombre = $_POST['editar_nombre'];
        $apellidos = $_POST['editar_apellidos'];
        $email = $_POST['editar_email'];
        $dni = $_POST['editar_dni'];
        $extension = $_POST['editar_extension'];
        $movil = $_POST['editar_movil'];
        $puesto = $_POST['editar_puesto'];
        $oficina_obra = $_POST['editar_oficina_obra'];
        $ubicacion = $_POST['editar_ubicacion'];

        if (empty($id) || empty($nombre) || empty($apellidos) || empty($email)) {
            $_SESSION['error_message'] = 'El nombre y correo son campos obligatorios.';
            header("Location: $base/usuarios");
            exit;
        }

        // Procesar la foto si se ha subido una nueva
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            $foto = $_FILES['foto'];
            $extension_foto = pathinfo($foto['name'], PATHINFO_EXTENSION);
            $nombre_foto = $user; // O el nombre que prefieras para la foto
            $ruta_foto = "public/img/usuarios/";

            // Eliminar foto antigua si existe
            $extensiones = ['png', 'jpg', 'jpeg'];
            foreach ($extensiones as $ext) {
                $ruta_foto_existente = $ruta_foto . $nombre_foto . '.' . $ext;
                if (file_exists($ruta_foto_existente)) {
                    unlink($ruta_foto_existente);
                }
            }

            // Subir la nueva foto
            $ruta_foto_final = $ruta_foto . $nombre_foto . '.' . $extension_foto;
            $foto_bd = $nombre_foto . '.' . $extension_foto;

            if (move_uploaded_file($foto['tmp_name'], $ruta_foto_final)) {
                $query = "UPDATE usuarios SET foto = :foto WHERE id = :id_usuario";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':foto', $foto_bd);
                $stmt->bindParam(':id_usuario', $id);

                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "Foto actualizada con éxito.";
                } else {
                    $_SESSION['error_message'] = "Error al actualizar la foto.";
                }
            } else {
                $_SESSION['error_message'] = "Error al mover la foto subida.";
            }
        }

        // Actualizar los demás datos del usuario
        $query = "UPDATE usuarios SET user = :user ,nombre = :nombre, apellidos = :apellidos, email = :email, dni = :dni, extension = :extension, movil = :movil, puesto = :puesto, oficina_obra = :oficina_obra, ubicacion = :ubicacion WHERE id = :id_usuario";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':user', $user);
        $stmt->bindParam(':apellidos', $apellidos);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':dni', $dni);
        $stmt->bindParam(':extension', $extension);
        $stmt->bindParam(':movil', $movil);
        $stmt->bindParam(':puesto', $puesto);
        $stmt->bindParam(':oficina_obra', $oficina_obra);
        $stmt->bindParam(':ubicacion', $ubicacion);
        $stmt->bindParam(':id_usuario', $id);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Empleado/a editado con éxito.';
        } else {
            $_SESSION['error_message'] = 'Error al editar el usuario/a.';
        }
    } else {
        $_SESSION['error_message'] = 'No tiene permisos para realizar esta acción.';
    }
    header("Location: $base/usuarios");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id']) && (in_array($rol, $id_rol))) {
    $usuario_eliminar_id = intval($_GET['id']);

    $query = "SELECT foto FROM usuarios WHERE id = :usuario_eliminar_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':usuario_eliminar_id', $usuario_eliminar_id);
    $stmt->execute();
    $usuario = $stmt->fetch();

    $foto = $usuario['foto'];

    if (isset($foto) && file_exists("public/img/usuarios/" . $foto)) {
        unlink("public/img/usuarios/" . $foto);
    }

    $query = "DELETE FROM usuarios WHERE id = :usuario_eliminar_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':usuario_eliminar_id', $usuario_eliminar_id);
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Usuario eliminado con éxito.";
    } else {
        $_SESSION['error_message'] = "Error al eliminar al usuario.";
    }

    header("Location: $base/usuarios");
    exit;
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <?php include "layouts/head.php" ?>
    <title>Usuarios | Admin Dashboard</title>
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
                        <li><a href="" class="active">Lista de usuarios</a></li>
                        <?php if (in_array($rol, $id_rol)) { ?>
                            /
                            <li><a href="<?= $base ?>/usuarios/anadir">Añadir usuario</a></li>
                        <?php } ?>
                    </ul>
                </div>
            </div>

            <div class="bottom-data pb-4">
                <div class="orders">
                    <div class="header">
                        <i class='bx bx-receipt'></i>
                        <h3>Lista de usuarios</h3>
                    </div>
                    <table id="tabla" class="display">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Usuario</th>
                                <th>Nombre</th>
                                <th>Correo</th>
                                <th>Extensión</th>
                                <th>Móvil</th>
                                <th>Puesto</th>
                                <th>Tipo</th>
                                <th>Ubicación</th>
                                <th></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $listado_usuario) : ?>
                                <tr>
                                    <td>
                                        <?php if (!$listado_usuario['foto']) { ?>
                                            <img src="<?= $base ?>/public/img/perfil.png">
                                        <?php } else { ?>
                                            <img src="<?= $base ?>/public/img/usuarios/<?php echo $listado_usuario['foto']; ?>">
                                        <?php } ?>
                                    </td>
                                    <td><?php echo $listado_usuario['user']; ?></td>
                                    <td><?php echo $listado_usuario['nombre'] . " " . $listado_usuario['apellidos']; ?></td>
                                    <td><?php echo $listado_usuario['email']; ?></td>
                                    <td><?php echo $listado_usuario['extension']; ?></td>
                                    <td><?php echo $listado_usuario['movil']; ?></td>
                                    <td><?php echo $listado_usuario['puesto']; ?></td>
                                    <td><?php echo $listado_usuario['oficina_obra']; ?></td>
                                    <td><?php echo $listado_usuario['ubicacion']; ?></td>
                                    <td><?php if (in_array($rol, $id_rol)) { ?>
                                            <i onclick="editarEmpleado(<?php echo $listado_usuario['id']; ?>, '<?php echo $listado_usuario['user']; ?>', 
                        '<?php echo $listado_usuario['nombre']; ?>', '<?php echo $listado_usuario['apellidos']; ?>', 
                        '<?php echo $listado_usuario['email']; ?>','<?php echo $listado_usuario['dni']; ?>', 
                        '<?php echo $listado_usuario['extension']; ?>', '<?php echo $listado_usuario['movil']; ?>', 
                        '<?php echo $listado_usuario['puesto']; ?>', '<?php echo $listado_usuario['oficina_obra']; ?>', 
                        '<?php echo $listado_usuario['ubicacion']; ?>')" class='bx bx-edit'></i>
                                        <?php } ?>
                                    </td>
                                    <td><?php if (in_array($rol, $id_rol)) { ?><a onclick="return confirmarEliminacion();" href="<?= $base ?>/usuarios/delete/<?php echo $listado_usuario['id']; ?>"><i class='bx bx-trash'></i></a><?php } ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <?php if (in_array($rol, $id_rol)) { ?>

        <!-- Modal para editar usuario -->
        <div style="z-index:9999;" class="modal fade" id="editarEmpleadoModal" tabindex="-1" role="dialog" aria-labelledby="editarEmpleadoModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editarEmpleadoModalLabel">Editar usuario: <span id="editarEmpleadoUser"></span></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form class="row" id="editarEmpleadoForm" action="<?= $base ?>/usuarios" method="post" enctype="multipart/form-data">
                            <div class="form-group col-12">
                                <input type="text" id="editar_id" name="editar_id" class="form-control" hidden readonly>
                            </div>
                            <div class="form-group col-12">
                                <input type="text" id="editar_user" name="editar_user" class="form-control" hidden readonly>
                            </div>
                            <div class="form-group col-6">
                                <label for="editar_nombre">Nombre *</label>
                                <input type="text" id="editar_nombre" name="editar_nombre" class="form-control" required>
                            </div>
                            <div class="form-group col-6">
                                <label for="editar_apellidos">Apellidos *</label>
                                <input type="text" id="editar_apellidos" name="editar_apellidos" class="form-control" required>
                            </div>
                            <div class="form-group col-12">
                                <label for="editar_email">Correo electrónico *</label>
                                <input type="text" id="editar_email" name="editar_email" class="form-control" required>
                            </div>
                            <div class="form-group col-6">
                                <label for="editar_dni">Dni</label>
                                <input type="text" id="editar_dni" name="editar_dni" class="form-control">
                            </div>
                            <div class="form-group col-6">
                                <label for="editar_extension">Extensión</label>
                                <input type="text" id="editar_extension" name="editar_extension" class="form-control">
                            </div>
                            <div class="form-group col-6">
                                <label for="editar_movil">Móvil</label>
                                <input type="text" id="editar_movil" name="editar_movil" class="form-control">
                            </div>
                            <div class="form-group col-6">
                                <label for="editar_oficina_obra">Oficina u obra *</label>
                                <select id="editar_oficina_obra" name="editar_oficina_obra" class="form-control" required>
                                    <option value="Oficina">Oficina</option>
                                    <option value="Obra">Obra</option>
                                </select>
                            </div>
                            <div class="form-group col-12">
                                <label for="editar_puesto">Puesto</label>
                                <input type="text" id="editar_puesto" name="editar_puesto" class="form-control">
                            </div>
                            <div class="form-group col-12">
                                <label for="editar_ubicacion">Ubicación *</label>
                                <select name="editar_ubicacion" id="editar_ubicacion" class="form-control" required>
                                    <option value="Asturias">Asturias</option>
                                    <option value="Barcelona">Barcelona</option>
                                    <option value="Madrid">Madrid</option>
                                    <option value="Valencia">Valencia</option>
                                </select>
                            </div>
                            <div class="form-group col-12">
                                <label for="editar_foto">Cambiar foto</label>
                                <label for="foto" class="custom-file-upload m-0">
                                    <input type="file" id="foto" name="foto" class="file-input">
                                    <span id="file-name">Seleccionar archivo</span>
                                </label>
                            </div>
                            <div class="form-group col-12">
                                <button type="submit" name="editar_usuario" class="btn" id="btnSubmit">Guardar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
    <?php include "layouts/scripts.php" ?>
    <script>
        function confirmarEliminacion() {
            return confirm("¿Estás seguro de eliminar este usuario?");
        }

        function editarEmpleado(id, user, nombre, apellidos, email, dni, extension, movil, puesto, oficina_obra, ubicacion) {

            document.getElementById('editar_id').value = id;
            document.getElementById('editar_user').value = user;
            document.getElementById('editar_nombre').value = nombre;
            document.getElementById('editar_apellidos').value = apellidos;
            document.getElementById('editar_email').value = email;
            document.getElementById('editar_dni').value = dni;
            document.getElementById('editar_extension').value = extension;
            document.getElementById('editar_movil').value = movil;
            document.getElementById('editar_puesto').value = puesto;
            document.getElementById('editar_oficina_obra').value = oficina_obra;
            document.getElementById('editar_ubicacion').value = ubicacion;

            document.getElementById('editarEmpleadoUser').innerText = user;

            $('#editarEmpleadoModal').modal('show');
        }

        $(document).ready(function() {
            var table = $('#tabla').DataTable({
                "pageLength": 5,
                lengthMenu: [
                    [5, 10, 50, -1],
                    [5, 10, 50, '+100']
                ],
                "ordering": true,
                "searching": true,
                "order": [
                    [1, 'asc']
                ],
                "columnDefs": [{
                    "targets": [0, 9, 10],
                    "orderable": false
                }],
                "language": {
                    "lengthMenu": "Mostrar _MENU_ registros por página",
                    "zeroRecords": "No se encontraron resultados",
                    "info": "Mostrando página _PAGE_ de _PAGES_",
                    "infoEmpty": "No hay registros disponibles",
                    "infoFiltered": "(filtrado de _MAX_ registros totales)",
                    "search": "Buscar:",
                    "paginate": {
                        "first": "Primero",
                        "last": "Último",
                        "next": "Siguiente",
                        "previous": "Anterior"
                    },
                    "loadingRecords": "Cargando...",
                    "processing": "Procesando...",
                    "emptyTable": "No hay datos disponibles en la tabla",
                    "infoThousands": ","
                }
            });
        });

        document.getElementById('foto').addEventListener('change', function() {
            var fileName = this.value.split('\\').pop();
            document.getElementById('file-name').textContent = fileName || 'Seleccionar archivo';
        });
    </script>


</body>

</html>