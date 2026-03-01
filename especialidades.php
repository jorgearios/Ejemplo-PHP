<?php
require_once 'config.php';

function redirigirEspecialidades($mensaje)
{
  header('Location: especialidades.php?msg=' . urlencode($mensaje));
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id_especialidad = isset($_POST['id_especialidad']) ? (int)$_POST['id_especialidad'] : 0;
  $nombre = trim($_POST['nombre'] ?? '');

  if ($nombre === '') {
    redirigirEspecialidades('El nombre de la especialidad es obligatorio.');
  }

  if ($id_especialidad > 0) {
    $stmt = $conexion->prepare('UPDATE especialidades SET nombre = ? WHERE id_especialidad = ?');
    $stmt->bind_param('si', $nombre, $id_especialidad);
    $ok = $stmt->execute();
    $stmt->close();
    redirigirEspecialidades($ok ? 'Especialidad actualizada.' : 'No se pudo actualizar la especialidad.');
  } else {
    $stmt = $conexion->prepare('INSERT INTO especialidades (nombre) VALUES (?)');
    $stmt->bind_param('s', $nombre);
    $ok = $stmt->execute();
    $stmt->close();
    redirigirEspecialidades($ok ? 'Especialidad agregada.' : 'No se pudo agregar la especialidad (nombre duplicado).');
  }
}

if (isset($_GET['accion']) && $_GET['accion'] === 'eliminar' && isset($_GET['id_especialidad'])) {
  $id_especialidad = (int)$_GET['id_especialidad'];
  if ($id_especialidad > 0) {
    $stmt = $conexion->prepare('DELETE FROM especialidades WHERE id_especialidad = ?');
    $stmt->bind_param('i', $id_especialidad);
    $ok = $stmt->execute();
    $stmt->close();
    redirigirEspecialidades($ok ? 'Especialidad eliminada.' : 'No se pudo eliminar la especialidad. Si tiene profesores relacionados, eliminarlos primero.');
  }
}

$especialidadEditar = ['id_especialidad' => 0, 'nombre' => ''];

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

$listado = $conexion->query('SELECT id_especialidad, nombre FROM especialidades ORDER BY id_especialidad DESC');
?>
<!doctype html>
<html lang="es">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>CRUD Especialidades | AdminLTE 4</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
  <meta name="color-scheme" content="light dark" />
  <meta name="theme-color" content="#007bff" media="(prefers-color-scheme: light)" />
  <meta name="theme-color" content="#1a1a1a" media="(prefers-color-scheme: dark)" />
  <meta name="supported-color-schemes" content="light dark" />
  <link rel="preload" href="css/adminlte.css" as="style" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css" integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q=" crossorigin="anonymous" media="print" onload="this.media='all'" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css" crossorigin="anonymous" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" crossorigin="anonymous" />
  <link rel="stylesheet" href="css/adminlte.css" />
</head>
<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
  <div class="app-wrapper">

    <nav class="app-header navbar navbar-expand bg-body">
      <div class="container-fluid">
        <ul class="navbar-nav">
          <li class="nav-item">
            <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
              <i class="bi bi-list"></i>
            </a>
          </li>
        </ul>
      </div>
    </nav>

    <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
      <div class="sidebar-brand">
        <a href="../index.html" class="brand-link">
          <img src="../assets/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image opacity-75 shadow" />
          <span class="brand-text fw-light">AdminLTE 4</span>
        </a>
      </div>
      <div class="sidebar-wrapper">
        <nav class="mt-2">
          <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="navigation" aria-label="Main navigation" data-accordion="false">
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
              <a href="especialidades.php" class="nav-link active">
                <i class="nav-icon bi bi-bookmark-star-fill"></i>
                <p>Especialidades</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="profesores.php" class="nav-link">
                <i class="nav-icon bi bi-person-workspace"></i>
                <p>Profesores</p>
              </a>
            </li>
          </ul>
        </nav>
      </div>
    </aside>

    <main class="app-main">

      <div class="app-content-header">
        <div class="container-fluid">
          <div class="row">
            <div class="col-sm-6">
              <h3 class="mb-0">CRUD de Especialidades</h3>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="#">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Especialidades</li>
              </ol>
            </div>
          </div>
        </div>
      </div>

      <div class="app-content">
        <div class="container-fluid">
          <div class="row g-4">

            <div class="col-8">
              <div class="card card-primary card-outline mb-4">
                <div class="card-header">
                  <div class="card-title">
                    <?php echo $especialidadEditar['id_especialidad'] > 0 ? 'Editar Especialidad' : 'Agregar Especialidad'; ?>
                  </div>
                </div>
                <form method="post" action="especialidades.php">
                  <div class="card-body">
                    <?php if (isset($_GET['msg'])): ?>
                      <div class="alert alert-info">
                        <?php echo htmlspecialchars($_GET['msg']); ?>
                      </div>
                    <?php endif; ?>

                    <input type="hidden" name="id_especialidad" value="<?php echo (int)$especialidadEditar['id_especialidad']; ?>">

                    <div class="mb-3">
                      <label class="form-label">Nombre de la especialidad</label>
                      <input type="text" class="form-control" name="nombre" required
                        value="<?php echo htmlspecialchars((string)$especialidadEditar['nombre']); ?>">
                    </div>
                  </div>
                  <div class="card-footer">
                    <button class="btn btn-primary" type="submit">
                      <?php echo $especialidadEditar['id_especialidad'] > 0 ? 'Guardar Cambios' : 'Agregar'; ?>
                    </button>
                    <?php if ($especialidadEditar['id_especialidad'] > 0): ?>
                      <a href="especialidades.php" class="btn btn-secondary ms-2">Cancelar</a>
                    <?php endif; ?>
                  </div>
                </form>
              </div>
            </div>

            <div class="col-8">
              <div class="card card-outline mb-4">
                <div class="card-header">
                  <div class="card-title">Listado de Especialidades</div>
                </div>
                <div class="card-body">
                  <table class="table table-striped">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Acciones</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php while ($e = $listado->fetch_assoc()): ?>
                        <tr>
                          <td><?php echo (int)$e['id_especialidad']; ?></td>
                          <td><?php echo htmlspecialchars($e['nombre']); ?></td>
                          <td>
                            <a href="especialidades.php?accion=editar&id_especialidad=<?php echo (int)$e['id_especialidad']; ?>" class="btn btn-sm btn-warning">
                              <i class="bi bi-pencil-fill"></i> Editar
                            </a>
                            <a href="especialidades.php?accion=eliminar&id_especialidad=<?php echo (int)$e['id_especialidad']; ?>"
                               class="btn btn-sm btn-danger ms-1"
                               onclick="return confirm('¿Eliminar especialidad?');">
                              <i class="bi bi-trash-fill"></i> Eliminar
                            </a>
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
      </div>

    </main>

    <footer class="app-footer">
      <strong>Copyright &copy; 2014-2025&nbsp;<a href="https://adminlte.io" class="text-decoration-none">AdminLTE.io</a>.</strong>
      All rights reserved.
    </footer>

  </div>

  <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
  <script src="js/adminlte.js"></script>
  <script>
    const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
    const Default = { scrollbarTheme: 'os-theme-light', scrollbarAutoHide: 'leave', scrollbarClickScroll: true };
    document.addEventListener('DOMContentLoaded', function () {
      const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);
      if (sidebarWrapper && OverlayScrollbarsGlobal?.OverlayScrollbars !== undefined) {
        OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
          scrollbars: { theme: Default.scrollbarTheme, autoHide: Default.scrollbarAutoHide, clickScroll: Default.scrollbarClickScroll },
        });
      }
    });
  </script>

</body>
</html>
