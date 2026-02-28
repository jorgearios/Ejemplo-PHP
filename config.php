<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'escuela';

$conexion = new mysqli($host, $user, $pass, $dbname);

if ($conexion->connect_error) {
    die('Error de conexion: ' . $conexion->connect_error);
}

$conexion->set_charset('utf8mb4');