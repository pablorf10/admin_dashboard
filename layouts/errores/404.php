<?php 
// Autor: Pablo Ríos Furniet
// Licencia: Creative Commons Attribution-NonCommercial 4.0 International (CC BY-NC 4.0)
// Este archivo forma parte de un proyecto con licencia no comercial.
// Puedes ver, modificar y reutilizar este código con fines personales o educativos, siempre que menciones al autor.
// Queda prohibido el uso comercial sin autorización expresa.
// Más información: https://creativecommons.org/licenses/by-nc/4.0/

include 'config/conexion.php'; 

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página no encontrada | Admin Dashboard</title>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="icon" type="image/png" href="<?= $base ?>/public/img/favicon.png" />
    <style>
        body {
            background-color: #fdfdfd;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
        }

        .error-container {
            text-align: center;
            padding: 40px;
            max-width: 600px;
            border-radius: 16px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            background-color: white;
            animation: fadeIn 1s ease-in-out;
        }

        .error-icon {
            font-size: 100px;
            color: #64a948;
            margin-bottom: 20px;
        }

        h1 {
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 15px;
        }

        p {
            font-size: 1.1rem;
            color: #666;
        }

        .btn {
            background-color: #64a948;
            border: none;
            color: white;
            padding: 12px 24px;
            font-size: 1rem;
            border-radius: 8px;
            transition: background-color 0.3s ease;
            margin-top: 10px;
            text-decoration: none;
        }

        .btn:hover {
            background-color:rgb(84, 165, 52);
            color: white;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>

    <div class="error-container">
        <div class="error-icon">
            <i class='bx bx-error-circle'></i>
        </div>
        <h1>¡Vaya! Página no encontrada</h1>
        <p>No hemos podido encontrar la página que buscas. Puede que haya cambiado de ubicación o ya no exista.</p>
        <div class="d-flex justify-content-center">
            <button onclick="history.back()" class="btn">Volver atrás</button>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script>
        const BASE_URL = '<?= rtrim($_ENV['BASE_URL'] ?? '', '/') ?>';
    </script>
    <script src="<?= $base ?>/public/js/index.js"></script>
</body>

</html>