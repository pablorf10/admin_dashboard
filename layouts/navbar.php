<?php
// Autor: Pablo Ríos Furniet
// Licencia: Creative Commons Attribution-NonCommercial 4.0 International (CC BY-NC 4.0)
// Este archivo forma parte de un proyecto con licencia no comercial.
// Puedes ver, modificar y reutilizar este código con fines personales o educativos, siempre que menciones al autor.
// Queda prohibido el uso comercial sin autorización expresa.
// Más información: https://creativecommons.org/licenses/by-nc/4.0/
?>
<nav>
    <i class='bx bx-menu'></i>
    <form id="searchForm">
        <div class="form-input">
            <input type="search" id="searchInput" placeholder="Buscar...">
            <button class="search-btn" type="button" id="searchBtn"><i class='bx bx-search'></i></button>
        </div>
    </form>
    <?php if (in_array(1, $id_rol)) { ?>
        <a href="<?= $base ?>/roles" class="mt-2"><i class='bx bx-crown'></i></a>
    <?php } ?>
    <div class="notif-container">
        <a href="javascript:void(0);" class="notif" id="notifToggle">
            <i class='bx bx-bell mt-2'></i>
            <span class="count" style="margin-top:-8px;"><?php echo count($notificaciones); ?></span>
        </a>
        <div class="notif-dropdown" id="notifDropdown">
            <?php if (count($notificaciones) > 0) { ?>
                <div class="notif-item d-flex justify-content-center" id="borrar-todas-notis">
                    <button class="btn btn-sm m-0" onclick="borrarTodasNotificaciones()">Borrar todas las notificaciones</button>
                </div>
                <?php foreach ($notificaciones as $notif) { ?>
                    <div class="notif-item" id="notif-<?php echo $notif['id']; ?>">
                        <div class="d-flex align-items-center">
                            <button class="btn btn-sm delete-btn" onclick="borrarNotificacion(<?php echo $notif['id']; ?>)"><i class='bx bx-x'></i></button>
                            <h6><?php echo htmlspecialchars($notif['tipo']); ?></h6>
                        </div>
                        <div class="pl-5">
                            <small><?php echo htmlspecialchars($notif['nombre']); ?></small>
                            <br>
                            <?php
                            $fecha = DateTime::createFromFormat('Y-m-d', $notif['fecha']);
                            $hora = DateTime::createFromFormat('H:i:s', $notif['hora']);
                            echo "<small>" . htmlspecialchars($fecha->format('d/m/Y')) . " - " . htmlspecialchars($hora->format('H:i')) . "</small>";
                            ?>
                        </div>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <div class="notif-item no-notifications">
                    <p class="text-center">No hay notificaciones.</p>
                </div>
            <?php } ?>
        </div>
    </div>
    <a href="<?= $base ?>/perfil" class="profile">
        <div class="profile-content">
            <?php if (!$usuario['foto']) { ?>
                <i class='bx bx-user'></i>
            <?php } else { ?>
                <img class="mr-2" src="<?= $base ?>/public/img/usuarios/<?php echo $usuario['foto'] ?>">
            <?php } ?>
            <?php echo $usuario['user']; ?>
        </div>
    </a>
</nav>