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
$rol = 3;

// Obtener todos los departamentos
$query = "SELECT * FROM departamentos ORDER BY id";
$stmt = $conn->prepare($query);
$stmt->execute();
$departamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener todos los usuarios
$query = "SELECT * FROM usuarios ORDER BY user";
$stmt = $conn->prepare($query);
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['anadir_departamento'])) {
    if (in_array($rol, $id_rol)) {
        $nombre = $_POST['nombre'];
        $jefe = $_POST['jefe'];
        $padre_id = $_POST['padre_id'];

        $query = "INSERT INTO departamentos (nombre, jefe, padre_id) VALUES (:nombre, :jefe, :padre_id)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':jefe', $jefe);
        $stmt->bindParam(':padre_id', $padre_id);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Departamento añadido con éxito.";
        } else {
            $_SESSION['error_message'] = "Error al añadir el departamento.";
        }
    } else {
        $_SESSION['error_message'] = "No tiene permiso para realizar esta acción.";
    }
    header("Location: $base/departamentos");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_departamento'])) {
    if (in_array($rol, $id_rol)) {
        $id = $_POST['editar_id'];
        $nombre = $_POST['editar_nombre'];
        $jefe = $_POST['editar_jefe'];
        $padre_id = $_POST['editar_padre_id'];

        $query = "UPDATE departamentos SET nombre = :nombre, jefe = :jefe, padre_id = :padre_id WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':jefe', $jefe);
        $stmt->bindParam(':padre_id', $padre_id);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Departamento editado con éxito.';
        } else {
            $_SESSION['error_message'] = 'Error al editar el departamento.';
        }
    } else {
        $_SESSION['error_message'] = "No tiene permiso para realizar esta acción.";
    }
    header("Location: $base/departamentos");
    exit;
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <?php include "layouts/head.php" ?>
    <title>Departamentos | Admin Dashboard</title>
</head>

<body>
    <?php include('layouts/sidebar.php'); ?>
    <!-- Main Content -->
    <div class="content">
        <?php include('layouts/navbar.php'); ?>
        <main>
            <div class="header">
                <div class="left">
                    <h1>Departamentos</h1>
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
                        <li><a class="active" href="">Departamentos</a></li>
                    </ul>
                </div>
            </div>

            <div class="bottom-data pb-4">
                <div class="orders">
                    <div class="header">
                        <i class='bx bx-receipt'></i>
                        <h3>Lista de departamentos</h3>
                    </div>
                    <table id="tabla" class="display">
                        <thead>
                            <tr>
                                <th>Departamento</th>
                                <th></th>
                                <th>Responsable</th>
                                <th>Dpto. Superior</th>
                                <th></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($departamentos as $listado_departamento) : ?>
                                <tr class="ver_tr" onclick="verDepartamento(<?php echo $listado_departamento['id']; ?>)">
                                    <td><?php echo $listado_departamento['nombre']; ?></td>
                                    <td><?php
                                        $jefe = array_filter($usuarios, function ($usuario) use ($listado_departamento) {
                                            return $usuario['id'] == $listado_departamento['jefe'];
                                        });
                                        if (!empty($jefe)) {
                                            $jefe = array_values($jefe)[0]; ?>
                                            <img src="<?= $base ?>/public/img/usuarios/<?= $jefe['foto'] ?>">
                                        <?php
                                        } else {
                                            echo '';
                                        }
                                        ?>
                                    </td>
                                    <td><?php
                                        $jefe = array_filter($usuarios, function ($usuario) use ($listado_departamento) {
                                            return $usuario['id'] == $listado_departamento['jefe'];
                                        });
                                        if (!empty($jefe)) {
                                            $jefe = array_values($jefe)[0];
                                            echo $jefe['nombre'] . ' ' . $jefe['apellidos'];
                                        } else {
                                            echo '';
                                        }
                                        ?></td>
                                    <td><?php
                                        $padre = array_filter($departamentos, function ($departamento) use ($listado_departamento) {
                                            return $departamento['id'] == $listado_departamento['padre_id'];
                                        });
                                        if (!empty($padre)) {
                                            $padre = array_values($padre)[0];
                                            echo $padre['nombre'];
                                        } else {
                                            echo '';
                                        }
                                        ?></td>
                                    <td>
                                        <?php if ($listado_departamento['id'] != 1 && (in_array($rol, $id_rol))) { ?>
                                            <i onclick="editarDepartamento(<?php echo $listado_departamento['id']; ?>, '<?php echo $listado_departamento['nombre']; ?>', 
                                            '<?php echo $listado_departamento['jefe']; ?>', '<?php echo $listado_departamento['padre_id']; ?>')" class='bx bx-edit'></i>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <?php if ($listado_departamento['id'] != 1 && (in_array($rol, $id_rol))) { ?>
                                            <a onclick="return confirmarEliminacion();" href="<?= $base ?>/departamentos/delete/<?php echo $listado_departamento['id']; ?>">
                                                <i class='bx bx-trash'></i>
                                            </a>
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="reminders">
                    <div class="header">
                        <i class='bx bx-buildings'></i>
                        <h3>Nuevo departamento</h3>
                    </div>
                    <form action="<?= $base ?>/departamentos" method="post" id="passwordForm">
                        <div class="form-group password-container">
                            <label for="nombre">Departamento o línea de negocio *</label>
                            <input type="text" id="nombre" name="nombre" class="form-control" required>
                        </div>
                        <div class="form-group password-container">
                            <label for="jefe">Responsable *</label>
                            <select id="jefe" name="jefe" class="form-control" required>
                                <?php foreach ($usuarios as $usuario) { ?>
                                    <option value="<?php echo $usuario['id']; ?>" <?php echo ($usuario['id'] == 6) ? 'selected' : ''; ?>><?php echo $usuario['user'] . ' - ' . $usuario['nombre'] . ' ' . $usuario['apellidos']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="form-group password-container">
                            <label for="padre_id">Departamento o línea de negocio superior *</label>
                            <select id="padre_id" name="padre_id" class="form-control">
                                <?php foreach ($departamentos as $departamento) { ?>
                                    <option value="<?php echo $departamento['id']; ?>" <?php echo ($departamento['id'] == 1) ? 'selected' : ''; ?>><?php echo $departamento['nombre']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <button type="submit" name="anadir_departamento" id="btnSubmit">Guardar</button>
                    </form>
                </div>
            </div>
        </main>
    </div>


    <!-- Modal para editar departamento -->
    <?php if (in_array($rol, $id_rol)) { ?>
        <div style="z-index:9999;" class="modal fade" id="editarDepartamentoModal" tabindex="-1" role="dialog" aria-labelledby="editarDepartamentoModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editarDepartamentoModalLabel">Editar departamento</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="editarDepartamentoForm" action="<?= $base ?>/departamentos" method="post">
                            <div class="form-group">
                                <input type="text" id="editar_id" name="editar_id" class="form-control" hidden readonly>
                            </div>
                            <div class="form-group">
                                <label for="editar_nombre">Departamento o línea de negocio *</label>
                                <input type="text" id="editar_nombre" name="editar_nombre" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="editar_jefe">Responsable *</label>
                                <select id="editar_jefe" name="editar_jefe" class="form-control" required>
                                    <?php foreach ($usuarios as $usuario) { ?>
                                        <option value="<?php echo $usuario['id']; ?>"><?php echo $usuario['user'] . ' - ' . $usuario['nombre'] . ' ' . $usuario['apellidos']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="editar_padre_id">Departamento o línea de negocio superior *</label>
                                <select id="editar_padre_id" name="editar_padre_id" class="form-control" required>
                                    <?php foreach ($departamentos as $departamento) { ?>
                                        <option value="<?php echo $departamento['id']; ?>"><?php echo $departamento['nombre']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <button type="submit" name="editar_departamento" class="btn" id="btnSubmit">Guardar</button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    <?php } ?>
    <?php include "layouts/scripts.php" ?>
    <script>
        function verDepartamento(id) {
            window.location.href = BASE_URL + '/departamentos/' + id;
        }

        function confirmarEliminacion() {
            event.stopPropagation();
            return confirm("¿Estás seguro de eliminar este departamento?");
        }

        function editarDepartamento(id, nombre, jefe, padre_id) {
            event.stopPropagation();
            document.getElementById('editar_id').value = id;
            document.getElementById('editar_nombre').value = nombre;
            document.getElementById('editar_jefe').value = jefe;
            document.getElementById('editar_padre_id').value = padre_id;

            $('#editarDepartamentoModal').modal('show');
        }

        $(document).ready(function() {
            var table = $('#tabla').DataTable({
                "pageLength": 3,
                lengthMenu: [
                    [3, 5, 10, -1],
                    [3, 5, 10, '+20']
                ],
                "ordering": true,
                "searching": true,
                "order": [
                    [3, 'asc']
                ],
                "columnDefs": [{
                    "targets": [0, 4, 5],
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