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
$rol = 4;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Obtener todos los documentos con la foto del usuario
$query = "SELECT documentos.*, usuarios.foto as foto_usuario FROM documentos 
          LEFT JOIN usuarios ON documentos.id_usuario = usuarios.id 
          ORDER BY documentos.id";
$stmt = $conn->prepare($query);
$stmt->execute();
$documentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

function getDocumentType($extension)
{
    $image_extensions = ['png', 'jpg', 'jpeg'];
    $video_extensions = ['mp4', 'avi', 'mkv'];
    $excel_extensions = ['xls', 'xlsx'];
    if (in_array($extension, $image_extensions)) {
        return 'imagen';
    } elseif ($extension == 'pdf') {
        return 'pdf';
    } elseif ($extension == 'apk') {
        return 'apk';
    } elseif (in_array($extension, $video_extensions)) {
        return 'video';
    } elseif (in_array($extension, $excel_extensions)) {
        return 'excel';
    } else {
        return 'otro';
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['anadir_documento'])) {
    if (in_array($rol, $id_rol)) {
        $nombre = $_POST['nombre'];
        $descripcion = $_POST['descripcion'];
        $enviar_notificacion = isset($_POST['enviar_notificacion']);

        if (isset($_FILES['archivo'])) {
            $archivo_nombre = $_FILES['archivo']['name'];
            $archivo_tipo = $_FILES['archivo']['type'];
            $archivo_temporal = $_FILES['archivo']['tmp_name'];
            $archivo_tamano = $_FILES['archivo']['size'];

            $archivo_extension = strtolower(pathinfo($archivo_nombre, PATHINFO_EXTENSION));
            $nombre_archivo_final = $nombre . "." . $archivo_extension;

            $docs_url = realpath($_ENV['DOCS_URL']);
            $ruta_archivo = "$docs_url/generales/" . $nombre_archivo_final;
            if (file_exists($ruta_archivo)) {
                $_SESSION['error_message'] = "El documento ya existe.";
                header("Location: $base/documentos");
                exit;
            }

            if (move_uploaded_file($archivo_temporal, $ruta_archivo)) {
                $tipo_documento = getDocumentType($archivo_extension);
                $query = "INSERT INTO documentos (nombre, descripcion, ruta, tipo, id_usuario) 
                          VALUES (:nombre, :descripcion, :ruta, :tipo, :id_usuario)";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':nombre', $nombre);
                $stmt->bindParam(':descripcion', $descripcion);
                $stmt->bindParam(':ruta', $nombre_archivo_final);
                $stmt->bindParam(':tipo', $tipo_documento);
                $stmt->bindParam(':id_usuario', $_SESSION['id_usuario']);

                if ($stmt->execute()) {
                    if ($enviar_notificacion) {
                        $query_notificacion = "INSERT INTO notificaciones (nombre, descripcion, fecha, hora, tipo, id_usuario)
                                               VALUES (:nombre, :descripcion, NOW(), NOW(), 'Documento nuevo', :id_usuario)";
                        $stmt_notificacion = $conn->prepare($query_notificacion);
                        $stmt_notificacion->bindParam(':nombre', $nombre);
                        $stmt_notificacion->bindParam(':descripcion', $descripcion);
                        $stmt_notificacion->bindParam(':id_usuario', $_SESSION['id_usuario']);
                        $stmt_notificacion->execute();
                        $id_notificacion = $conn->lastInsertId();

                        $query_usuarios = "SELECT id FROM usuarios";
                        $stmt_usuarios = $conn->prepare($query_usuarios);
                        $stmt_usuarios->execute();
                        $usuarios = $stmt_usuarios->fetchAll(PDO::FETCH_ASSOC);

                        foreach ($usuarios as $usuario) {
                            $query_usuario_notificacion = "INSERT INTO usuarios_notificaciones (id_usuario, id_notificacion)
                                                           VALUES (:id_usuario, :id_notificacion)";
                            $stmt_usuario_notificacion = $conn->prepare($query_usuario_notificacion);
                            $stmt_usuario_notificacion->bindParam(':id_usuario', $usuario['id']);
                            $stmt_usuario_notificacion->bindParam(':id_notificacion', $id_notificacion);
                            $stmt_usuario_notificacion->execute();
                        }

                        // Aquí enviamos el correo electrónico
                        try {
                            $mail = new PHPMailer(true);
                            include 'config/mail.php';
                            configureMail($mail);
                            $mail->isHTML(true);
                            $mail->addAddress($_ENV['CORREO_NOTIFICACIONES']);
                            $mail->Subject = 'Nuevo documento | Admin Dashboard';
                            $mail->CharSet = 'UTF-8';
                            ob_start();
                            include 'layouts/correos/nuevo_documento.php';
                            $mail->Body = ob_get_clean();
                            $mail->send();
                        } catch (Exception $e) {
                            echo "Error al enviar el correo electrónico: {$mail->ErrorInfo}";
                        }
                    }
                }
                $_SESSION['success_message'] = "Documento subido con éxito.";
            } else {
                $_SESSION['error_message'] = "Error al mover el archivo.";
            }
        } else {
            $_SESSION['error_message'] = "Error al recibir el archivo.";
        }
    } else {
        $_SESSION['error_message'] = "No tiene permiso para realizar esta acción.";
    }
    header("Location: $base/documentos");
    exit;
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['editar_documento'])) {
    $docs_url = realpath($_ENV['DOCS_URL']);
    if (!$docs_url) {
        $_SESSION['error_message'] = "Error: la ruta de documentos no existe.";
        header("Location: $base/documentos");
        exit;
    }

    $id_documento = $_POST['editar_id'];
    $nombre = $_POST['editar_nombre'];
    $descripcion = $_POST['editar_descripcion'];
    $enviar_notificacion = isset($_POST['editar_enviar_notificacion']);

    // Obtener la información actual del documento
    $query = "SELECT ruta, tipo FROM documentos WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $id_documento, PDO::PARAM_INT);
    $stmt->execute();
    $documento_actual = $stmt->fetch(PDO::FETCH_ASSOC);

    $nombre_archivo_actual = $documento_actual['ruta'];
    $tipo_archivo_actual = $documento_actual['tipo'];
    $ruta_archivo_actual = "$docs_url/generales/$nombre_archivo_actual";

    // Si se subió un nuevo archivo
    if (isset($_FILES['editar_archivo']) && $_FILES['editar_archivo']['error'] == UPLOAD_ERR_OK) {
        $archivo_nombre = $_FILES['editar_archivo']['name'];
        $archivo_temporal = $_FILES['editar_archivo']['tmp_name'];
        $archivo_extension = strtolower(pathinfo($archivo_nombre, PATHINFO_EXTENSION));
        $nombre_archivo_final = $nombre . "." . $archivo_extension;
        $ruta_archivo_nuevo = "$docs_url/generales/$nombre_archivo_final";

        // Borrar el anterior si existe
        if (file_exists($ruta_archivo_actual)) {
            unlink($ruta_archivo_actual);
        }

        // Mover el nuevo
        if (!move_uploaded_file($archivo_temporal, $ruta_archivo_nuevo)) {
            $_SESSION['error_message'] = "Error al mover el archivo.";
            header("Location: $base/documentos");
            exit;
        }

        $tipo_documento = getDocumentType($archivo_extension);
        $query = "UPDATE documentos SET nombre = :nombre, descripcion = :descripcion, ruta = :ruta, tipo = :tipo WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':ruta', $nombre_archivo_final);
        $stmt->bindParam(':tipo', $tipo_documento);
        $stmt->bindParam(':id', $id_documento, PDO::PARAM_INT);
    } else {
        // Solo cambia el nombre del archivo existente
        $archivo_extension = pathinfo($ruta_archivo_actual, PATHINFO_EXTENSION);
        $nombre_sin_extension = pathinfo($ruta_archivo_actual, PATHINFO_FILENAME);

        if ($nombre !== $nombre_sin_extension) {
            $nombre_archivo_final = $nombre . "." . $archivo_extension;
            $ruta_archivo_nuevo = "$docs_url/generales/$nombre_archivo_final";

            if (!file_exists($ruta_archivo_nuevo)) {
                if (file_exists($ruta_archivo_actual) && rename($ruta_archivo_actual, $ruta_archivo_nuevo)) {
                    $query = "UPDATE documentos SET nombre = :nombre, descripcion = :descripcion, ruta = :ruta WHERE id = :id";
                    $stmt = $conn->prepare($query);
                    $stmt->bindParam(':nombre', $nombre);
                    $stmt->bindParam(':descripcion', $descripcion);
                    $stmt->bindParam(':ruta', $nombre_archivo_final);
                    $stmt->bindParam(':id', $id_documento, PDO::PARAM_INT);
                } else {
                    $_SESSION['error_message'] = "Error al renombrar el archivo.";
                    header("Location: $base/documentos");
                    exit;
                }
            } else {
                $_SESSION['error_message'] = "El documento con ese nombre ya existe.";
                header("Location: $base/documentos");
                exit;
            }
        } else {
            $query = "UPDATE documentos SET nombre = :nombre, descripcion = :descripcion WHERE id = :id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->bindParam(':id', $id_documento, PDO::PARAM_INT);
        }
    }

    // Ejecutar actualización
    if ($stmt->execute()) {
        if ($enviar_notificacion) {
            $query_notificacion = "INSERT INTO notificaciones (nombre, descripcion, fecha, hora, tipo, id_usuario)
                                   VALUES (:nombre, :descripcion, NOW(), NOW(), 'Documento actualizado', :id_usuario)";
            $stmt_notificacion = $conn->prepare($query_notificacion);
            $stmt_notificacion->bindParam(':nombre', $nombre);
            $stmt_notificacion->bindParam(':descripcion', $descripcion);
            $stmt_notificacion->bindParam(':id_usuario', $_SESSION['id_usuario']);
            $stmt_notificacion->execute();

            $id_notificacion = $conn->lastInsertId();

            $query_usuarios = "SELECT id FROM usuarios";
            $stmt_usuarios = $conn->prepare($query_usuarios);
            $stmt_usuarios->execute();
            $usuarios = $stmt_usuarios->fetchAll(PDO::FETCH_ASSOC);

            foreach ($usuarios as $usuario) {
                $query_usuario_notificacion = "INSERT INTO usuarios_notificaciones (id_usuario, id_notificacion)
                                               VALUES (:id_usuario, :id_notificacion)";
                $stmt_usuario_notificacion = $conn->prepare($query_usuario_notificacion);
                $stmt_usuario_notificacion->bindParam(':id_usuario', $usuario['id']);
                $stmt_usuario_notificacion->bindParam(':id_notificacion', $id_notificacion);
                $stmt_usuario_notificacion->execute();
            }

            try {
                $mail = new PHPMailer(true);
                include 'config/mail.php';
                configureMail($mail);
                $mail->isHTML(true);
                $mail->addAddress($_ENV['CORREO_NOTIFICACIONES']);
                $mail->Subject = 'Documento actualizado | Admin Dashboard';
                $mail->CharSet = 'UTF-8';
                ob_start();
                include 'layouts/correos/editar_documento.php';
                $mail->Body = ob_get_clean();
                $mail->send();
            } catch (Exception $e) {
                echo "Error al enviar el correo electrónico: {$mail->ErrorInfo}";
            }
        }

        $_SESSION['success_message'] = "Documento actualizado con éxito.";
    } else {
        $_SESSION['error_message'] = "Error al actualizar el documento.";
    }

    header("Location: $base/documentos");
    exit;
}


?>

<!DOCTYPE html>
<html lang="es">

<head>
    <?php include "layouts/head.php" ?>
    <title>Documentos | Admin Dashboard</title>
</head>

<body>
    <?php include('layouts/sidebar.php'); ?>
    <!-- Main Content -->
    <div class="content">
        <?php include('layouts/navbar.php'); ?>
        <main>
            <div class="header">
                <div class="left">
                    <h1>Documentos</h1>
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
                        <li><a class="active" href="">Documentos</a></li>
                    </ul>
                </div>
            </div>

            <div class="bottom-data pb-4">
                <div class="orders">
                    <div class="header">
                        <i class='bx bx-receipt'></i>
                        <h3>Lista de documentos</h3>
                    </div>
                    <table id="tabla" class="display">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Documento</th>
                                <th>Descripción</th>
                                <th>Fecha y hora</th>
                                <th></th>
                                <th></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($documentos as $listado_documento) : ?>
                                <tr class="ver_tr" onclick="descargarDocumento('<?php echo $listado_documento['ruta']; ?>')">
                                    <td>
                                        <?php
                                        switch ($listado_documento['tipo']) {
                                            case 'imagen':
                                                echo "<i class='bx bxs-file-image'></i>";
                                                break;
                                            case 'pdf':
                                                echo "<i class='bx bxs-file-pdf'></i>";
                                                break;
                                            case 'apk':
                                                echo "<i class='bx bx-mobile'></i>";
                                                break;
                                            case 'video':
                                                echo "<i class='bx bxs-video'></i>";
                                                break;
                                            case 'excel':
                                                echo "<i class='bx bx-table'></i>";
                                                break;
                                            default:
                                                echo "<i class='bx bxs-file'></i>";
                                                break;
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo $listado_documento['nombre']; ?></td>
                                    <td><?php echo $listado_documento['descripcion']; ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($listado_documento['updated_at'])); ?></td>
                                    <td>
                                        <?php if ($listado_documento['foto_usuario']) { ?>
                                            <img src="<?= $base ?>/public/img/usuarios/<?php echo $listado_documento['foto_usuario']; ?>" alt="Foto usuario" width="30" height="30">
                                        <?php } else { ?>
                                            <i class='bx bx-user'></i>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <?php if (in_array($rol, $id_rol)) { ?>
                                            <i onclick="editarDocumento(<?php echo $listado_documento['id']; ?>, '<?php echo $listado_documento['nombre']; ?>', '<?php echo $listado_documento['descripcion']; ?>', '<?php echo $listado_documento['ruta']; ?>', '<?php echo $listado_documento['tipo']; ?>')" class='bx bx-edit'></i>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <?php if (in_array($rol, $id_rol)) { ?>
                                            <a onclick="return confirmarEliminacion();" href="<?= $base ?>/documentos/delete/<?php echo $listado_documento['id']; ?>">
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
                        <i class='bx bx-folder-plus'></i>
                        <h3>Nuevo documento</h3>
                    </div>
                    <form action="<?= $base ?>/documentos" method="post" id="passwordForm" enctype="multipart/form-data">
                        <div class="form-group password-container">
                            <label for="nombre">Nombre del documento *</label>
                            <input type="text" id="nombre" name="nombre" class="form-control" required>
                        </div>
                        <div class="form-group password-container">
                            <label for="descripcion">Descripción del documento</label>
                            <input type="text" id="descripcion" name="descripcion" class="form-control">
                        </div>
                        <div class="form-group password-container">
                            <label for="archivo">Documento adjunto *</label>
                            <input style="background-color: transparent; border: none; border-radius: 0px;" type="file" name="archivo" id="archivo" class="form-control-file" accept=".png, .jpg, .jpeg, .pdf, .mp4, .avi, .mkv, .xls, .xlsx, .apk" required>
                        </div>
                        <div class="form-group password-container">
                            <div class="form-check d-flex align-items-center">
                                <input type="checkbox" class="form-check-input" id="enviar_notificacion" name="enviar_notificacion">
                                <label class="form-check-label ml-2 mt-2 mb-2" for="enviar_notificacion">Enviar notificación</label>
                            </div>
                        </div>
                        <button type="submit" name="anadir_documento" id="btnSubmit">Guardar</button>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal para editar documento -->
    <?php if (in_array($rol, $id_rol)) { ?>
        <div style="z-index:9999;" class="modal fade" id="editarDocumentoModal" tabindex="-1" role="dialog" aria-labelledby="editarDocumentoModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editarDocumentoModalLabel">Editar documento</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="editarDocumentoForm" action="<?= $base ?>/documentos" method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <input type="text" id="editar_id" name="editar_id" class="form-control" hidden readonly>
                            </div>
                            <div class="form-group">
                                <label for="editar_nombre">Nombre del documento *</label>
                                <input type="text" id="editar_nombre" name="editar_nombre" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="editar_descripcion">Descripción del documento</label>
                                <input type="text" id="editar_descripcion" name="editar_descripcion" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="editar_archivo">Actualizar documento *</label>
                                <input style="background-color: transparent; border: none; border-radius: 0px;" type="file" name="editar_archivo" id="editar_archivo" class="form-control-file" accept=".png, .jpg, .jpeg, .pdf, .mp4, .avi, .mkv, .xls, .xlsx, .apk">
                                <div id="editar_documento_actual"></div>
                            </div>
                            <div class="form-group">
                                <div class="form-check d-flex align-items-center">
                                    <input type="checkbox" class="form-check-input" id="editar_enviar_notificacion" name="editar_enviar_notificacion">
                                    <label class="form-check-label ml-2 mt-2 mb-2" for="editar_enviar_notificacion">Enviar notificación</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <button type="submit" name="editar_documento" class="btn" id="btnSubmit">Guardar</button>
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
            event.stopPropagation();
            return confirm("¿Estás seguro de eliminar este documento?");
        }

        function editarDocumento(id, nombre, descripcion, ruta, tipo) {
            event.stopPropagation();
            document.getElementById('editar_id').value = id;
            document.getElementById('editar_nombre').value = nombre;
            document.getElementById('editar_descripcion').value = descripcion;

            // Mostrar el nombre del archivo actual (ruta)
            var nombreArchivo = ruta.split('/').pop();
            document.getElementById('editar_documento_actual').innerHTML = "Archivo actual: " + nombreArchivo;

            $('#editarDocumentoModal').modal('show');
        }

        function descargarDocumento(ruta) {
            window.location.href = `actions/descargar/documento_general.php?file=${encodeURIComponent(ruta)}`;
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
                    [1, 'asc']
                ],
                "columnDefs": [{
                    "targets": [0, 4, 5, 6],
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