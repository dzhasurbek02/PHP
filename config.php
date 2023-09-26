<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "bookdb";

try {
    $conn = new mysqli($host, $username, $password, $database);
    if ($conn->connect_error) {
        throw new Exception("Ошибка подключения к базе данных: " . $conn->connect_error);
    }
} catch (Exception $e) {
    echo "Произошла ошибка: " . $e->getMessage();
}
