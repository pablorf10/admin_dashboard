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
$rol = 5;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!in_array($rol, $id_rol)) {
    $_SESSION['error_message'] = 'No tienes permisos';
    header("Location: $base/noticias");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['anadir_noticia'])) {
    if (in_array($rol, $id_rol)) {
        $titulo = filter_input(INPUT_POST, 'titulo', FILTER_SANITIZE_STRING);
        $descripcion = nl2br(filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_STRING));
        $video = filter_input(INPUT_POST, 'video', FILTER_SANITIZE_URL); // Obtener el video
        $url = filter_input(INPUT_POST, 'enlace', FILTER_SANITIZE_URL); // Obtener el enlace externo
        $enviar_notificacion = isset($_POST['enviar_notificacion']);

        // Insertar la noticia incluyendo video y enlace externo
        $query = "INSERT INTO noticias (titulo, descripcion, video, url, id_usuario) 
                  VALUES (:titulo, :descripcion, :video, :url, :id_usuario)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':titulo', $titulo);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':video', $video);
        $stmt->bindParam(':url', $url);
        $stmt->bindParam(':id_usuario', $_SESSION['id_usuario']);


        if ($stmt->execute()) {
            $id_noticia = $conn->lastInsertId(); // Obtener el ID de la noticia recién creada

            // Manejo de archivo de imagen
            $imagen = null;
            if (!empty($_FILES['imagen']['name'])) {
                $imagen_extension = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
                $nombre_imagen_final = "imagen$id_noticia.$imagen_extension";
                $ruta_imagen = "public/img/noticias/$nombre_imagen_final";

                if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_imagen)) {
                    $imagen = $nombre_imagen_final;
                } else {
                    $_SESSION['error_message'] = "Error al subir la imagen.";
                    header("Location: $base/noticias/anadir");
                    exit;
                }
            }

            // Manejo de archivo PDF
            $pdf = null;
            if (!empty($_FILES['pdf']['name'])) {
                $nombre_pdf_final = "pdf$id_noticia.pdf";
                $docs_url = realpath($_ENV['DOCS_URL']);
                $ruta_pdf = "$docs_url/noticias/$nombre_pdf_final";

                if (move_uploaded_file($_FILES['pdf']['tmp_name'], $ruta_pdf)) {
                    $pdf = $nombre_pdf_final;
                } else {
                    $_SESSION['error_message'] = "Error al subir el PDF.";
                    header("Location: $base/noticias/anadir");
                    exit;
                }
            }

            // Actualizar la noticia con los archivos subidos (si existen)
            $query_update = "UPDATE noticias SET imagen = :imagen, pdf = :pdf WHERE id = :id_noticia";
            $stmt_update = $conn->prepare($query_update);
            $stmt_update->bindParam(':imagen', $imagen);
            $stmt_update->bindParam(':pdf', $pdf);
            $stmt_update->bindParam(':id_noticia', $id_noticia);

            if (!$stmt_update->execute()) {
                $_SESSION['error_message'] = "Error al actualizar la noticia con los archivos.";
                header("Location: $base/noticias/anadir");
                exit;
            }

            if ($enviar_notificacion) {

                $query_notificacion = "INSERT INTO notificaciones (nombre, fecha, hora, tipo, id_usuario)
                                               VALUES (:titulo, NOW(), NOW(), 'Nueva noticia', :id_usuario)";
                $stmt_notificacion = $conn->prepare($query_notificacion);
                $stmt_notificacion->bindParam(':titulo', $titulo);
                $stmt_notificacion->bindParam(':id_usuario', $_SESSION['id_usuario']);
                $stmt_notificacion->execute();

                $id_notificacion = $conn->lastInsertId();

                // Aquí deberías obtener los usuarios a los que quieres enviar notificaciones
                //$query_usuarios = "SELECT id FROM usuarios WHERE id IN (1)";
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
                    if ($imagen != null) {
                        $mail->addEmbeddedImage('public/img/noticias/' . $imagen, 'imagen_cid');
                    }
                    $mail->addAddress($_ENV['CORREO_NOTIFICACIONES']);
                    $mail->Subject = 'Noticias | Admin Dashboard';
                    $mail->CharSet = 'UTF-8';
                    ob_start();
                    include 'layouts/correos/nueva_noticia.php';
                    $mail->Body = ob_get_clean();
                    // Adjuntar el PDF si se ha subido
                    /* if ($pdf != null) {
                        $ruta_pdf_completa = '../../intranet_documentos/noticias/pdfs/' . $pdf;
                        $mail->addAttachment($ruta_pdf_completa, 'doc_noticia.pdf');
                    } */
                    $mail->send();
                } catch (Exception $e) {
                    echo "Error al enviar el correo electrónico: {$mail->ErrorInfo}";
                }
            }
        } else {
            $_SESSION['error_message'] = "Error al insertar la noticia.";
            header("Location: $base/noticias/anadir");
            exit;
        }
    } else {
        $_SESSION['error_message'] = "No tiene permiso para realizar esta acción.";
        header("Location: $base/noticias");
        exit;
    }
    $_SESSION['success_message'] = "Noticia publicada con éxito.";
    header("Location: $base/noticias/anadir");
    exit;
}


?>

<!DOCTYPE html>
<html lang="es">

<head>
    <?php include "layouts/head.php" ?>
    <title>Añadir noticia | Admin Dashboard</title>
</head>

<body>
    <?php include('layouts/sidebar.php'); ?>
    <!-- Main Content -->
    <div class="content">
        <?php include('layouts/navbar.php'); ?>
        <main>
            <div class="header">
                <div class="left">
                    <h1>Noticias</h1>
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
                        <li><a href="<?= $base ?>/noticias">Noticias</a></li>
                        <?php if (in_array($rol, $id_rol)) { ?>
                            /
                            <li><a class="active" href="">Añadir noticia</a></li>
                        <?php } ?>
                    </ul>
                </div>
            </div>

            <div class="bottom-data pb-4">
                <div class="reminders">
                    <div class="header">
                        <i class='bx bx-folder-plus'></i>
                        <h3>Añadir noticia</h3>
                    </div>
                    <form action="<?= $base ?>/noticias/anadir" method="post" id="passwordForm" enctype="multipart/form-data">
                        <div class="form-group password-container">
                            <label for="titulo">Título de la noticia*</label>
                            <input type="text" id="titulo" name="titulo" class="form-control" required>
                        </div>
                        <div class="form-group password-container">
                            <label for="descripcion">Cuerpo de la noticia *</label>
                            <textarea id="descripcion" name="descripcion" class="form-control" required></textarea>
                        </div>
                        <div class="form-group password-container">
                            <label for="imagen">Imagen</label>
                            <input type="file" name="imagen" id="imagen" class="input-seleccionar" accept=".png, .jpg, .jpeg">
                        </div>
                        <div class="form-group password-container">
                            <label for="pdf">Documento PDF</label>
                            <input type="file" name="pdf" id="pdf" class="input-seleccionar" accept=".pdf">
                        </div>
                        <div class="form-group password-container">
                            <label for="video">Vídeo (URL de YouTube)</label>
                            <input type="text" name="video" id="video" class="form-control" placeholder="https://www.youtube.com/watch?v=">
                        </div>
                        <div class="form-group password-container">
                            <label for="enlace">Enlace externo</label>
                            <input type="text" name="enlace" id="enlace" class="form-control" placeholder="https://sitio-web.com">
                        </div>
                        <div class="form-group password-container">
                            <div class="form-check d-flex align-items-center">
                                <input type="checkbox" class="form-check-input" id="enviar_notificacion" name="enviar_notificacion">
                                <label class="form-check-label ml-2 mt-2 mb-2" for="enviar_notificacion">Enviar notificación</label>
                            </div>
                        </div>
                        <button type="submit" name="anadir_noticia" id="btnSubmit">Publicar</button>
                    </form>
                </div>

                <div class="reminders" style="max-width: 500px;">
                    <div class="header">
                        <i class='bx bx-show'></i>
                        <h3>Previsualización</h3>
                    </div>
                    <div id="preview" class="preview">
                        <h4 id="preview-titulo" class="mb-4"></h4>
                        <img id="preview-imagen" src="" alt="" style="max-width: 100%; display: none;" class="mb-4">
                        <div id="preview-video" style="display: none;" class="mb-4">
                            <iframe id="video-iframe" width="100%" height="200px" frameborder="0" allowfullscreen></iframe>
                        </div>
                        <p id="preview-descripcion" align="justify" class="mb-4"></p>
                        <div id="preview-pdf" style="display: none;" class="mb-4">
                            <a id="pdf-enlace" href="#" target="_blank" class="enlace-descarga" style="color: #0000FF">
                                <i class="bx bx-download" style="vertical-align: middle; margin-right: 5px;"></i><span id="pdf-nombre"></span>
                            </a>
                        </div>
                        <div id="preview-enlace" style="display: none;" class="mb-4">
                            <a id="enlace-externo" href="#" target="_blank" class="enlace-descarga" style="color: #0000FF"></a>
                        </div>

                        <div class="header m-0 d-flex align-items-center justify-content-end">
                            <?php if ($usuario['foto']) { ?>
                                <img src="<?= $base ?>/public/img/usuarios/<?php echo $usuario['foto']; ?>" alt="Foto usuario" class="img-usuario"
                                    style="width: 34px; height: 34px; border-radius: 50%; object-fit: cover;">
                            <?php } else { ?>
                                <i class='bx bx-user' style="width: 34px; height: 34px; border-radius: 50%; object-fit: cover;"></i>
                            <?php } ?>
                            <p id="preview-usuario" class="m-0">
                                <?php echo $usuario['nombre'] . " " . $usuario['apellidos']; ?>
                            </p>
                        </div>
                        <p id="preview-dia" align="right"><?php echo date('d/m/Y - H:i', time()); ?></p>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <?php include "layouts/scripts.php" ?>
    <script>
        // Función para previsualizar el título
        $('#titulo').on('input', function() {
            let titulo = $(this).val();
            $('#preview-titulo').text(titulo ? titulo : '');
        });

        // Función para previsualizar la descripción
        $('#descripcion').on('input', function() {
            let descripcion = $(this).val();
            // Reemplazar los saltos de línea con <br> para que se muestren en el HTML
            let descripcionHTML = descripcion.replace(/\n/g, '<br>');
            $('#preview-descripcion').html(descripcionHTML ? descripcionHTML : '');
        });

        // Función para previsualizar la imagen
        $('#imagen').on('change', function() {
            let input = this;
            if (input.files && input.files[0]) {
                let reader = new FileReader();
                reader.onload = function(e) {
                    $('#preview-imagen').attr('src', e.target.result).show();
                }
                reader.readAsDataURL(input.files[0]);
            } else {
                $('#preview-imagen').hide();
            }
        });

        // Función para previsualizar el video de YouTube
        $('#video').on('input', function() {
            let videoUrl = $(this).val();
            let videoId = videoUrl.split('v=')[1]; // Extraer el ID del video
            let embedUrl = `https://www.youtube.com/embed/${videoId}`;

            if (videoId) {
                $('#video-iframe').attr('src', embedUrl);
                $('#preview-video').show();
            } else {
                $('#preview-video').hide();
                $('#video-iframe').attr('src', '');
            }
        });

        // Función para previsualizar el enlace del PDF
        $('#pdf').on('change', function() {
            let input = this;
            if (input.files && input.files[0]) {
                let pdfFile = input.files[0];
                let pdfUrl = URL.createObjectURL(pdfFile);
                $('#pdf-enlace').attr('href', pdfUrl);
                $('#pdf-nombre').text(pdfFile.name); // Mostrar el nombre del archivo
                $('#preview-pdf').show();
            } else {
                $('#preview-pdf').hide();
            }
        });

        // Función para previsualizar el enlace externo
        $('#enlace').on('input', function() {
            let enlace = $(this).val();
            if (enlace) {
                $('#enlace-externo').attr('href', enlace).text(enlace).show(); // Mostrar enlace completo
                $('#preview-enlace').show();
            } else {
                $('#preview-enlace').hide();
            }
        });
    </script>
</body>

</html>