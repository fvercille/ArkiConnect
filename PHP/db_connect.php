<?php
$host     = 'mysql.railway.internal';
$dbname   = getenv('MYSQLDATABASE') ?: 'railway';
$username = 'root';
$password = getenv('MYSQL_ROOT_PASSWORD') ?: getenv('MYSQLPASSWORD');
$port     = (int)(getenv('MYSQLPORT') ?: '3306');

mysqli_report(MYSQLI_REPORT_OFF);
$conn = new mysqli($host, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>