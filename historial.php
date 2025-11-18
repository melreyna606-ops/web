<?php
require_once("../conexion.php"); // Debe definir $conn = mysqli_connect(...);

// -----------------------------------------
// 1. Obtener lista de usuarios (dueños)
// -----------------------------------------
$usuarios = [];
$sqlUsuarios = "SELECT ID_Usuario, Nombre FROM USUARIO ORDER BY Nombre";
$resultUsuarios = mysqli_query($conn, $sqlUsuarios);

if ($resultUsuarios) {
    while ($row = mysqli_fetch_assoc($resultUsuarios)) {
        $usuarios[] = $row;
    }
}

// -----------------------------------------
// 2. Si viene un usuario seleccionado, cargar sus mascotas
// -----------------------------------------
$usuarioSeleccionado = isset($_GET['usuario']) ? (int)$_GET['usuario'] : 0;
$mascotaSeleccionada = isset($_GET['mascota']) ? (int)$_GET['mascota'] : 0;

$mascotas = [];
if ($usuarioSeleccionado > 0) {
    $sqlMascotas = "SELECT ID_Mascota, Nombre FROM MASCOTA WHERE ID_Usuario = ? ORDER BY Nombre";
    $stmtMascotas = mysqli_prepare($conn, $sqlMascotas);
    mysqli_stmt_bind_param($stmtMascotas, "i", $usuarioSeleccionado);
    mysqli_stmt_execute($stmtMascotas);
    $resultMascotas = mysqli_stmt_get_result($stmtMascotas);

    while ($row = mysqli_fetch_assoc($resultMascotas)) {
        $mascotas[] = $row;
    }
    mysqli_stmt_close($stmtMascotas);
}

// -----------------------------------------
// 3. Si vienen usuario + mascota, consultar historial (citas)
// -----------------------------------------
$historial = [];
$consultaError = "";

if ($usuarioSeleccionado > 0 && $mascotaSeleccionada > 0) {
    $sqlHistorial = "
        SELECT
            c.Fecha,
            c.Hora,
            c.Motivo,
            c.Estado,
            u.Nombre AS NombreDueno,
            m.Nombre AS NombreMascota,
            v.Nombre AS NombreVet
        FROM CITA c
        INNER JOIN USUARIO u ON c.ID_Usuario = u.ID_Usuario
        INNER JOIN MASCOTA m ON c.ID_Mascota = m.ID_Mascota
        LEFT JOIN VET v      ON c.ID_Vet = v.ID_Vet
        WHERE c.ID_Usuario = ? AND c.ID_Mascota = ?
        ORDER BY c.Fecha DESC, c.Hora DESC, c.ID_Cita DESC
    ";

    $stmtHistorial = mysqli_prepare($conn, $sqlHistorial);
    mysqli_stmt_bind_param($stmtHistorial, "ii", $usuarioSeleccionado, $mascotaSeleccionada);
    if (mysqli_stmt_execute($stmtHistorial)) {
        $resultHistorial = mysqli_stmt_get_result($stmtHistorial);
        while ($row = mysqli_fetch_assoc($resultHistorial)) {
            // Formatear fecha y hora (MySQL devuelve strings)
            $row['FechaFormateada'] = date('Y-m-d', strtotime($row['Fecha']));
            $row['HoraFormateada']  = date('H:i', strtotime($row['Hora']));
            $historial[] = $row;
        }
    } else {
        $consultaError = "Error en la consulta SQL del historial: " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmtHistorial);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial Médico de Mascotas 🩺</title>
    <link rel="stylesheet" href="../css/estilo.css">
    <style>
        .card {
            border-radius: 18px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
        }
        .tabla-historial th {
            background-color: #b7e4c7;
            font-weight: 600;
        }
        .tabla-historial td, .tabla-historial th {
            font-size: 14px;
            vertical-align: middle;
        }
        .badge-estado {
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600;
        }
        .badge-pendiente { background:#ffeeba; color:#856404; }
        .badge-programada { background:#d4edda; color:#155724; }
        .badge-otra { background:#e2e3e5; color:#383d41; }
    </style>
</head>
<body>

<?php include("../includes/header.php"); ?>

<div class="container mt-4 mb-5">

    <div class="card p-4 tabla-historial">
        <h2 class="text-center mb-3" style="color:#1b4332;">
            🩺 Historial Médico de Mascotas 🐾
        </h2>
        <p class="text-center text-muted mb-4">
            Consulta el historial de citas de cada mascota: fecha, motivo del servicio y veterinario responsable.
        </p>

        <!-- Filtros -->
        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Seleccione un Dueño (Usuario)</label>
                <select name="usuario" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Seleccione un usuario --</option>
                    <?php foreach ($usuarios as $u): ?>
                        <option value="<?= (int)$u['ID_Usuario']; ?>"
                            <?= ($usuarioSeleccionado == $u['ID_Usuario']) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($u['Nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-semibold">Seleccione una Mascota</label>
                <select name="mascota" class="form-select">
                    <option value="">-- Seleccione una mascota --</option>
                    <?php foreach ($mascotas as $m): ?>
                        <option value="<?= (int)$m['ID_Mascota']; ?>"
                            <?= ($mascotaSeleccionada == $m['ID_Mascota']) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($m['Nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-success w-100">
                    🔍 Buscar
                </button>
            </div>
        </form>

        <!-- Mensajes de error de consulta -->
        <?php if ($consultaError): ?>
            <div class="alert alert-danger text-center">
                ❌ <?= htmlspecialchars($consultaError); ?>
            </div>
        <?php endif; ?>

        <!-- Tabla de historial -->
        <div class="table-responsive mt-3">
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Mascota</th>
                    <th>Dueño</th>
                    <th>Servicio / Motivo</th>
                    <th>Veterinario</th>
                    <th>Estado</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($usuarioSeleccionado > 0 && $mascotaSeleccionada > 0): ?>
                    <?php if (count($historial) === 0): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">
                                No se encontraron citas registradas para esta mascota.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($historial as $h): ?>
                            <?php
                            $estadoClase = 'badge-otra';
                            if ($h['Estado'] === 'Pendiente')   $estadoClase = 'badge-pendiente';
                            if ($h['Estado'] === 'Programada') $estadoClase = 'badge-programada';
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($h['FechaFormateada']); ?></td>
                                <td><?= htmlspecialchars($h['HoraFormateada']); ?></td>
                                <td><?= htmlspecialchars($h['NombreMascota']); ?></td>
                                <td><?= htmlspecialchars($h['NombreDueno']); ?></td>
                                <td><?= htmlspecialchars($h['Motivo']); ?></td>
                                <td>
                                    <?= !empty($h['NombreVet']) 
                                         ? htmlspecialchars($h['NombreVet']) 
                                         : '<span class="text-muted">Sin asignar</span>'; ?>
                                </td>
                                <td>
                                    <span class="badge-estado <?= $estadoClase; ?>">
                                        <?= htmlspecialchars($h['Estado']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted">
                            Selecciona un dueño y una mascota, luego presiona <strong>Buscar</strong>.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<?php include("../includes/footer.php"); ?>

</body>
</html>