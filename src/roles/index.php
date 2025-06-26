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

if (!in_array($rol, $id_rol)) {
    $_SESSION['error_message'] = 'No tienes permisos';
    header("Location: $base/dashboard");
    exit;
}

// Obtener todos los roles
$query = "SELECT * FROM roles ORDER BY id";
$stmt = $conn->prepare($query);
$stmt->execute();
$roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener todos los usuarios
$query = "SELECT * FROM usuarios ORDER BY user";
$stmt = $conn->prepare($query);
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <?php include "layouts/head.php" ?>
    <title>Roles | Admin Dashboard</title>
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
                        <li><a class="active" href="">Roles</a></li>
                    </ul>
                </div>
            </div>

            <div class="bottom-data pb-4">
                <div class="orders">
                    <div class="header">
                        <i class='bx bx-receipt'></i>
                        <h3>Lista de roles</h3>
                    </div>
                    <table id="tabla" class="display">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Rol</th>
                                <th>Descripción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($roles as $listado_roles) : ?>
                                <tr class="ver_tr" onclick="verRol(<?php echo $listado_roles['id']; ?>)">
                                    <td><?php echo $listado_roles['id']; ?></td>
                                    <td><?php echo $listado_roles['nombre']; ?></td>
                                    <td><?php echo $listado_roles['descripcion']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </main>
    </div>
    <?php include "layouts/scripts.php" ?>
    <script>
        function verRol(id) {
            window.location.href = BASE_URL + '/roles/' + id;
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
                    [0, 'asc']
                ],
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