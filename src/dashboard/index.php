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
include 'actions/select/datos_usuario.php';

function obtenerNombreMes($numero_mes)
{
    $meses = [
        1 => "enero",
        "febrero",
        "marzo",
        "abril",
        "mayo",
        "junio",
        "julio",
        "agosto",
        "septiembre",
        "octubre",
        "noviembre",
        "diciembre"
    ];
    return $meses[$numero_mes];
}

function obtenerNombreDiaSemana($numero_dia_semana)
{
    $dias = [
        0 => "domingo",
        "lunes",
        "martes",
        "miércoles",
        "jueves",
        "viernes",
        "sábado"
    ];
    return $dias[$numero_dia_semana];
}

$timestamp = time();
$dia_semana = obtenerNombreDiaSemana(date("w", $timestamp));
$numero_dia_mes = date("j", $timestamp);
$numero_mes = date("n", $timestamp);
$nombre_mes = obtenerNombreMes($numero_mes);
$año = date("Y", $timestamp);

$fecha_de_hoy = ucfirst($dia_semana) . " " . $numero_dia_mes . " de " . $nombre_mes . " de " . $año;

$query_docs = "SELECT documentos.*, usuarios.foto as foto_usuario FROM documentos LEFT JOIN usuarios ON documentos.id_usuario = usuarios.id  ORDER BY updated_at DESC LIMIT 5";
$stmt_docs = $conn->prepare($query_docs);
$stmt_docs->execute();
$documentos = $stmt_docs->fetchAll(PDO::FETCH_ASSOC);

$query = "SELECT noticias.*, 
                 usuarios.foto AS foto_usuario_noticia, 
                 usuarios.nombre AS nombre_usuario_noticia, 
                 usuarios.apellidos AS apellidos_usuario_noticia 
          FROM noticias 
          LEFT JOIN usuarios ON noticias.id_usuario = usuarios.id 
          ORDER BY noticias.created_at DESC 
          LIMIT 1";

$stmt = $conn->prepare($query);
$stmt->execute();
$noticia = $stmt->fetch(PDO::FETCH_ASSOC);

// Procesar formulario de sugerencias
if (isset($_POST['enviar_sugerencia'])) {
    $tipo = $_POST['tipo'];
    $descripcion = $_POST['descripcion'];
    $esAnonimo = isset($_POST['enviar_anonimo']) ? true : false;

    // Determinar el correo de destino según el tipo de sugerencia
    if (in_array($tipo, ['prl'])) {
        $email_destino = $_ENV['CORREO_NOTIFICACIONES'];
    } elseif (in_array($tipo, ['rrhh', 'otro'])) {
        $email_destino = $_ENV['CORREO_NOTIFICACIONES'];
    }

    // Definir el mapeo de tipos de sugerencia
    $tipos_sugerencia = [
        'prl' => 'Prevención de Riesgos Laborales',
        'rrhh' => 'Recursos Humanos',
        'otro' => 'Otro'
    ];

    // Determinar el nombre legible para el tipo de sugerencia
    $tipo_legible = $tipos_sugerencia[$tipo] ?? 'Tipo desconocido';

    // Preparar contenido del correo
    $contenido = "<p><strong>Tipo de sugerencia:</strong> {$tipo_legible}</p>";
    $contenido .= "<p><strong>Descripción:</strong> {$descripcion}</p>";

    if ($esAnonimo) {
        $contenido .= "<p><strong>Enviado de forma anónima</strong></p>";
    } else {
        $nombre = $usuario['nombre'] ?? '';
        $apellidos = $usuario['apellidos'] ?? '';

        if (empty($nombre) && empty($apellidos)) {
            $nombre_usuario = 'Usuario desconocido';
        } else {
            $nombre_usuario = trim($nombre . ' ' . $apellidos);
        }
        $email_usuario = $usuario['email'] ?? 'Email no disponible';
        $contenido .= "<p><strong>Nombre:</strong> {$nombre_usuario}</p>";
        $contenido .= "<p><strong>Correo:</strong> {$email_usuario}</p>";
    }

    // Configurar PHPMailer
    $mail = new PHPMailer(true);
    try {
        include 'config/mail.php';
        configureMail($mail);
        $mail->isHTML(true);
        $mail->addAddress($email_destino);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = "Nueva sugerencia - {$tipo_legible}";
        $mail->Body = $contenido;

        // Enviar el correo
        $mail->send();
        $_SESSION['success_message'] = 'Sugerencia enviada correctamente.';
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Error al enviar la sugerencia";
    }

    header("Location: $base/dashboard");
    exit;
}
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <?php include "layouts/head.php" ?>
    <title>Dashboard | Admin Dashboard</title>
</head>

<body>
    <?php include('layouts/sidebar.php'); ?>
    <!-- Main Content -->
    <div class="content">
        <?php include('layouts/navbar.php'); ?>
        <main>
            <div class="header">
                <div class="left">
                    <h1>Portal del empleado</h1>
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
            <div class="bottom-data pb-4 mt-1">
                <div class="container-fluid p-3 m-0" style="background-color: transparent;">
                    <div class="row">
                        <!-- Columna de usuario -->
                        <div class="col-lg-3 col-md-6 col-12 mb-4">
                            <div class="card card-with-background">
                                <div class="card-body">
                                    <div class="user d-flex align-items-center">
                                        <?php if (!$usuario['foto']) { ?>
                                            <img class="mb-3 mr-3" src="public/img/perfil2.png">
                                        <?php } else { ?>
                                            <img class="mb-3 mr-3" src="public/img/usuarios/<?php echo $usuario['foto'] ?>">
                                        <?php } ?>
                                        <h4 class="card-title">Hola <?php echo $usuario['nombre'] ?></h4>
                                    </div>
                                    <p class="card-text">¡Bienvenido/a al portal del empleado!<br><?php echo $fecha_de_hoy; ?></p>
                                </div>
                            </div>
                            <div class="card mt-4">
                                <div class="card-body">
                                    <div class="header">
                                        <i style="background-color:#64a948;border-radius:10px; color:white; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.4);" class='bx bx-bulb p-2'></i>
                                        <h3>Sugerencias</h3>
                                    </div>
                                    <div class="reminders" style="box-shadow: none">
                                        <form action="<?= $base ?>/dashboard" method="post" id="passwordForm" enctype="multipart/form-data">
                                            <div class="form-group password-container">
                                                <label for="tipo">Tipo de sugerencia *</label>
                                                <select id="tipo" name="tipo" class="form-control" required>
                                                    <option value="prl">Prevención de Riesgos Laborales</option>
                                                    <option value="rrhh">Recursos Humanos</option>
                                                    <option value="otro">Otros</option>
                                                </select>
                                            </div>
                                            <div class="form-group password-container">
                                                <label for="descripcion">Descripción de la sugerencia *</label>
                                                <textarea id="descripcion" name="descripcion" class="form-control" rows="2" required></textarea>
                                            </div>
                                            <div class="form-group password-container">
                                                <div class="form-check d-flex align-items-center">
                                                    <input type="checkbox" class="form-check-input" id="enviar_anonimo" name="enviar_anonimo">
                                                    <label class="form-check-label ml-2 mt-2 mb-2" for="enviar_anonimo">Enviar de forma anónima</label>
                                                </div>
                                            </div>
                                            <button type="submit" name="enviar_sugerencia" id="btnSubmit">Enviar</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-5 col-md-6 col-12 mb-4">
                            <div class="card">
                                <div class="card-body">
                                    <div class="header">
                                        <i style="background-color:#64a948;border-radius:10px; color:white; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.4);" class='bx bx-file p-2'></i>
                                        <h3>Documentos</h3>
                                    </div>
                                    <ul class="list-unstyled">
                                        <?php foreach ($documentos as $listado_documento) : ?>
                                            <li class="mb-2" id="descargar-doc" onclick="descargarDocumento('<?php echo $listado_documento['ruta']; ?>')">
                                                <div class="doc-info">
                                                    <span class="doc-name d-block"><?php echo $listado_documento['nombre']; ?></span>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center" style="width: 100%;">
                                                    <div style="flex-grow: 1;"></div> <!-- Esto ayuda a empujar la fecha y el icono hacia la derecha -->
                                                    <div class="d-flex align-items-center" style="margin-top: 4px;">
                                                        <?php if ($listado_documento['foto_usuario']) { ?>
                                                            <img src="public/img/usuarios/<?php echo $listado_documento['foto_usuario']; ?>" alt="Foto usuario" class="img-usuario mr-2"
                                                                style="width: 24px; height: 24px; border-radius: 50%; object-fit: cover;">
                                                        <?php } else { ?>
                                                            <i class='bx bx-user'></i>
                                                        <?php } ?>
                                                        <span class="doc-date text-muted" style="white-space: nowrap;"><?php echo date('d/m/Y - H:i', strtotime($listado_documento['updated_at'])); ?></span>
                                                        <i class='bx bxs-download ml-2'></i>
                                                    </div>
                                                </div>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-12 col-12 d-flex flex-column">
                            <div class="card flex-fill">
                                <div class="card-body">
                                    <div class="header mb-4">
                                        <i style="background-color:#64a948;border-radius:10px; color:white; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.4);" class='bx bx-news p-2'></i>
                                        <h3>Noticias</h3>
                                    </div>
                                    <?php if ($noticia && is_array($noticia)): ?>
                                        <h4 id="preview-titulo" class="mb-4"><?php echo $noticia['titulo']; ?></h4>
                                        <?php if ($noticia['imagen'] != NULL): ?>
                                            <img id="preview-imagen" src="public/img/noticias/<?php echo $noticia['imagen']; ?>" alt="" style="max-width: 100%;" class="mb-4">
                                        <?php endif; ?>
                                        <?php
                                        $video_url = $noticia['video'];
                                        $video_id = null;

                                        if ($video_url) {
                                            parse_str(parse_url($video_url, PHP_URL_QUERY), $query);
                                            $video_id = isset($query['v']) ? $query['v'] : null;
                                        }
                                        ?>
                                        <div id="preview-video" class="mb-4" style="<?php echo $video_id ? '' : 'display:none;'; ?>">
                                            <iframe id="video-iframe" src="<?php echo $video_id ? "https://www.youtube.com/embed/$video_id" : ''; ?>" width="100%" height="200px" frameborder="0" allowfullscreen></iframe>
                                        </div>
                                        <div class="contenido-noticia">
                                            <p id="preview-descripcion" align="justify" class="mb-4"><?php echo $noticia['descripcion']; ?></p>
                                            <?php if ($noticia['pdf'] != NULL): ?>
                                                <div id="preview-pdf" class="mb-4" onclick="descargarDocumentoNoticia('<?php echo $noticia['pdf']; ?>')" style="color: #0000FF">
                                                    <i class="bx bx-download" style="vertical-align: middle; margin-right: 5px;"></i><span id="pdf-nombre">Descargar documento</span>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($noticia['url'] != NULL): ?>
                                                <div id="preview-enlace" class="mb-4">
                                                    <a id="enlace-externo" href="<?php echo $noticia['url']; ?>" target="_blank" class="enlace-descarga" style="color: #0000FF"><?php echo $noticia['url']; ?></a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (strlen($noticia['descripcion']) > 53): ?>
                                            <button class="btn btn-link ver-mas">Ver más</button>
                                        <?php endif; ?>
                                        <div class="header mt-4 m-0 d-flex align-items-center justify-content-end">
                                            <?php if ($noticia['foto_usuario_noticia']) { ?>
                                                <img src="public/img/usuarios/<?php echo $noticia['foto_usuario_noticia']; ?>" alt="Foto usuario" class="img-usuario" style="width: 34px; height: 34px; border-radius: 50%; object-fit: cover;">
                                            <?php } else { ?>
                                                <i class='bx bx-user'></i>
                                            <?php } ?>
                                            <p id="preview-usuario" class="m-0">
                                                <?php echo $noticia['nombre_usuario_noticia'] . " " . $noticia['apellidos_usuario_noticia']; ?>
                                            </p>
                                        </div>
                                        <p id="preview-dia" align="right" class="mb-1"><?php echo date('d/m/Y - H:i', strtotime($noticia['created_at'])); ?></p>
                                    <?php else: ?>
                                        <p>Aún no se ha publicado ninguna noticia.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </main>
    </div>
    <?php include "layouts/scripts.php" ?>
    <script>
        function descargarDocumento(ruta) {
            window.location.href = `actions/descargar/documento_general.php?file=${encodeURIComponent(ruta)}`;
        }

        $(document).ready(function() {
            $('.ver-mas').on('click', function() {
                const $contenido = $(this).siblings('.contenido-noticia');
                $contenido.toggleClass('contenido-expandido');
                $(this).text($contenido.hasClass('contenido-expandido') ? 'Ver menos' : 'Ver más');
            });
        });

        function descargarDocumentoNoticia(ruta) {
            window.location.href = `actions/descargar/documento_noticia.php?file=${encodeURIComponent(ruta)}`;

        }
    </script>
</body>

</html>