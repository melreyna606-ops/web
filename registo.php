<?php
include("conexion.php");

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre   = trim($_POST["nombre"] ?? '');
    $correo   = trim($_POST["correo"] ?? '');
    $password = $_POST["password"] ?? '';
    $rol      = $_POST["rol"] ?? '';

    if ($nombre !== '' && $correo !== '' && $password !== '' && $rol !== '') {

        // 1️⃣ Verificar si ya existe el correo
        $sqlCheck  = "SELECT 1 FROM USUARIOS_LOGIN WHERE Correo = ? LIMIT 1";
        $stmtCheck = mysqli_prepare($conn, $sqlCheck);

        if ($stmtCheck) {
            mysqli_stmt_bind_param($stmtCheck, "s", $correo);
            mysqli_stmt_execute($stmtCheck);
            $resultCheck = mysqli_stmt_get_result($stmtCheck);

            if ($resultCheck && mysqli_num_rows($resultCheck) > 0) {
                $mensaje = "<div class='alert alert-warning'>⚠️ El correo ya está registrado.</div>";
            } else {
                // 2️⃣ Generar hash seguro de la contraseña
                $hash = password_hash($password, PASSWORD_BCRYPT);

                // 3️⃣ Insertar usuario
                $sql  = "INSERT INTO USUARIOS_LOGIN (Nombre, Correo, Password, Rol, FechaRegistro)
                         VALUES (?, ?, ?, ?, NOW())";
                $stmt = mysqli_prepare($conn, $sql);

                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "ssss", $nombre, $correo, $hash, $rol);
                    $ok = mysqli_stmt_execute($stmt);

                    if ($ok) {
                        $mensaje = "<div class='alert alert-success'>✅ Usuario registrado correctamente. Ya puedes iniciar sesión.</div>";
                    } else {
                        $mensaje = "<div class='alert alert-danger'>❌ Error al registrar usuario.</div>";
                        // $mensaje .= "<pre>" . mysqli_error($conn) . "</pre>"; // descomenta para debug
                    }

                    mysqli_stmt_close($stmt);
                } else {
                    $mensaje = "<div class='alert alert-danger'>❌ Error al preparar el registro de usuario.</div>";
                }
            }

            mysqli_stmt_close($stmtCheck);
        } else {
            $mensaje = "<div class='alert alert-danger'>❌ Error al preparar la verificación del correo.</div>";
        }

    } else {
        $mensaje = "<div class='alert alert-warning'>⚠️ Completa todos los campos.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Registro de Usuario - Veterinaria</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
  background: linear-gradient(120deg, #95d5b2, #74c69d);
  font-family: "Poppins", sans-serif;
}
.container {
  max-width: 450px;
  margin: 80px auto;
  background: #fff;
  padding: 35px;
  border-radius: 15px;
  box-shadow: 0px 10px 25px rgba(0,0,0,0.1);
}
.btn-register {
  background-color: #2d6a4f;
  color: white;
  border: none;
  border-radius: 10px;
  padding: 10px;
  width: 100%;
  font-weight: bold;
}
.btn-register:hover {
  background-color: #1b4332;
}
a {
  color: #2d6a4f;
  text-decoration: none;
  font-weight: 500;
}
</style>
</head>
<body>
<div class="container">
  <h3 class="text-center mb-3">🐾 Veterinaria Mi Mascota</h3>
  <h5 class="text-center mb-4">Crear Cuenta</h5>

  <?= $mensaje ?>

  <form method="POST">
    <div class="mb-3">
      <label class="form-label">Nombre completo</label>
      <input type="text" name="nombre" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Correo electrónico</label>
      <input type="email" name="correo" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Contraseña</label>
      <input type="password" name="password" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Rol</label>
      <select name="rol" class="form-select" required>
        <option value="">Seleccione un rol...</option>
        <option value="Administrador">Administrador</option>
        <option value="Editor">Editor</option>
        <option value="Consultor">Consultor</option>
      </select>
    </div>
    <button class="btn-register" type="submit">Crear Cuenta</button>
  </form>

  <p class="text-center mt-3">¿Ya tienes cuenta?
    <a href="index.php">Inicia sesión aquí</a>
  </p>
</div>
</body>
</html>
