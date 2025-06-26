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
$id = intval($_GET['id']) ?? null;

if ($id && in_array($rol, $id_rol)) {
    
    $query = "SELECT ruta FROM documentos WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $documento = $stmt->fetch(PDO::FETCH_ASSOC);
    $docs_url = realpath($_ENV['DOCS_URL']);
    $ruta_doc = "$docs_url/generales/" . $documento['ruta'];

    if (file_exists($ruta_doc)) {
        unlink($ruta_doc);
    }

    $query = "DELETE FROM documentos WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Documento eliminado con éxito.";
    } else {
        $_SESSION['error_message'] = "Error al eliminar el documento.";
    }
} else {
    $_SESSION['error_message'] = "Acceso no autorizado o ID inválido.";
}

header("Location: $base/documentos");
exit;