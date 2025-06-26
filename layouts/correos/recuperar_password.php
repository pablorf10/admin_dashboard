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
  <title>Recuperar contraseña | Plantilla Correo</title>
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

    h1 {
      font-size: 36px;
      color: #333333;
      text-align: center;
    }

    p {
      font-size: 14px;
      color: #333333;
      line-height: 1.5;
      text-align: center;
    }

    .box {
      border: 2px dashed #cccccc;
      border-radius: 5px;
      padding: 20px;
      margin-top: 20px;
    }

    .highlight {
      font-size: 24px;
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
    <h1>Recuperar contraseña</h1>
    <p>Se ha enviado este correo automático en respuesta a su petición. Si no ha solicitado la recuperación, ignore este mensaje.</p>

    <div class="box">
      <p class="highlight">Se ha solicitado un cambio de contraseña</p>
    </div>

    <div style="text-align: center;">
      <a href="<?php echo $enlace ?>" class="button" target="_blank">RESTABLECER CONTRASEÑA</a>
    </div>
  </div>
</body>

</html>
