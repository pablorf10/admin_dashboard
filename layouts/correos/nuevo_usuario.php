<?php
// Autor: Pablo Ríos Furniet
// Licencia: Creative Commons Attribution-NonCommercial 4.0 International (CC BY-NC 4.0)
// Este archivo forma parte de un proyecto con licencia no comercial.
// Puedes ver, modificar y reutilizar este código con fines personales o educativos, siempre que menciones al autor.
// Queda prohibido el uso comercial sin autorización expresa.
// Más información: https://creativecommons.org/licenses/by-nc/4.0/
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Nuevo mensaje | Plantilla Correo</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: Arial, Helvetica, sans-serif;
      background-color: #fafafa;
    }

    .container {
      max-width: 600px;
      margin: 0 auto;
      background: #ffffff;
      padding: 20px;
    }

    h1,
    h2 {
      color: #333;
      text-align: center;
      margin: 0 0 10px;
    }

    h1 {
      font-size: 36px;
    }

    h2 {
      font-size: 26px;
    }

    p {
      font-size: 14px;
      color: #333;
      line-height: 1.5;
      text-align: center;
      margin: 0 0 10px;
    }

    .box {
      border: 2px dashed #ccc;
      border-radius: 5px;
      padding: 20px;
      margin: 20px 0;
    }

    .password {
      font-size: 30px;
      font-weight: bold;
      color: #64a948;
    }

    .button {
      display: inline-block;
      background: #64a948;
      color: white;
      padding: 10px 30px;
      margin-top: 20px;
      border-radius: 6px;
      text-decoration: none;
      font-size: 18px;
      text-align: center;
    }
  </style>
</head>

<body>
  <div class="container">
    <h1>Creación de usuario</h1>
    <p>Hola <?php echo $nombre ?>, hemos creado un usuario para el portal de empleados con sus datos.</p>
    <p>Inicie sesión con su usuario <strong><?php echo $user ?></strong>, compruebe sus datos y cambie la contraseña cuando pueda.</p>

    <div class="box">
      <h2>Contraseña:</h2>
      <p class="password"><?php echo $contrasena ?></p>
    </div>

    <div style="text-align: center;">
      <a href="<?= $_ENV['SITE_URL']; ?>" class="button" target="_blank">INICIAR SESIÓN</a>
    </div>
  </div>
</body>

</html>
