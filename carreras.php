<?php
require_once 'config.php';

// CRUD de carreras:
// - Crea y actualiza por POST
// - Elimina y carga datos a editar por GET
// - Muestra formulario + listado final
function redirigirCarreras($mensaje)
{
  header('Location: carreras.php?msg=' . urlencode($mensaje));
  exit;
}

// 1) Guardar (alta/edicion) de carrera
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id_carrera = isset($_POST['id_carrera']) ? (int)$_POST['id_carrera'] : 0;
  $nombre = trim($_POST['nombre'] ?? '');

  if ($nombre === '') {
    redirigirCarreras('El nombre de la carrera es obligatorio.');
  }

  // Si llega id_carrera, actualiza; en caso contrario, inserta.
  if ($id_carrera > 0) {
    $stmt = $conexion->prepare('UPDATE carreras SET nombre = ? WHERE id_carrera = ?');
    $stmt->bind_param('si', $nombre, $id_carrera);
    $ok = $stmt->execute();
    $stmt->close();
    redirigirCarreras($ok ? 'Carrera actualizada.' : 'No se pudo actualizar la carrera.');
  }
  else {
    $stmt = $conexion->prepare('INSERT INTO carreras (nombre) VALUES (?)');
    $stmt->bind_param('s', $nombre);
    $ok = $stmt->execute();
    $stmt->close();
    redirigirCarreras($ok ? 'Carrera agregada.' : 'No se pudo agregar la carrera (nombre duplicado).');
  }
}

// 2) Eliminar carrera
if (isset($_GET['accion']) && $_GET['accion'] === 'eliminar' && isset($_GET['id_carrera'])) {
  $id_carrera = (int)$_GET['id_carrera'];
  if ($id_carrera > 0) {
    $stmt = $conexion->prepare('DELETE FROM carreras WHERE id_carrera = ?');
    $stmt->bind_param('i', $id_carrera);
    $ok = $stmt->execute();
    $stmt->close();
    redirigirCarreras($ok ? 'Carrera eliminada.' : 'No se pudo eliminar la carrera. Si tiene alumnos relacionados, eliminarlos o reasignarlos primero.');
  }
}

// Estructura por defecto del formulario (modo agregar)
$carreraEditar = [
  'id_carrera' => 0,
  'nombre' => ''
];

// 3) Cargar carrera en modo edicion
if (isset($_GET['accion']) && $_GET['accion'] === 'editar' && isset($_GET['id_carrera'])) {
  $id_carrera = (int)$_GET['id_carrera'];
  $stmt = $conexion->prepare('SELECT id_carrera, nombre FROM carreras WHERE id_carrera = ?');
  $stmt->bind_param('i', $id_carrera);
  $stmt->execute();
  $resultado = $stmt->get_result();
  if ($fila = $resultado->fetch_assoc()) {
    $carreraEditar = $fila;
  }
  $stmt->close();
}

// 4) Listado principal de carreras
$listado = $conexion->query('SELECT id_carrera, nombre FROM carreras ORDER BY id_carrera DESC');
?>











<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD Carreras</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>AdminLTE 4 | General Form Elements</title>
    <!--begin::Accessibility Meta Tags-->
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
</head>
<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
  <div class="app-wrapper">
    <nav class="app-header navbar navbar-expand bg-body">
        <!--begin::Container-->
        <div class="container-fluid">
          
              <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                
      </nav>
      <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
                  <!--begin::Sidebar Brand-->
        <div class="sidebar-brand">
          <!--begin::Brand Link-->
          <a href="../index.html" class="brand-link">
            <!--begin::Brand Image-->
            <img
              src="../assets/img/AdminLTELogo.png"
              alt="AdminLTE Logo"
              class="brand-image opacity-75 shadow"
            />
            <!--end::Brand Image-->
            <!--begin::Brand Text-->
            <span class="brand-text fw-light">AdminLTE 4</span>
            <!--end::Brand Text-->
          </a>
          <!--end::Brand Link-->
        </div>

        <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="nav-icon bi bi-speedometer"></i>
                  <p>
                    Dashboard
                    <i class="nav-arrow bi bi-chevron-right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="../index.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Dashboard v1</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="../index2.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Dashboard v2</p>
                    </a>
                  </li>
        <!--end::Sidebar Brand-->
        <!--begin::Sidebar Wrapper-->
        
        <!--end::Sidebar Wrapper-->
      </aside>
      <!--begin::App Main-->
      <main class="app-main">
        <!--begin::App Content Header-->
        <div class="app-content-header">
          <!--begin::Container-->
          <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">
              <div class="col-sm-6">
    
    <!--begin::App Content-->
        <div class="app-content">
          <!--begin::Container-->
          <div class="container-fluid">
            <!--begin::Row-->
            <div class="row g-4">
    <h1>CRUD de Carreras</h1>

    <div class="card-body">

    <?php if (isset($_GET['msg'])): ?>
        <p><strong><?php echo htmlspecialchars($_GET['msg']); ?></strong></p>
    <?php
endif; ?>

    </div>
    <form method="post" action="carreras.php">
        <h2><?php echo $carreraEditar['id_carrera'] > 0 ? 'Editar Carrera' : 'Agregar Carrera'; ?></h2>
        <input type="hidden" name="id_carrera" value="<?php echo (int)$carreraEditar['id_carrera']; ?>">

        <label>Nombre de la carrera</label>
        <input type="text" class="form-control" name="nombre" required value="<?php echo htmlspecialchars((string)$carreraEditar['nombre']); ?>">

        <button class= "btn btn-primary" type="submit"><?php echo $carreraEditar['id_carrera'] > 0 ? 'Guardar Cambios' : 'Agregar'; ?></button>
        <?php if ($carreraEditar['id_carrera'] > 0): ?>
            <a href="carreras.php">Cancelar</a>
        <?php
endif; ?>
    </form>
</main>
<div class="app-content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
    <h2>Listado de Carreras</h2>
    <table class="table table-striped">
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
                    <td><?php echo (int)$c['id_carrera']; ?></td>
                    <td><?php echo htmlspecialchars($c['nombre']); ?></td>
                    <td class="acciones">
                        <a href="carreras.php?accion=editar&id_carrera=<?php echo (int)$c['id_carrera']; ?>">Editar</a>
                        <a href="carreras.php?accion=eliminar&id_carrera=<?php echo (int)$c['id_carrera']; ?>" onclick="return confirm('Eliminar carrera?');">Eliminar</a>
                    </td>
                </tr>
            <?php
endwhile; ?>
        </tbody>
    </table>
  </div></div></div>
</body> 
</html>