<?php
require_once 'config.php';

// CRUD de alumnos
function redirigirAlumnos($mensaje)
{
  header('Location: alumnos.php?msg=' . urlencode($mensaje));
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id_alumno = isset($_POST['id_alumno']) ? (int)$_POST['id_alumno'] : 0;
  $nombre = trim($_POST['nombre'] ?? '');

  if ($nombre === '') {
    redirigirAlumnos('El nombre del alumno es obligatorio.');
  }

  if ($id_alumno > 0) {
    $stmt = $conexion->prepare('UPDATE alumnos SET nombre = ? WHERE id_alumno = ?');
    $stmt->bind_param('si', $nombre, $id_alumno);
    $ok = $stmt->execute();
    $stmt->close();
    redirigirAlumnos($ok ? 'Alumno actualizado.' : 'No se pudo actualizar el alumno.');
  }
  else {
    $stmt = $conexion->prepare('INSERT INTO alumnos (nombre) VALUES (?)');
    $stmt->bind_param('s', $nombre);
    $ok = $stmt->execute();
    $stmt->close();
    redirigirAlumnos($ok ? 'Alumno agregado.' : 'No se pudo agregar el alumno.');
  }
}

if (isset($_GET['accion']) && $_GET['accion'] === 'eliminar' && isset($_GET['id_alumno'])) {
  $id_alumno = (int)$_GET['id_alumno'];
  if ($id_alumno > 0) {
    $stmt = $conexion->prepare('DELETE FROM alumnos WHERE id_alumno = ?');
    $stmt->bind_param('i', $id_alumno);
    $ok = $stmt->execute();
    $stmt->close();
    redirigirAlumnos($ok ? 'Alumno eliminado.' : 'No se pudo eliminar el alumno.');
  }
}

$alumnoEditar = ['id_alumno' => 0, 'nombre' => ''];

if (isset($_GET['accion']) && $_GET['accion'] === 'editar' && isset($_GET['id_alumno'])) {
  $id_alumno = (int)$_GET['id_alumno'];
  $stmt = $conexion->prepare('SELECT id_alumno, nombre FROM alumnos WHERE id_alumno = ?');
  $stmt->bind_param('i', $id_alumno);
  $stmt->execute();
  $resultado = $stmt->get_result();
  if ($fila = $resultado->fetch_assoc()) {
    $alumnoEditar = $fila;
  }
  $stmt->close();
}

$listado = $conexion->query('SELECT id_alumno, nombre FROM alumnos ORDER BY id_alumno DESC');
?>
<!doctype html>
<html lang="es">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>AdminLTE 4 | CRUD Alumnos</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" crossorigin="anonymous" />
    <link rel="stylesheet" href="../css/adminlte.css" />
  </head>
  <body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
    <div class="app-wrapper">
      
      <nav class="app-header navbar navbar-expand bg-body">
        <div class="container-fluid">
          <ul class="navbar-nav">
            <li class="nav-item">
              <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button"><i class="bi bi-list"></i></a>
            </li>
            <li class="nav-item d-none d-md-block"><a href="index.php" class="nav-link">Inicio</a></li>
          </ul>
        </div>
      </nav>

      <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
        <div class="sidebar-brand">
          <a href="index.php" class="brand-link">
            <span class="brand-text fw-light">Mi Escuela App</span>
          </a>
        </div>
        <div class="sidebar-wrapper">
          <nav class="mt-2">
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="navigation" data-accordion="false">
              <li class="nav-header">MENÚ PRINCIPAL</li>
              <li class="nav-item">
                <a href="carreras.php" class="nav-link">
                  <i class="nav-icon bi bi-journal-bookmark-fill"></i>
                  <p>CRUD Carreras</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="alumnos.php" class="nav-link active">
                  <i class="nav-icon bi bi-people-fill"></i>
                  <p>CRUD Alumnos</p>
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
              <div class="col-sm-6"><h3 class="mb-0">Gestión de Alumnos</h3></div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                  <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Alumnos</li>
                </ol>
              </div>
            </div>
          </div>
        </div>

        <div class="app-content">
          <div class="container-fluid">
            <?php if (isset($_GET['msg'])): ?>
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <strong>Aviso:</strong> <?php echo htmlspecialchars($_GET['msg']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php
endif; ?>

            <div class="row g-4">
              <div class="col-md-4">
                <div class="card card-primary card-outline mb-4">
                  <div class="card-header">
                    <h3 class="card-title"><?php echo $alumnoEditar['id_alumno'] > 0 ? 'Editar Alumno' : 'Agregar Alumno'; ?></h3>
                  </div>
                  <form method="post" action="alumnos.php">
                    <div class="card-body">
                      <input type="hidden" name="id_alumno" value="<?php echo (int)$alumnoEditar['id_alumno']; ?>">
                      <div class="mb-3">
                        <label for="nombreAlumno" class="form-label">Nombre del alumno</label>
                        <input type="text" class="form-control" id="nombreAlumno" name="nombre" required value="<?php echo htmlspecialchars((string)$alumnoEditar['nombre']); ?>">
                      </div>
                    </div>
                    <div class="card-footer">
                      <button type="submit" class="btn btn-primary"><?php echo $alumnoEditar['id_alumno'] > 0 ? 'Guardar Cambios' : 'Agregar'; ?></button>
                      <?php if ($alumnoEditar['id_alumno'] > 0): ?>
                          <a href="alumnos.php" class="btn btn-secondary">Cancelar</a>
                      <?php
endif; ?>
                    </div>
                  </form>
                </div>
              </div>

              <div class="col-md-8">
                <div class="card mb-4">
                  <div class="card-header">
                    <h3 class="card-title">Listado de Alumnos</h3>
                  </div>
                  <div class="card-body p-0">
                    <table class="table table-striped table-hover">
                      <thead>
                        <tr>
                          <th style="width: 50px">ID</th>
                          <th>Nombre</th>
                          <th style="width: 200px">Acciones</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php while ($a = $listado->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo (int)$a['id_alumno']; ?></td>
                                <td><?php echo htmlspecialchars($a['nombre']); ?></td>
                                <td>
                                    <a href="alumnos.php?accion=editar&id_alumno=<?php echo (int)$a['id_alumno']; ?>" class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil-square"></i> Editar
                                    </a>
                                    <a href="alumnos.php?accion=eliminar&id_alumno=<?php echo (int)$a['id_alumno']; ?>" onclick="return confirm('¿Estás seguro de eliminar este alumno?');" class="btn btn-sm btn-danger">
                                        <i class="bi bi-trash"></i> Eliminar
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
            </div>
          </div>
        </div>
      </main>
      
      <footer class="app-footer">
        <strong>Copyright &copy; 2024 Mi Escuela.</strong> Todos los derechos reservados.
      </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
    <script src="../js/adminlte.js"></script>
  </body>
</html>