<?php
$host     = getenv('MYSQLHOST') ?: 'mysql.railway.internal';
$dbname   = getenv('MYSQLDATABASE') ?: 'railway';
$username = getenv('MYSQLUSER') ?: 'root';
$password = getenv('MYSQLPASSWORD') ?: '';
$port     = getenv('MYSQLPORT') ?: '3306';

$conn = new mysqli($host, $username, $password, $dbname, (int)$port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>