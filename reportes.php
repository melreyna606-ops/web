<?php
$pageTitle = "Reportes Globales 📊";
include("../conexion.php");
include("../includes/header.php");

// Valor por defecto
$totalCitas = 0;

// ==========================
//   TOTAL DE CITAS AGENDADAS
// ==========================
// Si quieres excluir canceladas, podrías usar:
// $sqlTotalCitas = "SELECT COUNT(*) AS Total FROM CITA WHERE Estado <> 'Cancelada'";
$sqlTotalCitas = "SELECT COUNT(*) AS Total FROM CITA";

$result = mysqli_query($conn, $sqlTotalCitas);

if ($result && $row = mysqli_fetch_assoc($result)) {
    $totalCitas = (int)$row["Total"];
}
?>

<style>
    body {
        background-color: #f8faf9;
        font-family: "Poppins", sans-serif;
    }
    .report-wrapper {
        max-width: 900px;
        margin: 40px auto 80px;
        background: #ffffff;
        border-radius: 18px;
        box-shadow: 0 8px 18px rgba(0, 0, 0, 0.06);
        padding: 35px 45px 45px;
    }
    .report-title {
        text-align: center;
        font-size: 28px;
        color: #1b4332;
        margin-bottom: 8px;
    }
    .report-subtitle {
        text-align: center;
        color: #6c757d;
        margin-bottom: 30px;
    }
    .card-kpi {
        max-width: 320px;
        margin: 0 auto;
        background: #e9f5ec;
        border-radius: 16px;
        padding: 25px 30px;
        text-align: center;
    }
    .card-kpi h4 {
        font-size: 15px;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #40916c;
        margin-bottom: 10px;
    }
    .card-kpi .value {
        font-size: 40px;
        font-weight: 700;
        color: #081c15;
    }
</style>

<div class="report-wrapper">
    <h2 class="report-title">📊 Reportes Globales</h2>
    <p class="report-subtitle">
        Visualiza el número total de citas agendadas en la veterinaria.
    </p>

    <div class="card-kpi">
        <h4>Total de Citas</h4>
        <div class="value"><?php echo $totalCitas; ?></div>
    </div>
</div>

<?php include("../includes/footer.php"); ?>
