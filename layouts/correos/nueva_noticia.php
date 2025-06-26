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
  <title>Nueva Noticia | Plantilla Correo</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: Arial, sans-serif;
      background-color: #FAFAFA;
    }
    .container {
      max-width: 600px;
      margin: auto;
      background-color: #ffffff;
      padding: 20px;
      border-radius: 8px;
    }
    h1 {
      color: #333;
      font-size: 36px;
      text-align: center;
    }
    .noticia-box {
      border: 2px dashed #ccc;
      border-radius: 5px;
      padding: 20px;
      text-align: center;
    }
    .noticia-box h2 {
      color: #64a948;
      font-size: 28px;
    }
    .noticia-box p {
      color: #333;
      font-size: 14px;
      line-height: 1.5;
      text-align: justify;
    }
    .noticia-box img {
      max-width: 100%;
      height: auto;
      margin: 10px 0;
    }
    .btn {
      display: inline-block;
      background-color: #64a948;
      color: #fff;
      padding: 10px 30px;
      text-decoration: none;
      border-radius: 6px;
      font-size: 16px;
      margin-top: 20px;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>Nueva noticia</h1>
    <div class="noticia-box">
      <h2><strong><?php echo $titulo; ?></strong></h2>
      <?php if ($imagen != null): ?>
        <img src="cid:imagen_cid" alt="Imagen">
      <?php endif; ?>
      <p><?php echo $descripcion; ?></p>
      <?php if ($url != null): ?>
        <p><a href="<?php echo $url; ?>"><?php echo $url; ?></a></p>
      <?php endif; ?>
      <?php if ($video != null): ?>
        <p><a href="<?php echo $video; ?>"><?php echo $video; ?></a></p>
      <?php endif; ?>
      <a href="<?= $_ENV['SITE_URL']; ?>" class="btn" target="_blank">ACCEDER AL PORTAL</a>
    </div>
  </div>
</body>
</html>