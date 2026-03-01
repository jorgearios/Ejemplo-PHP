-- --------------------------------------------------------
-- Tabla: especialidades
-- --------------------------------------------------------

CREATE TABLE `especialidades` (
  `id_especialidad` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `especialidades`
  ADD PRIMARY KEY (`id_especialidad`);

ALTER TABLE `especialidades`
  MODIFY `id_especialidad` int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------
-- Tabla: profesores
-- --------------------------------------------------------

CREATE TABLE `profesores` (
  `no_empleado` varchar(20) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `id_especialidad` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `profesores`
  ADD PRIMARY KEY (`no_empleado`),
  ADD KEY `id_especialidad` (`id_especialidad`);

ALTER TABLE `profesores`
  ADD CONSTRAINT `fk_profesor_especialidad` FOREIGN KEY (`id_especialidad`) REFERENCES `especialidades` (`id_especialidad`);
