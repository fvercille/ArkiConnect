<?php
$server_root = $_SERVER['DOCUMENT_ROOT'];
$file_path = __FILE__;

// Ibigay lang ang part ng path na kailangan mo (mula sa htdocs pababa)
$project_path = str_replace($server_root, '', $file_path);

// I-print ang path na kailangan mo
echo "<h1>Ito ang dapat mong gamitin sa header() function:</h1>";
echo "<p style='font-size: 1.5em; color: red;'><strong>" . str_replace('test_path.php', 'login.php', $project_path) . "</strong></p>";
echo "<hr>";

// Para sa ABSOLUTE URL
$host = $_SERVER['HTTP_HOST'];
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";
$full_url = $protocol . $host . str_replace('test_path.php', 'login.php', $project_path);

echo "<h1>Ito ang FULL URL na dapat gumana:</h1>";
echo "<p style='font-size: 1.5em; color: blue;'><strong>" . $full_url . "</strong></p>";
?>