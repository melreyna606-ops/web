<?php
$pageTitle = "Registrar Mascota 🐾";
include("../includes/header.php");
include("../conexion.php");

// ===============================
//  OBTENER USUARIOS (DUEÑOS)
// ===============================
$usuarios = [];
$queryUsuarios = "SELECT ID_Usuario, Nombre FROM USUARIO ORDER BY Nombre";
$result = mysqli_query($conn, $queryUsuarios);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $usuarios[] = $row;
    }
}

// ===============================
//  REGISTRAR MASCOTA
// ===============================
$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_usuario       = $_POST['id_usuario']       ?? '';
    $nombre           = trim($_POST['nombre']      ?? '');
    $especie          = trim($_POST['especie']     ?? '');
    $raza             = trim($_POST['raza']        ?? '');
    $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';
    $sexo             = $_POST['sexo']             ?? '';

    if (
        !empty($id_usuario) &&
        !empty($nombre) &&
        !empty($especie) &&
        !empty($raza) &&
        !empty($fecha_nacimiento) &&
        !empty($sexo)
    ) {
        // Insertar mascota con MySQLi (consulta preparada)
        $sql  = "INSERT INTO MASCOTA 
                (ID_Usuario, Nombre, Especie, Raza, FechaNacimiento, Sexo, FechaCreacion)
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param(
                $stmt,
                "isssss",
                $id_usuario,
                $nombre,
                $especie,
                $raza,
                $fecha_nacimiento,
                $sexo
            );

            $ok = mysqli_stmt_execute($stmt);

            if ($ok) {
                $mensaje = "<div class='alert success'>🎉 Mascota registrada correctamente.</div>";
            } else {
                $mensaje = "<div class='alert error'>❌ Error al registrar la mascota.</div>";
                // $mensaje .= "<pre>" . mysqli_error($conn) . "</pre>"; // descomenta si quieres ver el error
            }

            mysqli_stmt_close($stmt);
        } else {
            $mensaje = "<div class='alert error'>❌ Error al preparar el registro de la mascota.</div>";
        }
    } else {
        $mensaje = "<div class='alert warning'>⚠️ Por favor completa todos los campos.</div>";
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
    input, select {
        width: 100%;
        padding: 10px;
        margin-top: 6px;
        border: 1.5px solid #b7e4c7;
        border-radius: 8px;
        transition: 0.3s;
        font-size: 15px;
    }
    input:focus, select:focus {
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
    <h2>Registrar Mascota 🐾</h2>

    <?= $mensaje; ?>

    <!-- Importante: action vacío para que POST regrese a esta misma página -->
    <form action="" method="POST">
        <label for="id_usuario">Dueño (Usuario)</label>
        <select name="id_usuario" required>
            <option value="">Seleccione un usuario...</option>
            <?php foreach ($usuarios as $usuario): ?>
                <option value="<?= $usuario['ID_Usuario']; ?>">
                    <?= htmlspecialchars($usuario['Nombre']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="nombre">Nombre de la Mascota</label>
        <input type="text" name="nombre" placeholder="Ej. Rocky" required>

        <label for="especie">Especie</label>
        <input type="text" name="especie" placeholder="Ej. Perro, Gato" required>

        <label for="raza">Raza</label>
        <input type="text" name="raza" placeholder="Ej. Labrador, Siamés" required>

        <label for="fecha_nacimiento">Fecha de Nacimiento</label>
        <input type="date" name="fecha_nacimiento" required>

        <label for="sexo">Sexo</label>
        <select name="sexo" required>
            <option value="">Seleccione...</option>
            <option value="Macho">Macho</option>
            <option value="Hembra">Hembra</option>
        </select>

        <button type="submit">Registrar Mascota</button>
    </form>
</div>

<?php include("../includes/footer.php"); ?>
