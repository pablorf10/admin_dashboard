<?php
// Autor: Pablo Ríos Furniet
// Licencia: Creative Commons Attribution-NonCommercial 4.0 International (CC BY-NC 4.0)
// Este archivo forma parte de un proyecto con licencia no comercial.
// Puedes ver, modificar y reutilizar este código con fines personales o educativos, siempre que menciones al autor.
// Queda prohibido el uso comercial sin autorización expresa.
// Más información: https://creativecommons.org/licenses/by-nc/4.0/

session_start();
include '../../config/conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: $base/login");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_notificacion = $_POST['id'];
    $id_usuario = $_SESSION['id_usuario'];

    $query = "DELETE FROM usuarios_notificaciones WHERE id_notificacion = :id_notificacion AND id_usuario = :id_usuario";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id_notificacion', $id_notificacion);
    $stmt->bindParam(':id_usuario', $id_usuario);
    $stmt->execute();
}
?>
