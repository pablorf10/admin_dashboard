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

$query = "SELECT noticias.*, usuarios.foto as foto_usuario_noticia, usuarios.nombre as nombre_usuario_noticia, usuarios.apellidos as apellidos_usuario_noticia FROM noticias 
          LEFT JOIN usuarios ON noticias.id_usuario = usuarios.id
          ORDER BY noticias.created_at DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$noticias = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <?php include "layouts/head.php" ?>
    <title>Noticias | Admin Dashboard</title>
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
                        <li><a class="active"  href="">Noticias</a></li>
                        <?php if (in_array($rol, $id_rol)) { ?>
                            /
                            <li><a  href="<?= $base ?>/noticias/anadir">Añadir noticia</a></li>
                        <?php } ?>
                    </ul>
                </div>
            </div>

            <div class="bottom-data pb-4">
                <div class="reminders row mr-1 ml-1">
                    <?php if (empty($noticias)): ?>
                        <p class="m-0">Aún no se han publicado noticias.</p>
                    <?php else: ?>
                        <?php foreach ($noticias as $listado_noticias) : ?>
                            <div id="preview" class="preview col-lg-4 col-md-6 col-sm-12 m-0">
                                <div class="header mb-4">
                                    <i class='bx bx-news'></i>
                                    <h3 id="preview-titulo"><?php echo htmlspecialchars($listado_noticias['titulo']); ?></h3>
                                </div>
                                <?php if ($listado_noticias['imagen'] != NULL): ?>
                                    <img id="preview-imagen" src="<?= $base ?>/public/img/noticias/<?php echo htmlspecialchars($listado_noticias['imagen']); ?>" alt="" style="max-width: 100%;" class="mb-4">
                                <?php endif; ?>
                                <?php
                                $video_url = $listado_noticias['video'];
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
                                    <p id="preview-descripcion" align="justify" class="mb-4"><?php echo $listado_noticias['descripcion']; ?></p>
                                    <?php if ($listado_noticias['pdf'] != NULL): ?>
                                        <div id="preview-pdf" class="mb-4" onclick="descargarDocumento('<?php echo $listado_noticias['pdf']; ?>')" style="color: #0000FF">
                                            <i class="bx bx-download" style="vertical-align: middle; margin-right: 5px;"></i><span id="pdf-nombre">Descargar documento</span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($listado_noticias['url'] != NULL): ?>
                                        <div id="preview-enlace" class="mb-4">
                                            <a id="enlace-externo" href="<?php echo $listado_noticias['url']; ?>" target="_blank" class="enlace-descarga" style="color: #0000FF"><?php echo $listado_noticias['url']; ?></a>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <?php if (strlen($listado_noticias['descripcion']) > 53): ?>
                                    <button class="btn btn-link ver-mas">Ver más</button>
                                <?php endif; ?>

                                <div class="header m-0 d-flex align-items-center justify-content-end">
                                    <?php if ($listado_noticias['foto_usuario_noticia']) { ?>
                                        <img src="<?= $base ?>/public/img/usuarios/<?php echo htmlspecialchars($listado_noticias['foto_usuario_noticia']); ?>" alt="Foto usuario" class="img-usuario" style="width: 34px; height: 34px; border-radius: 50%; object-fit: cover;">
                                    <?php } else { ?>
                                        <i class='bx bx-user'></i>
                                    <?php } ?>
                                    <p id="preview-usuario" class="m-0">
                                        <?php echo htmlspecialchars($listado_noticias['nombre_usuario_noticia'] . " " . $listado_noticias['apellidos_usuario_noticia']); ?>
                                    </p>
                                </div>
                                <p id="preview-dia" align="right" class="mb-1"><?php echo date('d/m/Y - H:i', strtotime($listado_noticias['created_at'])); ?></p>
                                <?php if (in_array($rol, $id_rol)) { ?>
                                    <div class="header m-0 d-flex align-items-center justify-content-end">
                                        <a onclick="return confirmarEliminacion();" href="<?= $base ?>/noticias/delete/<?php echo $listado_noticias['id']; ?>">
                                            <i class='bx bx-trash'></i>
                                        </a>
                                    </div>
                                <?php } ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        </main>
    </div>
    <?php include "layouts/scripts.php" ?>
    <script>
        $(document).ready(function() {
            $('.ver-mas').on('click', function() {
                const $contenido = $(this).siblings('.contenido-noticia');
                $contenido.toggleClass('contenido-expandido');
                $(this).text($contenido.hasClass('contenido-expandido') ? 'Ver menos' : 'Ver más');
            });
        });

        function descargarDocumento(ruta) {
            window.location.href = `../actions/descargar/documento_noticia.php?file=${encodeURIComponent(ruta)}`;
        }

        function confirmarEliminacion() {
            event.stopPropagation();
            return confirm("¿Estás seguro de eliminar esta noticia?");
        }
    </script>

</body>

</html>