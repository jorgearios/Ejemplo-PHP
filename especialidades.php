<?php
require_once 'config.php';

// CRUD de especialidades:
// - Crea y actualiza por POST
// - Elimina y carga datos a editar por GET
// - Muestra formulario + listado final
function redirigirespecialidades($mensaje)
{
  header('Location: especialidades.php?msg=' . urlencode($mensaje));
  exit;
}

// 1) Guardar (alta/edicion) de especialidad
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id_especialidad = isset($_POST['id_especialidad']) ? (int)$_POST['id_especialidad'] : 0;
  $nombre = trim($_POST['nombre'] ?? '');

  if ($nombre === '') {
    redirigirespecialidades('El nombre de la especialidad es obligatorio.');
  }

  // Si llega id_especialidad, actualiza; en caso contrario, inserta.
  if ($id_especialidad > 0) {
    $stmt = $conexion->prepare('UPDATE especialidades SET nombre = ? WHERE id_especialidad = ?');
    $stmt->bind_param('si', $nombre, $id_especialidad);
    $ok = $stmt->execute();
    $stmt->close();
    redirigirespecialidades($ok ? 'Especialidad actualizada.' : 'No se pudo actualizar la especialidad.');
  }
  else {
    $stmt = $conexion->prepare('INSERT INTO especialidades (nombre) VALUES (?)');
    $stmt->bind_param('s', $nombre);
    $ok = $stmt->execute();
    $stmt->close();
    redirigirespecialidades($ok ? 'Especialidad agregada.' : 'No se pudo agregar la especialidad (nombre duplicado).');
  }
}

// 2) Eliminar especialidad
if (isset($_GET['accion']) && $_GET['accion'] === 'eliminar' && isset($_GET['id_especialidad'])) {
  $id_especialidad = (int)$_GET['id_especialidad'];
  if ($id_especialidad > 0) {
    $stmt = $conexion->prepare('DELETE FROM especialidades WHERE id_especialidad = ?');
    $stmt->bind_param('i', $id_especialidad);
    $ok = $stmt->execute();
    $stmt->close();
    redirigirespecialidades($ok ? 'especialidad eliminada.' : 'No se pudo eliminar la especialidad. Si tiene alumnos relacionados, eliminarlos o reasignarlos primero.');
  }
}

// Estructura por defecto del formulario (modo agregar)
$especialidadEditar = [
  'id_especialidad' => 0,  'nombre' => ''
];

// 3) Cargar especialidad en modo edicion
if (isset($_GET['accion']) && $_GET['accion'] === 'editar' && isset($_GET['id_especialidad'])) {
  $id_especialidad = (int)$_GET['id_especialidad'];
  $stmt = $conexion->prepare('SELECT id_especialidad, nombre FROM especialidades WHERE id_especialidad = ?');
  $stmt->bind_param('i', $id_especialidad);
  $stmt->execute();
  $resultado = $stmt->get_result();
  if ($fila = $resultado->fetch_assoc()) {
    $especialidadEditar = $fila;
  }
  $stmt->close();
}

// 4) Listado principal de especialidades
$listado = $conexion->query('SELECT id_especialidad, nombre FROM especialidades ORDER BY id_especialidad DESC');
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CRUD especialidades</title>
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
              <div class="col-sm-6"><h3 class="mb-0">CRUD de especialidades</h3></div>
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
                  <form method="post" action="especialidades.php">
                    
                  



<div class="card-body">

<?php if (isset($_GET['msg'])): ?>
<p><strong><?php echo htmlspecialchars($_GET['msg']); ?></strong></p>
<?php
endif; ?>
<div class="card-body">

<h2 class="card-header" ><div class="card-title"><?php echo $especialidadEditar['id_especialidad'] > 0 ? 'Editar especialidad' : 'Agregar especialidad'; ?></h2></div>
<input type="hidden" name="id_especialidad" value="<?php echo (int)$especialidadEditar['id_especialidad']; ?>">
<div class="card-body">
<label>Nombre de la especialidad</label>
<input type="text" class="form-control"  name="nombre" required value="<?php echo htmlspecialchars((string)$especialidadEditar['nombre']); ?>">
</div>
<button class="btn btn-primary" type="submit"><?php echo $especialidadEditar['id_especialidad'] > 0 ? 'Guardar Cambios' : 'Agregar'; ?></button>
<?php if ($especialidadEditar['id_especialidad'] > 0): ?>
<a class="btn btn-danger" href="especialidades.php">Cancelar</a>
<?php
endif; ?>
</form>
   </main>
   <div class="app-content">

 <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">
                <div class="card mb-2">
       <h2>Listado de especialidades</h2>
        <table class="table table-bordered">
       <thead>
        <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Acciones</th>
        </tr>
        </thead>
          <tbody>
          <?php while ($c = $listado->fetch_assoc()): ?>
          <tr>
          <td><?php echo (int)$c['id_especialidad']; ?></td>
          <td><?php echo htmlspecialchars($c['nombre']); ?></td>
          <td class="acciones">
          <a class="btn btn-primary" href="especialidades.php?accion=editar&id_especialidad=<?php echo (int)$c['id_especialidad']; ?>">Editar</a>
          <a class="btn btn-danger" href="especialidades.php?accion=eliminar&id_especialidad=<?php echo (int)$c['id_especialidad']; ?>" onclick="return confirm('Eliminar especialidad?');">Eliminar</a>
          </td>
          </tr>
          <?php
endwhile; ?>
          </tbody>
        </table>
</div>
        </div>
        
</div>
</body>
</html>
