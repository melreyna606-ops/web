<?php
// ============================================
// Archivo: conexion.php
// Descripción: Conexión a MySQL en InfinityFree
// Base de datos: if0_40377057_clinica_veterinaria
// ============================================

// Datos de MySQL (los que ves en el panel)
// Para usar phpMyAdmin / MySQL local (XAMPP) cambia estos valores.
// En XAMPP lo habitual es host = '127.0.0.1' o 'localhost', user = 'root' y password = '' (vacía).
$host     = 'localhost';
$user     = 'root';
$password = ''; // Si tu root tiene contraseña, ponla aquí
$dbname   = 'clinica_vet'; // Ajusta al nombre de la BD que importaste en phpMyAdmin

// Habilitar reporte de errores como excepciones y conectar (mejor manejo de errores)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $conn = mysqli_connect($host, $user, $password, $dbname);
    // Forzar UTF-8 (acentos, ñ, etc.)
    mysqli_set_charset($conn, 'utf8mb4');
} catch (mysqli_sql_exception $e) {
    // Registrar el detalle en el log del servidor y mostrar mensaje genérico al usuario
    error_log('DB connection error: ' . $e->getMessage());
    http_response_code(500);
    die('❌ Error de conexión a la base de datos. Revisa la configuración en `conexion.php`.');
}

// Si quieres probar que conecta, puedes descomentar esta línea:
// echo "<p style='color: green;'>✔️ Conexión exitosa a MySQL.</p>";
?>
