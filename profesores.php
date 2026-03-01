<?php
require_once 'config.php';

function redirigirAlumnos($mensaje)
{
  header('Location: alumnos.php?msg=' . urlencode($mensaje));
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $matriculaOriginal = trim($_POST['matricula_original'] ?? '');
  $matricula         = trim($_POST['matricula'] ?? '');
  $nombre            = trim($_POST['nombre'] ?? '');
  $id_carrera        = isset($_POST['id_carrera']) ? (int)$_POST['id_carrera'] : 0;
  $email             = trim($_POST['email'] ?? '');
  $promedio_general  = isset($_POST['promedio_general']) ? (float)$_POST['promedio_general'] : -1;

  if ($matricula === '' || $nombre === '' || $id_carrera <= 0 || $email === '' || $promedio_general < 0 || $promedio_general > 10) {
    redirigirAlumnos('Datos inválidos. Verifica todos los campos.');
  }

  if ($matriculaOriginal !== '') {
    $stmt = $conexion->prepare('UPDATE alumnos SET matricula = ?, nombre = ?, id_carrera = ?, email = ?, promedio_general = ? WHERE matricula = ?');
    $stmt->bind_param('ssisss', $matricula, $nombre, $id_carrera, $email, $promedio_general, $matriculaOriginal);
    $ok = $stmt->execute();
    $stmt->close();
    redirigirAlumnos($ok ? 'Alumno actualizado.' : 'No se pudo actualizar el alumno.');
  } else {
    $stmt = $conexion->prepare('INSERT INTO alumnos (matricula, id_carrera, nombre, email, promedio_general) VALUES (?, ?, ?, ?, ?)');
    $stmt->bind_param('sissd', $matricula, $id_carrera, $nombre, $email, $promedio_general);
    $ok = $stmt->execute();
    $stmt->close();
    redirigirAlumnos($ok ? 'Alumno agregado.' : 'No se pudo agregar el alumno. Revisa matrícula única y carrera válida.');
  }
}

if (isset($_GET['accion']) && $_GET['accion'] === 'eliminar' && isset($_GET['matricula'])) {
  $matricula = trim($_GET['matricula']);
  if ($matricula !== '') {
    $stmt = $conexion->prepare('DELETE FROM alumnos WHERE matricula = ?');
    $stmt->bind_param('s', $matricula);
    $ok = $stmt->execute();
    $stmt->close();
    redirigirAlumnos($ok ? 'Alumno eliminado.' : 'No se pudo eliminar el alumno.');
  }
}

$carreras = $conexion->query('SELECT id_carrera, nombre FROM carreras ORDER BY nombre ASC');

$alumnoEditar = [
  'matricula_original' => '',
  'matricula'          => '',
  'nombre'             => '',
  'id_carrera'         => 0,
  'email'              => '',
  'promedio_general'   => ''
];

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

$listado = $conexion->query('SELECT a.matricula, a.nombre, c.nombre AS carrera, a.email, a.promedio_general
  FROM alumnos a
  INNER JOIN carreras c ON c.id_carrera = a.id_carrera
  ORDER BY a.matricula DESC');
?>
<!doctype html>
<html lang="es">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>CRUD Alumnos | AdminLTE 4</title>
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
              <a href="alumnos.php" class="nav-link active">
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
              <h3 class="mb-0">CRUD de Alumnos</h3>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="#">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Alumnos</li>
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
                    <?php echo $alumnoEditar['matricula_original'] !== '' ? 'Editar Alumno' : 'Agregar Alumno'; ?>
                  </div>
                </div>
                <form method="post" action="alumnos.php">
                  <div class="card-body">
                    <?php if (isset($_GET['msg'])): ?>
                      <div class="alert alert-info">
                        <?php echo htmlspecialchars($_GET['msg']); ?>
                      </div>
                    <?php endif; ?>

                    <input type="hidden" name="matricula_original" value="<?php echo htmlspecialchars((string)$alumnoEditar['matricula_original']); ?>">

                    <div class="mb-3">
                      <label class="form-label">Matrícula</label>
                      <input type="text" class="form-control" name="matricula" required
                        value="<?php echo htmlspecialchars((string)$alumnoEditar['matricula']); ?>">
                    </div>

                    <div class="mb-3">
                      <label class="form-label">Nombre</label>
                      <input type="text" class="form-control" name="nombre" required
                        value="<?php echo htmlspecialchars((string)$alumnoEditar['nombre']); ?>">
                    </div>

                    <div class="mb-3">
                      <label class="form-label">Carrera</label>
                      <select class="form-select" name="id_carrera" required>
                        <option value="">Selecciona una carrera</option>
                        <?php while ($c = $carreras->fetch_assoc()): ?>
                          <option value="<?php echo (int)$c['id_carrera']; ?>"
                            <?php echo ((int)$alumnoEditar['id_carrera'] === (int)$c['id_carrera']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($c['nombre']); ?>
                          </option>
                        <?php endwhile; ?>
                      </select>
                    </div>

                    <div class="mb-3">
                      <label class="form-label">Email</label>
                      <input type="email" class="form-control" name="email" required
                        value="<?php echo htmlspecialchars((string)$alumnoEditar['email']); ?>">
                    </div>

                    <div class="mb-3">
                      <label class="form-label">Promedio general (0 - 10)</label>
                      <input type="number" class="form-control" step="0.01" min="0" max="10"
                        name="promedio_general" required
                        value="<?php echo htmlspecialchars((string)$alumnoEditar['promedio_general']); ?>">
                    </div>

                  </div>
                  <div class="card-footer">
                    <button class="btn btn-primary" type="submit">
                      <?php echo $alumnoEditar['matricula_original'] !== '' ? 'Guardar Cambios' : 'Agregar'; ?>
                    </button>
                    <?php if ($alumnoEditar['matricula_original'] !== ''): ?>
                      <a href="alumnos.php" class="btn btn-secondary ms-2">Cancelar</a>
                    <?php endif; ?>
                  </div>
                </form>
              </div>
            </div>

            <div class="col-8">
              <div class="card card-outline mb-4">
                <div class="card-header">
                  <div class="card-title">Listado de Alumnos</div>
                </div>
                <div class="card-body">
                  <table class="table table-striped">
                    <thead>
                      <tr>
                        <th>Matrícula</th>
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
                          <td>
                            <a href="alumnos.php?accion=editar&matricula=<?php echo urlencode($a['matricula']); ?>" class="btn btn-sm btn-warning">
                              <i class="bi bi-pencil-fill"></i> Editar
                            </a>
                            <a href="alumnos.php?accion=eliminar&matricula=<?php echo urlencode($a['matricula']); ?>"
                               class="btn btn-sm btn-danger ms-1"
                               onclick="return confirm('¿Eliminar alumno?');">
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
