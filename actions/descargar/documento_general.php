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

if (isset($_GET['file'])) {
    $file = $_GET['file'];
    $docs_url = $_ENV['DOCS_URL'];
    $file_path = "../../$docs_url/generales/".basename($file);

    // Verifica si el archivo existe
    if (file_exists($file_path)) {
        // Define los encabezados
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . basename($file_path));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file_path));
        // Limpia el búfer de salida
        ob_clean();
        flush();
        // Lee el archivo y envíalo al usuario
        readfile($file_path);
        exit;
    } else {
        echo "El archivo no existe.";
    }
} else {
    echo "Archivo no especificado.";
}
