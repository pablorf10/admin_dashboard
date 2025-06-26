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
  <title>Nuevo documento | Plantilla Correo</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: Arial, Helvetica, sans-serif;
      background-color: #FAFAFA;
    }

    .container {
      max-width: 600px;
      margin: 0 auto;
      background: #ffffff;
      padding: 20px;
      border-radius: 8px;
    }

    h1 {
      font-size: 36px;
      text-align: center;
      color: #333;
      margin-bottom: 20px;
    }

    p {
      font-size: 16px;
      line-height: 1.6;
      color: #333;
      text-align: center;
    }

    .box {
      border: 2px dashed #ccc;
      padding: 20px;
      margin-top: 20px;
      border-radius: 5px;
      text-align: center;
    }

    .highlight {
      font-size: 28px;
      font-weight: bold;
      color: #64a948;
      margin-bottom: 10px;
    }

    .button {
      display: inline-block;
      background-color: #64a948;
      color: #fff;
      padding: 12px 25px;
      border-radius: 6px;
      text-decoration: none;
      font-size: 18px;
      margin-top: 20px;
    }

    @media only screen and (max-width: 600px) {
      h1 {
        font-size: 28px;
      }

      .highlight {
        font-size: 22px;
      }

      p {
        font-size: 14px;
      }

      .button {
        font-size: 16px;
        padding: 10px 20px;
      }
    }
  </style>
</head>

<body>
  <div class="container">
    <h1>Nuevo documento</h1>
    <p>Se ha acaba de añadir un nuevo documento al portal de empleados.</p>

    <div class="box">
      <div class="highlight"><?php echo $nombre; ?></div>
      <p>
        <?php if (!empty($descripcion)) echo $descripcion; ?>
      </p>
      <p><?php echo date('Y-m-d H:i'); ?></p>
    </div>

    <div style="text-align: center;">
      <a href="<?= $_ENV['SITE_URL']; ?>" class="button" target="_blank">VER DOCUMENTO</a>
    </div>
  </div>
</body>

</html>
