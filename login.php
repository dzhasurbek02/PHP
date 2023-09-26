<?php
session_start();
include("config.php");

try {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = $_POST["username"];
        $password = $_POST["password"];

        $sql = "SELECT * FROM users WHERE username='$username'";
        $result = $conn->query($sql);

        if ($result) {
            if ($result->num_rows == 1) {
                $row = $result->fetch_assoc();
                if (password_verify($password, $row["password"])) {
                    $_SESSION["username"] = $username;
                    header("Location: dashboard.php");
                    exit; // Важно завершить выполнение скрипта после перенаправления
                } else {
                    throw new Exception("Неверный пароль.");
                }
            } else {
                throw new Exception("Пользователь не найден.");
            }
        } else {
            throw new Exception("Ошибка выполнения SQL-запроса: " . $conn->error);
        }
    }
} catch (Exception $e) {
    // Обработка ошибок, если они возникнут внутри блока try
    echo "Произошла ошибка: " . $e->getMessage();
}

$conn->close();
?>
