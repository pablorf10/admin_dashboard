<?php
// Autor: Pablo Ríos Furniet
// Licencia: Creative Commons Attribution-NonCommercial 4.0 International (CC BY-NC 4.0)
// Este archivo forma parte de un proyecto con licencia no comercial.
// Puedes ver, modificar y reutilizar este código con fines personales o educativos, siempre que menciones al autor.
// Queda prohibido el uso comercial sin autorización expresa.
// Más información: https://creativecommons.org/licenses/by-nc/4.0/
    
$base = rtrim($_ENV['BASE_URL'] ?? '/', '/');
$fullUri = $_SERVER['REQUEST_URI'];
$relativeUri = preg_replace('#^' . preg_quote($base, '#') . '#', '', $fullUri);
$segmento = explode('/', trim($relativeUri, '/'))[0] ?? 'dashboard';
?>
 
<div class="sidebar">
    <a href="<?= $base ?>" class="logo">
        <i class='bx bx-globe'></i>
        <div class="logo-name">
            <img style="width: 80%;" src="<?= $base ?>/public/img/logo.png">
        </div>
    </a>

    <ul class="side-menu">
        <li class="<?= $segmento === 'dashboard' ? 'active' : '' ?>">
            <a href="<?= $base ?>/dashboard"><i class='bx bxs-dashboard'></i>Dashboard</a>
        </li>

         <li class="<?= $segmento === 'usuarios' ? 'active' : '' ?>">
            <a href="<?= $base ?>/usuarios"><i class='bx bx-user'></i>Usuarios</a>
        </li>

         <li class="<?= $segmento === 'departamentos' ? 'active' : '' ?>">
            <a href="<?= $base ?>/departamentos"><i class='bx bx-building'></i>Departamentos</a>
        </li>

         <li class="<?= $segmento === 'noticias' ? 'active' : '' ?>">
            <a href="<?= $base ?>/noticias"><i class='bx bx-news'></i>Noticias</a>
        </li>

        <li class="<?= $segmento === 'documentos' ? 'active' : '' ?>">
            <a href="<?= $base ?>/documentos"><i class='bx bx-file'></i>Documentos</a>
        </li>
    </ul>
 
    <ul class="side-menu"> 
        <li>
            <a href="<?= $base ?>/login/logout" class="logout">
                <i class='bx bx-log-out'></i>Cerrar sesión
            </a>
        </li>
    </ul>
</div>