<?php
require_once 'config.php';

// CRUD de profesores:
// - Crea y actualiza por POST
// - Elimina y carga datos a editar por GET
// - Muestra formulario + listado final
function redirigir($mensaje)
{
  header('Location: profesores.php?msg=' . urlencode($mensaje));
  exit;
}

// 1) Guardar (alta/edicion) de profesor
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $noEmpleadoOriginal = trim($_POST['noEmpleado_original'] ?? '');
  $noEmpleado = trim($_POST['noEmpleado'] ?? '');
  $nombre = trim($_POST['nombre'] ?? '');
  $id_especialidad = isset($_POST['id_especialidad']) ? (int)$_POST['id_especialidad'] : 0;
  $email = trim($_POST['email'] ?? '');
  $telefono = isset($_POST['telefono']) ? (string)$_POST['telefono'] : -1;

  if ($noEmpleado === '' || $nombre === '' || $id_especialidad <= 0 || $email === '') {
    redirigir('Datos invalidos. Verifica los campos.');
  }

  // Si existe noEmpleado original, se trata de una edicion.
  if ($noEmpleadoOriginal !== '') {
    $stmt = $conexion->prepare('UPDATE profesores SET noEmpleado = ?, nombre = ?, id_especialidad = ?, email = ?, telefono = ? WHERE noEmpleado = ?');
    $stmt->bind_param('isissi', $noEmpleado, $nombre, $id_especialidad, $email, $telefono, $noEmpleadoOriginal);
    $ok = $stmt->execute();
    $stmt->close();
    redirigir($ok ? 'profesor actualizado.' : 'No se pudo actualizar el profesor.');
  }
  else {
    // Alta de nuevo profesor.
    $stmt = $conexion->prepare('INSERT INTO profesores (noEmpleado, id_especialidad, nombre, email, telefono) VALUES (?, ?, ?, ?, ?)');
    $stmt->bind_param('iisss', $noEmpleado, $id_especialidad, $nombre, $email, $telefono);
    $ok = $stmt->execute();
    $stmt->close();
    redirigir($ok ? 'profesor agregado.' : 'No se pudo agregar el profesor. Revisa noEmpleado unica y carrera valida.');
  }
}

// 2) Eliminar profesor por noEmpleado
if (isset($_GET['accion']) && $_GET['accion'] === 'eliminar' && isset($_GET['noEmpleado'])) {
  $noEmpleado = trim($_GET['noEmpleado']);
  if ($noEmpleado !== '') {
    $stmt = $conexion->prepare('DELETE FROM profesores WHERE noEmpleado = ?');
    $stmt->bind_param('s', $noEmpleado);
    $ok = $stmt->execute();
    $stmt->close();
    redirigir($ok ? 'profesor eliminado.' : 'No se pudo eliminar el profesor.');
  }
}

// Datos de apoyo para el select de especialidades en el formulario
$especialidades = $conexion->query('SELECT id_especialidad, nombre FROM especialidades ORDER BY nombre ASC');

// Estructura por defecto del formulario (modo agregar)
$profesorEditar = [
  'noEmpleado_original' => '', 'noEmpleado' => '', 'nombre' => '', 'id_especialidad' => 0, 'email' => '', 'telefono' => ''
];

// 3) Cargar profesor en modo edicion
if (isset($_GET['accion']) && $_GET['accion'] === 'editar' && isset($_GET['noEmpleado'])) {
  $noEmpleado = trim($_GET['noEmpleado']);
  if ($noEmpleado !== '') {
    $stmt = $conexion->prepare('SELECT noEmpleado, id_especialidad, nombre, email, telefono FROM profesores WHERE noEmpleado = ?');
    $stmt->bind_param('s', $noEmpleado);
    $stmt->execute();
    $resultado = $stmt->get_result();
    if ($fila = $resultado->fetch_assoc()) {
      $fila['noEmpleado_original'] = $fila['noEmpleado'];
      $profesorEditar = $fila;
    }
    $stmt->close();
  }
}

// 4) Listado principal de profesores con nombre de carrera
$listado = $conexion->query('SELECT a.noEmpleado, a.nombre, c.nombre AS carrera, a.email, a.telefono
FROM profesores a
INNER JOIN especialidades c ON c.id_especialidad = a.id_especialidad
ORDER BY a.noEmpleado DESC');
?>






<!doctype html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CRUD profesores</title>
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
            <span class="brand-text fw-light">Menu</span>
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
                  <p>Carreras</p>
                </a>
                 </li>
              <li class="nav-item">
                <a href="alumnos.php" class="nav-link">
                  <i class="nav-icon"></i>
                  <p>Alumnos</p>
                </a>
                 </li>
                  <li class="nav-item">
                <a href="especialidades.php" class="nav-link">
                  <i class="nav-icon"></i>
                  <p>Especialidades</p>
                </a>
</li>
 <li class="nav-item">
                <a href="profesores.php" class="nav-link">
                  <i class="nav-icon"></i>
                  <p>Profesores</p>
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
              <div class="col-sm-6"><h3 class="mb-0">CRUD de profesores</h3></div>
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
                  <form method="post" action="profesores.php">
                    <div class="card-header"><div class="card-title"><?php echo $profesorEditar['noEmpleado_original'] !== '' ? 'Editar profesor' : 'Agregar profesor'; ?></div></div>

    <input type="hidden" name="noEmpleado_original" value="<?php echo htmlspecialchars((string)$profesorEditar['noEmpleado_original']); ?>">
<div class="card-body">
   
    <label>Numero de empleado</label>
    <input type="text" class="form-control" name="noEmpleado" required value="<?php echo htmlspecialchars((string)$profesorEditar['noEmpleado']); ?>">

</div>
<div class="card-body">
    <label>Nombre</label>
    <input type="text" class="form-control" name="nombre" required value="<?php echo htmlspecialchars((string)$profesorEditar['nombre']); ?>">
</div>
<div class="card-body">
    <label>Especialidad</label>
    <select  class="form-select" name="id_especialidad" required>
    <option value="">Selecciona una especialidad</option>
    <?php while ($c = $especialidades->fetch_assoc()): ?>
    <option value="<?php echo (int)$c['id_especialidad']; ?>" <?php echo((int)$profesorEditar['id_especialidad'] === (int)$c['id_especialidad']) ? 'selected' : ''; ?>>
    <?php echo htmlspecialchars($c['nombre']); ?>
    </option>
    <?php
endwhile; ?>
    </select>
    </div>
    <div class="card-body">
    <label >Correo electronico</label>
    <input type="email" class="form-control" name="email" required value="<?php echo htmlspecialchars((string)$profesorEditar['email']); ?>">
    </div>
    <div class="card-body">
    <label>Telefono</label>
    <input type="number" class="form-control" step="0.01" name="telefono" required value="<?php echo htmlspecialchars((string)$profesorEditar['telefono']); ?>">
    </div>
     <div class="card-footer">
    <button type="submit" class="btn btn-primary"><?php echo $profesorEditar['noEmpleado_original'] !== '' ? 'Guardar Cambios' : 'Agregar'; ?></button>
    </div>
    <?php if ($profesorEditar['noEmpleado_original'] !== ''): ?>
    <a class="btn btn-danger" href="profesores.php">Cancelar</a>
    <?php
endif; ?>
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
        <h2>Listado de profesores Registrados</h2>
        <table class="table table-bordered">
        <thead>
        <tr>
        <th>noEmpleado</th>
        <th>Nombre</th>
        <th>Carrera</th>
        <th>Email</th>
        <th>telefono</th>
        <th>Acciones</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($a = $listado->fetch_assoc()): ?>
        <tr>
        <td><?php echo htmlspecialchars($a['noEmpleado']); ?></td>
        <td><?php echo htmlspecialchars($a['nombre']); ?></td>
        <td><?php echo htmlspecialchars($a['carrera']); ?></td>
        <td><?php echo htmlspecialchars($a['email']); ?></td>
        <td><?php echo htmlspecialchars((string)$a['telefono']); ?></td>
        <td class="acciones">
        <a class="btn btn-primary" href="profesores.php?accion=editar&noEmpleado=<?php echo urlencode($a['noEmpleado']); ?>">Editar</a>
        <a class="btn btn-danger" href="profesores.php?accion=eliminar&noEmpleado=<?php echo urlencode($a['noEmpleado']); ?>" onclick="return confirm('Eliminar profesor?');">Eliminar</a>
        </td>
        </tr>
        <?php
endwhile; ?>
        </tbody>
        </table>
    </div>
        </div>
        
</div>
</div>
</div>
</body>
</html>
