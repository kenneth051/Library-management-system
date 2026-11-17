<?php

$host = 'localhost';
$dbUser = 'root';
$dbPassword = '';
$dbName = 'library_db';

$conn = new mysqli($host, $dbUser, $dbPassword, $dbName);

if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}
