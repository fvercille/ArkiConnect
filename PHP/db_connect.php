<?php
$host     = getenv('MYSQLHOST') ?: 'mysql.railway.internal';
$dbname   = getenv('MYSQLDATABASE') ?: 'railway';
$username = getenv('MYSQLUSER') ?: 'root';
$password = getenv('MYSQLPASSWORD') ?: 'dUHYDXuiEtCKqpIquUEWabwyOiEhHgMy';
$port     = (int)(getenv('MYSQLPORT') ?: '3306');

// Debug
echo "PASS LENGTH: " . strlen($password) . " | FIRST 3 CHARS: " . substr($password, 0, 3);
die();
?>