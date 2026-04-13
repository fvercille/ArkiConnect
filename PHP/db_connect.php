<?php
$host     = 'mysql.railway.internal';
$dbname   = 'railway';
$username = 'root';
$password = 'dUHYDXuiEtCKqpIquUEWabwyOiEhHgMy'; 
$port     = 3306;

mysqli_report(MYSQLI_REPORT_OFF);
$conn = new mysqli($host, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>