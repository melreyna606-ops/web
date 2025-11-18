<?php
// Conexión con la base de datos para leer parámetros del sistema
// __DIR__ = carpeta donde está este archivo (includes)
include_once(__DIR__ . "/../conexion.php");

$config_footer = null;

if ($conn) {
    $sql_footer   = "SELECT * FROM PARAMETROS LIMIT 1";
    $result_footer = mysqli_query($conn, $sql_footer);
    if ($result_footer) {
        $config_footer = mysqli_fetch_assoc($result_footer);
    }
}
?>

<footer class="text-center text-white mt-5" style="background-color: #146c43; padding: 25px 0;">
    <?php if ($config_footer): ?>
        <h5 class="fw-bold mb-2"><?php echo htmlspecialchars($config_footer['NombreClinica']); ?></h5>
        <p class="mb-1"><?php echo htmlspecialchars($config_footer['Direccion']); ?></p>
        <p class="mb-1">
            📞 <?php echo htmlspecialchars($config_footer['Telefono']); ?> |
            📧 <?php echo htmlspecialchars($config_footer['Correo']); ?>
        </p>
        <p class="mb-2">🕓 <?php echo htmlspecialchars($config_footer['Horario']); ?></p>
    <?php else: ?>
        <h5 class="fw-bold mb-2">Veterinaria Mi Mascota</h5>
        <p class="mb-1">Av. Central #123, Monterrey, NL</p>
        <p class="mb-1">📞 8123456789 | 📧 contacto@mimascota.com</p>
        <p class="mb-2">🕓 Lunes a Sábado de 9:00 a 18:00</p>
    <?php endif; ?>

    <hr class="mx-auto" style="width: 80%; border-color: rgba(255,255,255,0.3);">
    <p class="mb-0">
        © <?php echo date("Y"); ?>
        <?php echo $config_footer ? htmlspecialchars($config_footer['NombreClinica']) : 'Veterinaria Mi Mascota'; ?> |
        Todos los derechos reservados 🐾
    </p>
</footer>
