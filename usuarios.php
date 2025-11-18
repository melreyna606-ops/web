<?php
$pageTitle = "Registrar Usuario 👤";
include("../includes/header.php");
include("../conexion.php");

$alert = "";

// Registrar usuario (dueño)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre    = trim($_POST['nombre'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $telefono  = trim($_POST['telefono'] ?? '');
    $correo    = trim($_POST['correo'] ?? '');

    if (!empty($nombre) && !empty($direccion) && !empty($telefono) && !empty($correo)) {

        // 1️⃣ Verificar si el correo ya existe (MySQLi)
        $checkSql  = "SELECT 1 FROM USUARIO WHERE CorreoElectronico = ? LIMIT 1";
        $stmtCheck = mysqli_prepare($conn, $checkSql);

        if ($stmtCheck) {
            mysqli_stmt_bind_param($stmtCheck, "s", $correo);
            mysqli_stmt_execute($stmtCheck);
            $resultCheck = mysqli_stmt_get_result($stmtCheck);

            if ($resultCheck && mysqli_num_rows($resultCheck) > 0) {
                $alert = "<div class='alert warning'>⚠️ Este correo ya está registrado.</div>";
            } else {
                // 2️⃣ Insertar nuevo usuario
                $sql  = "INSERT INTO USUARIO (Nombre, Direccion, Telefono, CorreoElectronico, FechaRegistro)
                         VALUES (?, ?, ?, ?, NOW())";
                $stmt = mysqli_prepare($conn, $sql);

                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "ssss", $nombre, $direccion, $telefono, $correo);
                    $ok = mysqli_stmt_execute($stmt);

                    if ($ok) {
                        $alert = "<div class='alert success'>🎉 Usuario registrado correctamente.</div>";
                    } else {
                        $alert = "<div class='alert error'>❌ Error al registrar usuario.</div>";
                        // $alert .= "<pre>" . mysqli_error($conn) . "</pre>"; // descomenta si quieres ver el error
                    }

                    mysqli_stmt_close($stmt);
                } else {
                    $alert = "<div class='alert error'>❌ Error al preparar el registro de usuario.</div>";
                    // $alert .= "<pre>" . mysqli_error($conn) . "</pre>";
                }
            }

            mysqli_stmt_close($stmtCheck);
        } else {
            $alert = "<div class='alert error'>❌ Error al preparar la verificación de correo.</div>";
            // $alert .= "<pre>" . mysqli_error($conn) . "</pre>";
        }

    } else {
        $alert = "<div class='alert warning'>⚠️ Por favor completa todos los campos.</div>";
    }
}
?>

<style>
    body {
        background: #f8faf9;
        font-family: "Poppins", sans-serif;
    }
    .form-wrapper {
        max-width: 650px;
        margin: 60px auto;
        background: white;
        border-radius: 15px;
        box-shadow: 0px 8px 16px rgba(0,0,0,0.1);
        padding: 40px 50px;
    }
    h2 {
        text-align: center;
        color: #2d6a4f;
        font-size: 28px;
        margin-bottom: 25px;
    }
    label {
        display: block;
        margin-top: 12px;
        font-weight: 600;
        color: #1b4332;
    }
    input, textarea {
        width: 100%;
        padding: 10px;
        margin-top: 6px;
        border: 1.5px solid #b7e4c7;
        border-radius: 8px;
        transition: 0.3s;
        font-size: 15px;
    }
    input:focus, textarea:focus {
        outline: none;
        border-color: #40916c;
        box-shadow: 0 0 5px rgba(64,145,108,0.4);
    }
    button {
        background-color: #40916c;
        color: white;
        font-weight: bold;
        padding: 12px 25px;
        border: none;
        border-radius: 10px;
        margin-top: 25px;
        cursor: pointer;
        width: 100%;
        transition: 0.3s;
    }
    button:hover {
        background-color: #2d6a4f;
    }
    .alert {
        text-align: center;
        margin: 20px auto;
        padding: 12px;
        width: 80%;
        border-radius: 10px;
        font-weight: 600;
    }
    .alert.success { background: #d8f3dc; color: #1b4332; }
    .alert.error   { background: #ffe5e5; color: #d00000; }
    .alert.warning { background: #fff3bf; color: #664d03; }
</style>

<div class="form-wrapper">
    <h2>Registrar Usuario 👤</h2>

    <!-- Mostrar mensaje -->
    <?= !empty($alert) ? $alert : '' ?>

    <form action="" method="POST">
        <label for="nombre">Nombre Completo:</label>
        <input type="text" name="nombre" placeholder="Ej. Juan Pérez" required>

        <label for="direccion">Dirección:</label>
        <input type="text" name="direccion" placeholder="Ej. Calle 123, Monterrey" required>

        <label for="telefono">Teléfono:</label>
        <input type="text" name="telefono" placeholder="Ej. 8123456789" required>

        <label for="correo">Correo Electrónico:</label>
        <input type="email" name="correo" placeholder="Ej. juanperez@email.com" required>

        <button type="submit">Registrar Usuario</button>
    </form>

    <div class="text-center mt-3">
        <a href="panel_editor.php"
           class="btn btn-secondary"
           style="
                text-decoration:none;
                color:white;
                background:#2d6a4f;
                padding:8px 16px;
                border-radius:8px;
                display:inline-block;
                margin-top:15px;">
            Volver al Panel
        </a>
    </div>
</div>

<?php include("../includes/footer.php"); ?>
