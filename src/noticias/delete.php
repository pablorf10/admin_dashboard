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
$id = intval($_GET['id']) ?? null;

if ($id && in_array($rol, $id_rol)) {
    $query = "SELECT pdf, imagen FROM noticias WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $noticia = $stmt->fetch(PDO::FETCH_ASSOC);
    $docs_url = realpath($_ENV['DOCS_URL']);
    $ruta_pdf = "$docs_url/noticias/" . $noticia['pdf'];
    $ruta_imagen = "public/img/noticias/" . $noticia['imagen'];

    if (file_exists($ruta_pdf)) {
        unlink($ruta_pdf);
    }

    if (file_exists($ruta_imagen)) {
        unlink($ruta_imagen);
    }

    $query = "DELETE FROM noticias WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Noticia eliminada con éxito.";
    } else {
        $_SESSION['error_message'] = "Error al eliminar la noticia.";
    }
} else {
    $_SESSION['error_message'] = "Acceso no autorizado o ID inválido.";
}

header("Location: $base/noticias");
exit;

?>

