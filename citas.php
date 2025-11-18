<?php
$pageTitle = "Consultar Citas 🗓️";
include("../includes/header.php");
include("../conexion.php");
?>

<div class="container mt-5 mb-5">
    <div class="card shadow-lg p-4" style="border-radius: 15px;">
        <h2 class="text-center text-success mb-4">🗓️ Citas Agendadas</h2>
        <p class="text-center text-muted mb-4">
            Visualiza todas las citas registradas en el sistema.  
            (Solo lectura, no editable por el consultor)
        </p>

        <!-- Filtros -->
        <form method="GET" class="mb-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Filtrar por Dueño</label>
                    <select name="usuario" class="form-select">
                        <option value="">Todos</option>
                        <?php
                        // Lista de dueños (USUARIO)
                        $sql_usuarios   = "SELECT ID_Usuario, Nombre FROM USUARIO ORDER BY Nombre";
                        $resultUsuarios = mysqli_query($conn, $sql_usuarios);

                        if ($resultUsuarios) {
                            while ($row = mysqli_fetch_assoc($resultUsuarios)) {
                                $selected = (isset($_GET['usuario']) && $_GET['usuario'] == $row['ID_Usuario']) ? 'selected' : '';
                                echo "<option value='{$row['ID_Usuario']}' $selected>" . htmlspecialchars($row['Nombre']) . "</option>";
                            }
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Filtrar por Servicio</label>
                    <select name="motivo" class="form-select">
                        <option value="">Todos</option>
                        <?php
                        $servicios = [
                            "Vacunación",
                            "Desparasitación",
                            "Cirugía",
                            "Consulta general",
                            "Esterilización"
                        ];
                        $motivoSeleccionado = $_GET['motivo'] ?? '';
                        foreach ($servicios as $serv) {
                            $sel = ($motivoSeleccionado === $serv) ? "selected" : "";
                            echo "<option value='" . htmlspecialchars($serv) . "' $sel>" . htmlspecialchars($serv) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-success w-100 fw-bold">🔍 Filtrar</button>
                </div>
            </div>
        </form>

        <!-- Tabla de citas -->
        <div class="table-responsive mt-3">
            <table class="table table-hover align-middle">
                <thead class="table-success text-center">
                    <tr>
                        <th>ID</th>
                        <th>Dueño</th>
                        <th>Mascota</th>
                        <th>Fecha</h4>
                        <th>Hora</th>
                        <th>Servicio</th>
                        <th>Veterinario</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Consulta base
                    $query = "
                        SELECT 
                            C.ID_Cita,
                            U.Nombre AS Dueno,
                            M.Nombre AS Mascota,
                            C.Fecha,
                            C.Hora,
                            C.Motivo AS Servicio,
                            V.Nombre AS Veterinario,
                            C.Estado
                        FROM CITA C
                        INNER JOIN USUARIO U ON C.ID_Usuario = U.ID_Usuario
                        INNER JOIN MASCOTA M ON C.ID_Mascota = M.ID_Mascota
                        LEFT JOIN VET V ON C.ID_Vet = V.ID_Vet
                    ";

                    $where  = [];
                    $params = [];
                    $types  = "";

                    // Filtro por usuario (dueño)
                    if (!empty($_GET['usuario'])) {
                        $where[]  = "U.ID_Usuario = ?";
                        $params[] = (int)$_GET['usuario'];
                        $types   .= "i"; // entero
                    }

                    // Filtro por motivo/servicio
                    if (!empty($_GET['motivo'])) {
                        $where[]  = "C.Motivo = ?";
                        $params[] = $_GET['motivo'];
                        $types   .= "s"; // string
                    }

                    if ($where) {
                        $query .= " WHERE " . implode(" AND ", $where);
                    }

                    $query .= " ORDER BY C.Fecha DESC";

                    // Si hay filtros, usamos consulta preparada
                    if (!empty($params)) {
                        $stmt = mysqli_prepare($conn, $query);
                        if ($stmt) {
                            mysqli_stmt_bind_param($stmt, $types, ...$params);
                            mysqli_stmt_execute($stmt);
                            $result = mysqli_stmt_get_result($stmt);
                        } else {
                            $result = false;
                        }
                    } else {
                        // Sin filtros: consulta directa
                        $result = mysqli_query($conn, $query);
                    }

                    if ($result === false) {
                        echo "<tr><td colspan='8' class='text-center text-danger'>❌ Error en la consulta.</td></tr>";
                    } else {
                        if (mysqli_num_rows($result) === 0) {
                            echo "<tr><td colspan='8' class='text-center text-muted'>⚕️ No se encontraron citas registradas.</td></tr>";
                        } else {
                            while ($row = mysqli_fetch_assoc($result)) {
                                $fecha = $row['Fecha'] ?? '';
                                $hora  = $row['Hora'] ?? '';

                                // En MySQL normalmente vienen como 'YYYY-MM-DD' y 'HH:MM:SS'
                                // Si quieres recortar segundos:
                                if (strlen($hora) >= 5) {
                                    $hora = substr($hora, 0, 5);
                                }

                                $estado = $row['Estado'] ?? '';
                                if ($estado === 'Completada') {
                                    $estadoColor = 'text-success';
                                } elseif ($estado === 'Pendiente') {
                                    $estadoColor = 'text-warning';
                                } else {
                                    $estadoColor = 'text-secondary';
                                }

                                echo "
                                    <tr class='text-center'>
                                        <td>{$row['ID_Cita']}</td>
                                        <td>" . htmlspecialchars($row['Dueno']) . "</td>
                                        <td>" . htmlspecialchars($row['Mascota']) . "</td>
                                        <td>" . htmlspecialchars($fecha) . "</td>
                                        <td>" . htmlspecialchars($hora) . "</td>
                                        <td>" . htmlspecialchars($row['Servicio']) . "</td>
                                        <td>" . htmlspecialchars($row['Veterinario'] ?? 'No asignado') . "</td>
                                        <td class='{$estadoColor}'><strong>" . htmlspecialchars($estado) . "</strong></td>
                                    </tr>
                                ";
                            }
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include("../includes/footer.php"); ?>
