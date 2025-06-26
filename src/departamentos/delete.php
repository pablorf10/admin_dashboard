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
$id = $_GET['id'] ?? null;

if ($id && in_array($rol, $id_rol)) {
    $query = "DELETE FROM departamentos WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $id);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Departamento eliminado con éxito.";
    } else {
        $_SESSION['error_message'] = "Error al eliminar el departamento.";
    }
} else {
    $_SESSION['error_message'] = "Acceso no autorizado o ID inválido.";
}
 
header("Location: $base/departamentos");
exit;
