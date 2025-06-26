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

$id = $_GET['id'] ?? null;

if ($id && in_array($rol, $id_rol)) {
    $query = "SELECT foto FROM usuarios WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $usuario = $stmt->fetch();

    $foto = $usuario['foto'];

    if (isset($foto) && file_exists("public/img/usuarios/" . $foto)) {
        unlink("public/img/usuarios/" . $foto);
    }

    $query = "DELETE FROM usuarios WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $id);
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Usuario eliminado con éxito.";
    } else {
        $_SESSION['error_message'] = "Error al eliminar al usuario.";
    }
} else {
    $_SESSION['error_message'] = "Acceso no autorizado o ID inválido.";
}

header("Location: $base/usuarios");
exit;
