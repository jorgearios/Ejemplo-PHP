<?php
require_once 'config.php';

function redirigirProfesores($mensaje)
{
  header('Location: profesores.php?msg=' . urlencode($mensaje));
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $no_empleado = trim($_POST['no_empleado'] ?? '');
  $noEmpleadoOrig = trim($_POST['no_empleado_original'] ?? '');
  $nombre = trim($_POST['nombre'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $telefono = trim($_POST['telefono'] ?? '');
  $id_especialidad = isset($_POST['id_especialidad']) ? (int)$_POST['id_especialidad'] : 0;

  if ($no_empleado === '' || $nombre === '' || $email === '' || $telefono === '' || $id_especialidad <= 0) {
    redirigirProfesores('Todos los campos son obligatorios.');
  }

  if ($noEmpleadoOrig !== '') {
    $stmt = $conexion->prepare('UPDATE profesores SET no_empleado = ?, nombre = ?, email = ?, telefono = ?, id_especialidad = ? WHERE no_empleado = ?');
    $stmt->bind_param('ssssiss', $no_empleado, $nombre, $email, $telefono, $id_especialidad, $noEmpleadoOrig);
    $ok = $stmt->execute();
    $stmt->close();
    redirigirProfesores($ok ? 'Profesor actualizado.' : 'No se pudo actualizar el profesor.');
  }
  else {
    $stmt = $conexion->prepare('INSERT INTO profesores (no_empleado, nombre, email, telefono, id_especialidad) VALUES (?, ?, ?, ?, ?)');
    $stmt->bind_param('ssssi', $no_empleado, $nombre, $email, $telefono, $id_especialidad);
    $ok = $stmt->execute();
    $stmt->close();
    redirigirProfesores($ok ? 'Profesor agregado.' : 'No se pudo agregar el profesor. Verifica que el No. de empleado sea único.');
  }
}

if (isset($_GET['accion']) && $_GET['accion'] === 'eliminar' && isset($_GET['no_empleado'])) {
  $no_empleado = trim($_GET['no_empleado']);
  if ($no_empleado !== '') {
    $stmt = $conexion->prepare('DELETE FROM profesores WHERE no_empleado = ?');
    $stmt->bind_param('s', $no_empleado);
    $ok = $stmt->execute();
    $stmt->close();
    redirigirProfesores($ok ? 'Profesor eliminado.' : 'No se pudo eliminar el profesor.');
  }
}

$especialidades = $conexion->query('SELECT id_especialidad, nombre FROM especialidades ORDER BY nombre ASC');

$profesorEditar = [
  'no_empleado_original' => '',
  'no_empleado' => '',
  'nombre' => '',
  'email' => '',
  'telefono' => '',
  'id_especialidad' => 0,
];

if (isset($_GET['accion']) && $_GET['accion'] === 'editar' && isset($_GET['no_empleado'])) {
  $no_empleado = trim($_GET['no_empleado']);
  if ($no_empleado !== '') {
    $stmt = $conexion->prepare('SELECT no_empleado, nombre, email, telefono, id_especialidad FROM profesores WHERE no_empleado = ?');
    $stmt->bind_param('s', $no_empleado);
    $stmt->execute();
    $resultado = $stmt->get_result();
    if ($fila = $resultado->fetch_assoc()) {
      $fila['no_empleado_original'] = $fila['no_empleado'];
      $profesorEditar = $fila;
    }
    $stmt->close();
  }
}

$listado = $conexion->query('SELECT p.no_empleado, p.nombre, p.email, p.telefono, e.nombre AS especialidad
  FROM profesores p
  INNER JOIN especialidades e ON e.id_especialidad = p.id_especialidad
  ORDER BY p.no_empleado DESC');
?>
<!doctype html>
<html lang="es">
<!--begin::Head-->
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>CRUD Profesores | AdminLTE 4</title>
  <!--begin::Accessibility Meta Tags-->
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
  <meta name="color-scheme" content="light dark" />
  <meta name="theme-color" content="#007bff" media="(prefers-color-scheme: light)" />
  <meta name="theme-color" content="#1a1a1a" media="(prefers-color-scheme: dark)" />
  <!--end::Accessibility Meta Tags-->
  <meta name="supported-color-schemes" content="light dark" />
  <link rel="preload" href="css/adminlte.css" as="style" />
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
<!--end::Head-->
<!--begin::Body-->
<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
  <!--begin::App Wrapper-->
  <div class="app-wrapper">

    <!--begin::Header-->
    <nav class="app-header navbar navbar-expand bg-body">
      <div class="container-fluid">
        <!--begin::Start Navbar Links-->
        <ul class="navbar-nav">
          <li class="nav-item">
            <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
              <i class="bi bi-list"></i>
            </a>
          </li>
        </ul>
        <!--end::Start Navbar Links-->
      </div>
    </nav>
    <!--end::Header-->

    <!--begin::Sidebar-->
    <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
      <!--begin::Sidebar Brand-->
      <div class="sidebar-brand">
        <a href="../index.html" class="brand-link">
          <img
            src="../assets/img/AdminLTELogo.png"
            alt="AdminLTE Logo"
            class="brand-image opacity-75 shadow"
          />
          <span class="brand-text fw-light">AdminLTE 4</span>
        </a>
      </div>
      <!--end::Sidebar Brand-->
      <!--begin::Sidebar Wrapper-->
      <div class="sidebar-wrapper">
        <nav class="mt-2">
          <ul
            class="nav sidebar-menu flex-column"
            data-lte-toggle="treeview"
            role="navigation"
            aria-label="Main navigation"
            data-accordion="false"
          >
            <li class="nav-item">
              <a href="carreras.php" class="nav-link">
                <i class="nav-icon bi bi-mortarboard-fill"></i>
                <p>Carreras</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="alumnos.php" class="nav-link">
                <i class="nav-icon bi bi-people-fill"></i>
                <p>Alumnos</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="especialidades.php" class="nav-link">
                <i class="nav-icon bi bi-bookmark-star-fill"></i>
                <p>Especialidades</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="profesores.php" class="nav-link active">
                <i class="nav-icon bi bi-person-workspace"></i>
                <p>Profesores</p>
              </a>
            </li>
          </ul>
        </nav>
      </div>
      <!--end::Sidebar Wrapper-->
    </aside>
    <!--end::Sidebar-->

    <!--begin::App Main-->
    <main class="app-main">

      <!--begin::App Content Header-->
      <div class="app-content-header">
        <div class="container-fluid">
          <div class="row">
            <div class="col-sm-6">
              <h3 class="mb-0">CRUD de Profesores</h3>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="#">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Profesores</li>
              </ol>
            </div>
          </div>
        </div>
      </div>
      <!--end::App Content Header-->

      <!--begin::App Content-->
      <div class="app-content">
        <div class="container-fluid">
          <div class="row g-4">

            <!--begin::Col Formulario-->
            <div class="col-8">
              <div class="card card-primary card-outline mb-4">
                <div class="card-header">
                  <div class="card-title">
                    <?php echo $profesorEditar['no_empleado_original'] !== '' ? 'Editar Profesor' : 'Agregar Profesor'; ?>
                  </div>
                </div>
                <form method="post" action="profesores.php">
                  <div class="card-body">

                    <?php if (isset($_GET['msg'])): ?>
                      <div class="alert alert-info">
                        <?php echo htmlspecialchars($_GET['msg']); ?>
                      </div>
                    <?php
endif; ?>

                    <input type="hidden" name="no_empleado_original" value="<?php echo htmlspecialchars((string)$profesorEditar['no_empleado_original']); ?>">

                    <div class="mb-3">
                      <label class="form-label">No. de Empleado</label>
                      <input type="text" class="form-control" name="no_empleado" required
                        value="<?php echo htmlspecialchars((string)$profesorEditar['no_empleado']); ?>">
                    </div>

                    <div class="mb-3">
                      <label class="form-label">Nombre</label>
                      <input type="text" class="form-control" name="nombre" required
                        value="<?php echo htmlspecialchars((string)$profesorEditar['nombre']); ?>">
                    </div>

                    <div class="mb-3">
                      <label class="form-label">Email</label>
                      <input type="email" class="form-control" name="email" required
                        value="<?php echo htmlspecialchars((string)$profesorEditar['email']); ?>">
                    </div>

                    <div class="mb-3">
                      <label class="form-label">Teléfono</label>
                      <input type="text" class="form-control" name="telefono" required
                        value="<?php echo htmlspecialchars((string)$profesorEditar['telefono']); ?>">
                    </div>

                    <div class="mb-3">
                      <label class="form-label">Especialidad</label>
                      <select class="form-select" name="id_especialidad" required>
                        <option value="">Selecciona una especialidad</option>
                        <?php while ($e = $especialidades->fetch_assoc()): ?>
                          <option value="<?php echo (int)$e['id_especialidad']; ?>"
                            <?php echo((int)$profesorEditar['id_especialidad'] === (int)$e['id_especialidad']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($e['nombre']); ?>
                          </option>
                        <?php
endwhile; ?>
                      </select>
                    </div>

                  </div>
                  <div class="card-footer">
                    <button class="btn btn-primary" type="submit">
                      <?php echo $profesorEditar['no_empleado_original'] !== '' ? 'Guardar Cambios' : 'Agregar'; ?>
                    </button>
                    <?php if ($profesorEditar['no_empleado_original'] !== ''): ?>
                      <a href="profesores.php" class="btn btn-secondary ms-2">Cancelar</a>
                    <?php
endif; ?>
                  </div>
                </form>
              </div>
            </div>
            <!--end::Col Formulario-->

            <!--begin::Col Listado-->
            <div class="col-8">
              <div class="card card-outline mb-4">
                <div class="card-header">
                  <div class="card-title">Listado de Profesores</div>
                </div>
                <div class="card-body">
                  <table class="table table-striped">
                    <thead>
                      <tr>
                        <th>No. Empleado</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Especialidad</th>
                        <th>Acciones</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php while ($p = $listado->fetch_assoc()): ?>
                        <tr>
                          <td><?php echo htmlspecialchars($p['no_empleado']); ?></td>
                          <td><?php echo htmlspecialchars($p['nombre']); ?></td>
                          <td><?php echo htmlspecialchars($p['email']); ?></td>
                          <td><?php echo htmlspecialchars($p['telefono']); ?></td>
                          <td><?php echo htmlspecialchars($p['especialidad']); ?></td>
                          <td>
                            <a href="profesores.php?accion=editar&no_empleado=<?php echo urlencode($p['no_empleado']); ?>" class="btn btn-sm btn-warning">
                              <i class="bi bi-pencil-fill"></i> Editar
                            </a>
                            <a href="profesores.php?accion=eliminar&no_empleado=<?php echo urlencode($p['no_empleado']); ?>"
                               class="btn btn-sm btn-danger ms-1"
                               onclick="return confirm('¿Eliminar profesor?');">
                              <i class="bi bi-trash-fill"></i> Eliminar
                            </a>
                          </td>
                        </tr>
                      <?php
endwhile; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
            <!--end::Col Listado-->

          </div>
        </div>
      </div>
      <!--end::App Content-->

    </main>
    <!--end::App Main-->

    <!--begin::Footer-->
    <footer class="app-footer">
      <strong>Copyright &copy; 2014-2025&nbsp;<a href="https://adminlte.io" class="text-decoration-none">AdminLTE.io</a>.</strong>
      All rights reserved.
    </footer>
    <!--end::Footer-->

  </div>
  <!--end::App Wrapper-->

  <!--begin::Scripts-->
  <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
  <script src="js/adminlte.js"></script>
  <script>
    const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
    const Default = {
      scrollbarTheme: 'os-theme-light',
      scrollbarAutoHide: 'leave',
      scrollbarClickScroll: true,
    };
    document.addEventListener('DOMContentLoaded', function () {
      const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);
      if (sidebarWrapper && OverlayScrollbarsGlobal?.OverlayScrollbars !== undefined) {
        OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
          scrollbars: {
            theme: Default.scrollbarTheme,
            autoHide: Default.scrollbarAutoHide,
            clickScroll: Default.scrollbarClickScroll,
          },
        });
      }
    });
  </script>
  <!--end::Scripts-->

</body>
<!--end::Body-->
</html>