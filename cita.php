<?php
include("../conexion.php");
include("../includes/header.php");

// ======================
// Listas para los selects
// ======================

// Usuarios (dueños)
$usuarios = [];
$qUsuarios = "SELECT ID_Usuario, Nombre FROM USUARIO ORDER BY Nombre";
$rUsuarios = mysqli_query($conn, $qUsuarios);
if ($rUsuarios) {
    while ($row = mysqli_fetch_assoc($rUsuarios)) {
        $usuarios[] = $row;
    }
}

// Mascotas
$mascotas = [];
$qMascotas = "SELECT ID_Mascota, Nombre FROM MASCOTA ORDER BY Nombre";
$rMascotas = mysqli_query($conn, $qMascotas);
if ($rMascotas) {
    while ($row = mysqli_fetch_assoc($rMascotas)) {
        $mascotas[] = $row;
    }
}

// Servicios (catálogo del admin)
$servicios = [];
$qServicios = "SELECT ID_Servicio, Nombre, Precio FROM SERVICIO ORDER BY Nombre";
$rServicios = mysqli_query($conn, $qServicios);
if ($rServicios) {
    while ($row = mysqli_fetch_assoc($rServicios)) {
        $servicios[] = $row;
    }
}

// ======================
// Registro de la cita
// ======================
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $id_usuario   = $_POST['id_usuario']   ?? null;
    $id_mascota   = $_POST['id_mascota']   ?? null;
    $id_servicio  = $_POST['id_servicio']  ?? null;
    $fecha        = $_POST['fecha']        ?? null;
    $hora         = $_POST['hora']         ?? null;

    if ($id_usuario && $id_mascota && $id_servicio && $fecha && $hora) {

        // 1) Obtener el nombre y precio del servicio seleccionado
        $sqlServ = "SELECT Nombre, Precio FROM SERVICIO WHERE ID_Servicio = ?";
        $stmtServ = mysqli_prepare($conn, $sqlServ);
        mysqli_stmt_bind_param($stmtServ, "i", $id_servicio);
        mysqli_stmt_execute($stmtServ);
        $resultServ = mysqli_stmt_get_result($stmtServ);

        if ($resultServ && ($rowServ = mysqli_fetch_assoc($resultServ))) {
            $motivo = $rowServ['Nombre'];
            $precio = $rowServ['Precio'];

            // 2) Insertar la cita (MySQL usa NOW() en lugar de GETDATE())
            $sql = "INSERT INTO CITA (ID_Usuario, ID_Mascota, Fecha, Hora, Motivo, Estado, FechaCreacion)
                    VALUES (?, ?, ?, ?, ?, 'Pendiente', NOW())";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "iisss", $id_usuario, $id_mascota, $fecha, $hora, $motivo);

            if (mysqli_stmt_execute($stmt)) {
                // 3) Obtener ID de la cita recién insertada
                $id_cita = mysqli_insert_id($conn);

                if ($id_cita) {
                    // 4) Redirigir a facturación
                    header("Location: factura.php?id_cita=" . urlencode($id_cita));
                    exit;
                } else {
                    echo '<div class="alert alert-warning text-center mt-3">
                            ⚠ Cita registrada, pero no se pudo obtener su ID para la facturación.
                          </div>';
                }
            } else {
                echo '<div class="alert alert-danger text-center mt-3">
                        ❌ Error al registrar la cita.
                      </div>';
                echo '<pre>' . mysqli_error($conn) . '</pre>';
            }

        } else {
            echo '<div class="alert alert-danger text-center mt-3">
                    ❌ No se pudo obtener la información del servicio seleccionado.
                  </div>';
        }

    } else {
        echo '<div class="alert alert-warning text-center mt-3">
                ⚠ Por favor completa todos los campos.
              </div>';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Registrar Cita 🐾</title>
    <link rel="stylesheet" href="../css/estilo.css" />
    <style>
        body {
            background:#f8faf9;
            font-family:Poppins, system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
        }
        .container {
            max-width: 720px;
            margin: 60px auto;
            background:#fff;
            border-radius:16px;
            box-shadow:0 10px 24px rgba(0,0,0,.08);
            padding:28px;
        }
        h2 {
            text-align:center;
            color:#2d6a4f;
            margin-bottom:22px;
        }
        label {
            font-weight:600;
            color:#1b4332;
            margin-top:8px;
            display:block;
        }
        select, input {
            width:100%;
            padding:12px;
            border:1.5px solid #b7e4c7;
            border-radius:10px;
            margin-top:6px;
            font-size:15px;
            transition:.25s;
            background:#fff;
        }
        select:focus, input:focus {
            outline:none;
            border-color:#40916c;
            box-shadow:0 0 0 4px rgba(64,145,108,.12);
        }
        .btn {
            width:100%;
            margin-top:16px;
            padding:12px 14px;
            border:none;
            border-radius:12px;
            background:#40916c;
            color:#fff;
            font-weight:700;
            cursor:pointer;
        }
        .btn:hover { background:#2d6a4f; }
        .alert {
            padding:12px 14px;
            border-radius:10px;
            margin:16px auto;
            max-width:720px;
            font-weight:600;
            text-align:center;
        }
        .alert-danger { background:#ffe5e5; color:#b00020; }
        .alert-warning { background:#fff3bf; color:#664d03; }
        .muted {
            color:#6b7280;
            font-size:13px;
            margin-top:4px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Registrar Cita 🐾</h2>

    <?php if (empty($servicios)): ?>
        <div class="alert alert-warning">
            ⚠ No hay servicios registrados. Ve al panel de administrador y agrega servicios en el catálogo.
        </div>
    <?php endif; ?>

    <form method="POST">
        <!-- Usuario -->
        <label for="id_usuario">Dueño (Usuario)</label>
        <select name="id_usuario" id="id_usuario" required>
            <option value="">Seleccione un usuario…</option>
            <?php foreach ($usuarios as $u): ?>
                <option value="<?= $u['ID_Usuario'] ?>">
                    <?= htmlspecialchars($u['Nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <!-- Mascota -->
        <label for="id_mascota">Mascota</label>
        <select name="id_mascota" id="id_mascota" required>
            <option value="">Seleccione una mascota…</option>
            <?php foreach ($mascotas as $m): ?>
                <option value="<?= $m['ID_Mascota'] ?>">
                    <?= htmlspecialchars($m['Nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <!-- Servicio -->
        <label for="id_servicio">Tipo de Servicio</label>
        <select name="id_servicio" id="id_servicio" required>
            <option value="">Seleccione un servicio…</option>
            <?php foreach ($servicios as $s): ?>
                <option value="<?= $s['ID_Servicio'] ?>">
                    <?= htmlspecialchars($s['Nombre']) ?> — $<?= number_format((float)$s['Precio'], 2) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <div class="muted">
            Los servicios vienen del catálogo administrado en el panel de Administrador (tabla SERVICIO).
        </div>

        <!-- Fecha -->
        <label for="fecha">Fecha de la Cita</label>
        <input type="date" name="fecha" id="fecha" required />

        <!-- Hora -->
        <label for="hora">Hora de la Cita</label>
        <input type="time" name="hora" id="hora" required step="60" />

        <button type="submit" class="btn">Registrar Cita</button>
    </form>
</div>

<?php include("../includes/footer.php"); ?>
</body>
</html>