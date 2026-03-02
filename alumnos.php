<?php
require_once 'config.php';

// CRUD de alumnos:
// - Crea y actualiza por POST
// - Elimina y carga datos a editar por GET
// - Muestra formulario + listado final
function redirigir($mensaje)
{
    header('Location: alumnos.php?msg=' . urlencode($mensaje));
    exit;
}

// 1) Guardar (alta/edicion) de alumno
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $matriculaOriginal = trim($_POST['matricula_original'] ?? '');
    $matricula = trim($_POST['matricula'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $id_carrera = isset($_POST['id_carrera']) ? (int)$_POST['id_carrera'] : 0;
    $email = trim($_POST['email'] ?? '');
    $promedio_general = isset($_POST['promedio_general']) ? (float)$_POST['promedio_general'] : -1;

    if ($matricula === '' || $nombre === '' || $id_carrera <= 0 || $email === '' || $promedio_general < 0 || $promedio_general > 10) {
        redirigir('Datos invalidos. Verifica los campos.');
    }

    // Si existe matricula original, se trata de una edicion.
    if ($matriculaOriginal !== '') {
        $stmt = $conexion->prepare('UPDATE alumnos SET matricula = ?, nombre = ?, id_carrera = ?, email = ?, promedio_general = ? WHERE matricula = ?');
        $stmt->bind_param('ssisss', $matricula, $nombre, $id_carrera, $email, $promedio_general, $matriculaOriginal);
        $ok = $stmt->execute();
        $stmt->close();
        redirigir($ok ? 'Alumno actualizado.' : 'No se pudo actualizar el alumno.');
    } else {
        // Alta de nuevo alumno.
        $stmt = $conexion->prepare('INSERT INTO alumnos (matricula, id_carrera, nombre, email, promedio_general) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('sissd', $matricula, $id_carrera, $nombre, $email, $promedio_general);
        $ok = $stmt->execute();
        $stmt->close();
        redirigir($ok ? 'Alumno agregado.' : 'No se pudo agregar el alumno. Revisa matricula unica y carrera valida.');
    }
}

// 2) Eliminar alumno por matricula
if (isset($_GET['accion']) && $_GET['accion'] === 'eliminar' && isset($_GET['matricula'])) {
    $matricula = trim($_GET['matricula']);
    if ($matricula !== '') {
        $stmt = $conexion->prepare('DELETE FROM alumnos WHERE matricula = ?');
        $stmt->bind_param('s', $matricula);
        $ok = $stmt->execute();
        $stmt->close();
        redirigir($ok ? 'Alumno eliminado.' : 'No se pudo eliminar el alumno.');
    }
}

// Datos de apoyo para el select de carreras en el formulario
$carreras = $conexion->query('SELECT id_carrera, nombre FROM carreras ORDER BY nombre ASC');

// Estructura por defecto del formulario (modo agregar)
$alumnoEditar = [
    'matricula_original' => '',
'matricula' => '',
'nombre' => '',
'id_carrera' => 0,
'email' => '',
'promedio_general' => ''
];

// 3) Cargar alumno en modo edicion
if (isset($_GET['accion']) && $_GET['accion'] === 'editar' && isset($_GET['matricula'])) {
    $matricula = trim($_GET['matricula']);
    if ($matricula !== '') {
        $stmt = $conexion->prepare('SELECT matricula, id_carrera, nombre, email, promedio_general FROM alumnos WHERE matricula = ?');
        $stmt->bind_param('s', $matricula);
        $stmt->execute();
        $resultado = $stmt->get_result();
        if ($fila = $resultado->fetch_assoc()) {
            $fila['matricula_original'] = $fila['matricula'];
            $alumnoEditar = $fila;
        }
        $stmt->close();
    }
}

// 4) Listado principal de alumnos con nombre de carrera
$listado = $conexion->query('SELECT a.matricula, a.nombre, c.nombre AS carrera, a.email, a.promedio_general
FROM alumnos a
INNER JOIN carreras c ON c.id_carrera = a.id_carrera
ORDER BY a.matricula DESC');
?>






<!doctype html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CRUD Alumnos</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
<meta name="color-scheme" content="light dark" />
<meta name="theme-color" content="#007bff" media="(prefers-color-scheme: light)" />
<meta name="theme-color" content="#1a1a1a" media="(prefers-color-scheme: dark)" />
<!--end::Accessibility Meta Tags-->
<!--begin::Primary Meta Tags-->
<meta name="title" content="AdminLTE 4 | General Form Elements" />
<meta name="author" content="ColorlibHQ" />
<meta
name="description"
content="AdminLTE is a Free Bootstrap 5 Admin Dashboard, 30 example pages using Vanilla JS. Fully accessible with WCAG 2.1 AA compliance."
/>
<meta
name="keywords"
content="bootstrap 5, bootstrap, bootstrap 5 admin dashboard, bootstrap 5 dashboard, bootstrap 5 charts, bootstrap 5 calendar, bootstrap 5 datepicker, bootstrap 5 tables, bootstrap 5 datatable, vanilla js datatable, colorlibhq, colorlibhq dashboard, colorlibhq admin dashboard, accessible admin panel, WCAG compliant"
/>
<!--end::Primary Meta Tags-->
<!--begin::Accessibility Features-->
<!-- Skip links will be dynamically added by accessibility.js -->
<meta name="supported-color-schemes" content="light dark" />
<link rel="preload" href="css/adminlte.css" as="style" />
<!--end::Accessibility Features-->
<!--begin::Fonts-->
<link
rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q="
crossorigin="anonymous"
media="print"
onload="this.media='all'"
/>
<!--end::Fonts-->
<!--begin::Third Party Plugin(OverlayScrollbars)-->
<link
rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css"
crossorigin="anonymous"
/>
<!--end::Third Party Plugin(OverlayScrollbars)-->
<!--begin::Third Party Plugin(Bootstrap Icons)-->
<link
rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
crossorigin="anonymous"
/>
<!--end::Third Party Plugin(Bootstrap Icons)-->
<!--begin::Required Plugin(AdminLTE)-->
<link rel="stylesheet" href="css/adminlte.css" />
<!--end::Required Plugin(AdminLTE)-->
</head>
<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
<div class="app-wrapper">
<nav class="app-header navbar navbar-expand bg-body">
<!--begin::Container-->
<div class="container-fluid">
<!--begin::Start Navbar Links-->
<!--end::Start Navbar Links-->
<!--begin::End Navbar Links-->
<ul class="navbar-nav ms-auto">
<!--begin::Navbar Search-->

<!--end::Navbar Search-->
<!--begin::Messages Dropdown Menu-->
<li class="nav-item dropdown">
<div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">

</li>
<!--end::Notifications Dropdown Menu-->
<!--begin::Fullscreen Toggle-->

</li>
<!--end::Fullscreen Toggle-->
<!--begin::User Menu Dropdown-->

</ul>
</div>
</nav>
  <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
        <!--begin::Sidebar Brand-->
        <div class="sidebar-brand">
          <!--begin::Brand Link-->
          <a href="index.html" class="brand-link">
            <!--begin::Brand Image-->
           
            <!--end::Brand Image-->
            <!--begin::Brand Text-->
            <span class="brand-text fw-light">Ejemplo</span>
            <!--end::Brand Text-->
          </a>
          <!--end::Brand Link-->
        </div>
        <!--end::Sidebar Brand-->
        <!--begin::Sidebar Wrapper-->
        <div class="sidebar-wrapper">
          <nav class="mt-2">
            <!--begin::Sidebar Menu-->
            <ul
              class="nav sidebar-menu flex-column"
              data-lte-toggle="treeview"
              role="navigation"
              aria-label="Main navigation"
              data-accordion="false"
              id="navigation"
            >
            <li class="nav-item">
                <a href="carreras.php" class="nav-link">
                  <i class="nav-icon"></i>
                  <p>Ir a CRUD Carreras</p>
                </a>
                 </li>
              <li class="nav-item">
                <a href="alumnos.php" class="nav-link">
                  <i class="nav-icon"></i>
                  <p>Ir a CRUD Alumnos</p>
                </a>
                 </li>
                  <li class="nav-item">
                <a href="especialidades.php" class="nav-link">
                  <i class="nav-icon"></i>
                  <p>Ir a CRUD Especialidades</p>
                </a>
</li>
 <li class="nav-item">
                <a href="profesores.php" class="nav-link">
                  <i class="nav-icon"></i>
                  <p>Ir a CRUD Profesores</p>
                </a>
</li>
              
            </ul>
            <!--end::Sidebar Menu-->
          </nav>
        </div>
        <!--end::Sidebar Wrapper-->
      </aside>
      <main class="app-main">
        <!--begin::App Content Header-->
        <div class="app-content-header">
          <!--begin::Container-->
          <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">
              <div class="col-sm-6"><h3 class="mb-0">CRUD de alumnos</h3></div>
              <div class="col-sm-6">
               
              </div>
            </div>
            <!--end::Row-->
          </div>
          <!--end::Container-->
        </div>
        <!--end::App Content Header-->
        <!--begin::App Content-->
        <div class="app-content">
          <!--begin::Container-->
          <div class="container-fluid">
            <!--begin::Row-->
            <div class="row g-4">
              <div class="col-md-6">
                <!--begin::Quick Example-->
                <div class="card card-primary card-outline mb-4">
                  <!--end::Header-->
                  <!--begin::Form-->
                  <form method="post" action="alumnos.php">
                    <div class="card-header"><div class="card-title"><?php echo $alumnoEditar['matricula_original'] !== '' ? 'Editar Alumno' : 'Agregar Alumno'; ?></div></div>

    <input type="hidden" name="matricula_original" value="<?php echo htmlspecialchars((string)$alumnoEditar['matricula_original']); ?>">
<div class="card-body">
   
    <label>Matricula</label>
    <input type="text" class="form-control" name="matricula" required value="<?php echo htmlspecialchars((string)$alumnoEditar['matricula']); ?>">

</div>
<div class="card-body">
    <label>Nombre</label>
    <input type="text" class="form-control" name="nombre" required value="<?php echo htmlspecialchars((string)$alumnoEditar['nombre']); ?>">
</div>
<div class="card-body">
    <label>Carrera</label>
    <select  class="form-select" name="id_carrera" required>
    <option value="">Selecciona una carrera</option>
    <?php while ($c = $carreras->fetch_assoc()): ?>
    <option value="<?php echo (int)$c['id_carrera']; ?>" <?php echo ((int)$alumnoEditar['id_carrera'] === (int)$c['id_carrera']) ? 'selected' : ''; ?>>
    <?php echo htmlspecialchars($c['nombre']); ?>
    </option>
    <?php endwhile; ?>
    </select>
    </div>
    <div class="card-body">
    <label >Correo electronico</label>
    <input type="email" class="form-control" name="email" required value="<?php echo htmlspecialchars((string)$alumnoEditar['email']); ?>">
    </div>
    <div class="card-body">
    <label>Promedio general (0 - 10)</label>
    <input type="number" class="form-control" step="0.01" min="0" max="10" name="promedio_general" required value="<?php echo htmlspecialchars((string)$alumnoEditar['promedio_general']); ?>">
    </div>
     <div class="card-footer">
    <button type="submit" class="btn btn-primary"><?php echo $alumnoEditar['matricula_original'] !== '' ? 'Guardar Cambios' : 'Agregar'; ?></button>
    </div>
    <?php if ($alumnoEditar['matricula_original'] !== ''): ?>
    <a class="btn btn-danger" href="alumnos.php">Cancelar</a>
    <?php endif; ?>
</form>
                  <!--end::Form-->
                </div>
                <!--end::Quick Example-->
             
      </main>
<div class="app-content">

 <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">
                <div class="card mb-2">
        <h2>Listado de Alumnos Registrados</h2>
        <table class="table table-bordered">
        <thead>
        <tr>
        <th>Matricula</th>
        <th>Nombre</th>
        <th>Carrera</th>
        <th>Email</th>
        <th>Promedio</th>
        <th>Acciones</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($a = $listado->fetch_assoc()): ?>
        <tr>
        <td><?php echo htmlspecialchars($a['matricula']); ?></td>
        <td><?php echo htmlspecialchars($a['nombre']); ?></td>
        <td><?php echo htmlspecialchars($a['carrera']); ?></td>
        <td><?php echo htmlspecialchars($a['email']); ?></td>
        <td><?php echo htmlspecialchars((string)$a['promedio_general']); ?></td>
        <td class="acciones">
        <a class="btn btn-primary" href="alumnos.php?accion=editar&matricula=<?php echo urlencode($a['matricula']); ?>">Editar</a>
        <a class="btn btn-danger" href="alumnos.php?accion=eliminar&matricula=<?php echo urlencode($a['matricula']); ?>" onclick="return confirm('Eliminar alumno?');">Eliminar</a>
        </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
        </table>
    </div>
        </div>
        
</div>
</div>
</div>
</body>
</html>
