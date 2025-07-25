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
$rol = 1;

if (isset($_GET['id'])) {
    $id_rol_get = intval($_GET['id']);
} else {
    header("Location: $base/roles");
    exit;
}

if (!in_array($rol, $id_rol)) {
    $_SESSION['error_message'] = 'No tienes permisos';
    header("Location: $base/roles");
    exit;
}

// Obtener los datos del rol
$query = "SELECT * FROM roles WHERE id = :id_rol";
$stmt = $conn->prepare($query);
$stmt->bindParam(':id_rol', $id_rol_get);
$stmt->execute();
$datos_rol = $stmt->fetch();

// Obtener los usuarios que pertenecen al rol específico
$query = "
    SELECT u.* 
    FROM usuarios u
    INNER JOIN usuarios_roles ur ON u.id = ur.id_usuario
    WHERE ur.id_rol = :id_rol
    ORDER BY u.user";
$stmt = $conn->prepare($query);
$stmt->bindParam(':id_rol', $id_rol_get);
$stmt->execute();
$usuarios = $stmt->fetchAll();

// Proceso de quitar usuario de rol
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id_usuario']) && ((in_array($rol, $id_rol)))) {
    $usuario_eliminar_id = intval($_GET['id_usuario']);

    $query = "DELETE FROM usuarios_roles WHERE id_usuario = :usuario_eliminar_id AND id_rol = :id_rol ";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':usuario_eliminar_id', $usuario_eliminar_id);
    $stmt->bindParam(':id_rol', $id_rol_get);
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Empleado/a eliminado del rol con éxito.";
    } else {
        $_SESSION['error_message'] = "Error al eliminar el usuario/a del rol.";
    }

    header("Location: $base/roles/$id_rol_get");

    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['añadir_usuario']) && ((in_array($rol, $id_rol)))) {
    $usuarios_seleccionados = $_POST['usuarios'];

    foreach ($usuarios_seleccionados as $anadir_usuario_id) {
        // Verificar si el usuario ya está en el rol
        $query = "SELECT COUNT(*) AS count FROM usuarios_roles WHERE id_rol = :id_rol AND id_usuario = :id_usuario";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id_rol', $id_rol_get, PDO::PARAM_INT);
        $stmt->bindParam(':id_usuario', $anadir_usuario_id, PDO::PARAM_INT);
        $stmt->execute();
        $resultado_anadir_usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        // Si el usuario no esta en el rol añadirlo
        if ($resultado_anadir_usuario['count'] == 0) {
            $query_insert = "INSERT INTO usuarios_roles (id_rol, id_usuario) VALUES  (:id_rol, :id_usuario)";
            $stmt_insert = $conn->prepare($query_insert);
            $stmt_insert->bindParam(':id_rol', $id_rol_get, PDO::PARAM_INT);
            $stmt_insert->bindParam(':id_usuario', $anadir_usuario_id, PDO::PARAM_INT);
            $stmt_insert->execute();

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Empleado añadido al rol con éxito.";
            } else {
                $_SESSION['error_message'] = "Error al añadir usuario al rol.";
            }
        }
    }
    header("Location: $base/roles/$id_rol_get");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <?php include "layouts/head.php" ?>
    <title><?php echo $datos_rol['nombre']; ?> | Admin Dashboard</title>
</head>

<body>
    <?php include('layouts/sidebar.php'); ?>
    <!-- Main Content -->
    <div class="content">
        <?php include('layouts/navbar.php'); ?>
        <main>
            <div class="header">
                <div class="left">
                    <h1>Roles</h1>
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
                        <li><a href="<?= $base ?>/roles">Roles</a></li>
                        /
                        <li><a href="" class="active"><?php echo $datos_rol['nombre']; ?></a></li>
                    </ul>
                </div>
            </div>

            <div class="bottom-data pb-4">
                <div class="orders">
                    <div class="header">
                        <i class='bx bx-buildings'></i>
                        <h3><?php echo $datos_rol['nombre']; ?></h3>
                        <i onclick="añadirEmpleado()" style="cursor: pointer;" class='bx bx-plus'></i>
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
                                    <td>
                                        <a
                                            onclick="return quitarEmpleado();"
                                            href="<?= $base ?>/roles/<?= $id_rol_get ?>/<?= $listado_usuario['id'] ?>">
                                            <i class='bx bx-minus-circle'></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <div style="z-index: 9999" class="modal fade" id="añadirEmpleadoModal" tabindex="-1" role="dialog" aria-labelledby="añadirEmpleadoModal" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="añadirEmpleadoLabel">Añadir usuarios</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form class="pt-0" id="crearPymaForm" action="<?= $base ?>/roles/<?= $id_rol_get ?>" method="post">
                        <div class="form-group">
                            <input class='mb-1 pl-3 p-1' style="border-radius: 30px;" type="text" id="userSearchInput" onkeyup="searchUsers()" placeholder="Buscar usuario...">
                            <div id="usuariosContainer" class="usuarios-container">
                                <?php
                                $query_usuarios = "
                                SELECT u.* 
                                FROM usuarios u
                                LEFT JOIN usuarios_roles ur 
                                ON u.id = ur.id_usuario AND ur.id_rol = :id_rol
                                WHERE ur.id_usuario IS NULL
                                ORDER BY u.user";
                                $stmt_usuarios = $conn->prepare($query_usuarios);
                                $stmt_usuarios->bindParam(':id_rol', $id_rol_get, PDO::PARAM_INT);
                                $stmt_usuarios->execute();
                                while ($row_usuario = $stmt_usuarios->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<div class='form-check'>";
                                    echo "<input class='form-check-input' type='checkbox' name='usuarios[]' value='" . $row_usuario['id'] . "' id='usuario" . $row_usuario['id'] . "'>";
                                    echo "<label class='form-check-label' for='usuario" . $row_usuario['id'] . "'>" . $row_usuario['user'] . " - " . $row_usuario['nombre'] . " " . $row_usuario['apellidos'] . "</label>";
                                    echo "</div>";
                                }
                                ?>
                            </div>
                        </div>
                        <button type="submit" name="añadir_usuario" class="btn" id="btnSubmit">Añadir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php include "layouts/scripts.php" ?>
    <script>
        function quitarEmpleado() {
            return confirm("¿Estás seguro de eliminar este usuario/a del rol?");
        }

        function añadirEmpleado() {
            $('#añadirEmpleadoModal').modal('show');
        }

        function searchUsers() {
            var input, filter, container, divs, labels, i, txtValue;
            input = document.getElementById('userSearchInput');
            filter = input.value.toUpperCase();
            container = document.getElementById('usuariosContainer');
            divs = container.getElementsByTagName('div');

            for (i = 0; i < divs.length; i++) {
                labels = divs[i].getElementsByTagName('label')[0];
                if (labels) {
                    txtValue = labels.textContent || labels.innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        divs[i].style.display = "";
                    } else {
                        divs[i].style.display = "none";
                    }
                }
            }
        }

        $(document).ready(function() {
            var table = $('#tabla').DataTable({
                "pageLength": -1,
                lengthMenu: [
                    [3, 5, 10, -1],
                    [3, 5, 10, '+10']
                ],
                "ordering": true,
                "searching": true,
                "order": [
                    [1, 'asc']
                ],
                "columnDefs": [{
                    "targets": [0, 9],
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
    </script>
</body>

</html>