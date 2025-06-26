<?php
// Autor: Pablo Ríos Furniet
// Licencia: Creative Commons Attribution-NonCommercial 4.0 International (CC BY-NC 4.0)
// Este archivo forma parte de un proyecto con licencia no comercial.
// Puedes ver, modificar y reutilizar este código con fines personales o educativos, siempre que menciones al autor.
// Queda prohibido el uso comercial sin autorización expresa.
// Más información: https://creativecommons.org/licenses/by-nc/4.0/

if (!isset($_SESSION['id_usuario'])) {
    header("Location: $base/login");
    exit;
}

$query = "SELECT * FROM usuarios WHERE id = :id_usuario";
$stmt = $conn->prepare($query);
$stmt->bindParam(':id_usuario', $_SESSION['id_usuario']);
$stmt->execute();
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

$query_dptos = "SELECT d.* FROM usuarios_departamentos ud INNER JOIN departamentos d ON ud.id_departamento = d.id WHERE ud.id_usuario = :id_usuario";
$stmt_dptos = $conn->prepare($query_dptos);
$stmt_dptos->bindParam(':id_usuario', $_SESSION['id_usuario']);
$stmt_dptos->execute();
$departamentos = $stmt_dptos->fetchAll(PDO::FETCH_ASSOC);

$nombre_departamentos = array_map(function ($dpto) {
    return $dpto['nombre'];
}, $departamentos);

$departamentos_str = implode(", ", $nombre_departamentos);

$query_roles = "SELECT r.id FROM usuarios_roles ur INNER JOIN roles r ON ur.id_rol = r.id WHERE ur.id_usuario = :id_usuario";
$stmt_roles = $conn->prepare($query_roles);
$stmt_roles->bindParam(':id_usuario', $_SESSION['id_usuario']);
$stmt_roles->execute();
$roles = $stmt_roles->fetchAll(PDO::FETCH_ASSOC);

$id_rol = array_map(function ($rol) {
    return $rol['id'];
}, $roles);

$query_notificaciones = "SELECT n.* FROM notificaciones n INNER JOIN usuarios_notificaciones un ON n.id = un.id_notificacion WHERE un.id_usuario = :id_usuario ORDER BY n.fecha, n.hora DESC";
$stmt_notificaciones = $conn->prepare($query_notificaciones);
$stmt_notificaciones->bindParam(':id_usuario', $_SESSION['id_usuario']);
$stmt_notificaciones->execute();
$notificaciones = $stmt_notificaciones->fetchAll(PDO::FETCH_ASSOC);
