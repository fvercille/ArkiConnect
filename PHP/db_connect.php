<?php
$host     = getenv('MYSQLHOST') ?: 'mysql.railway.internal';
$dbname   = getenv('MYSQLDATABASE') ?: 'railway';
$username = getenv('MYSQLUSER') ?: 'root';
$password = getenv('MYSQLPASSWORD') ?: '${{MySQL.MYSQLPASSWORD}}';
$port     = getenv('MYSQLPORT') ?: '3306';

// Debug - tanggalin pagkatapos
echo "HOST: $host | DB: $dbname | USER: $username | PORT: $port | PASS: " . (empty($password) ? 'EMPTY' : 'HAS VALUE');
die();

$conn = new mysqli($host, $username, $password, $dbname, (int)$port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>