<?php
$host     = getenv('MYSQLHOST') ?: 'mysql.railway.internal';
$dbname   = getenv('MYSQLDATABASE') ?: 'railway';
$username = getenv('MYSQLUSER') ?: 'root';
$password = getenv('MYSQLPASSWORD') ?: '${{MySQL.MYSQLPASSWORD}}';
$port     = (int)(getenv('MYSQLPORT') ?: '3306');

mysqli_report(MYSQLI_REPORT_OFF);
$conn = new mysqli();
$conn->real_connect($host, $username, $password, $dbname, $port, null, MYSQLI_CLIENT_SSL);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>