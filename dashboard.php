<?php
session_start();
include("config.php");
include("logger.php");

// Подключение библиотеки PHPWord
require_once 'vendor/autoload.php';
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

if (!isset($_SESSION["username"])) {
    header("Location: index.php");
    exit();
}

// Логика для защищенной страницы

// Создание нового пользователя
if (isset($_POST["create_user"])) {
    $new_username = $_POST["new_username"];
    $new_email = $_POST["new_email"];
    $new_password = $_POST["new_password"];
    $isRoot = isset($_POST["is_root"]) ? 1 : 0;

    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (username, email, password, isRoot) VALUES ('$new_username', '$new_email', '$hashed_password', '$isRoot')";

    if ($conn->query($sql) === TRUE) {
        log_action("Создан новый пользователь: $new_username");
    } else {
        echo "Ошибка при создании пользователя: " . $conn->error;
    }
}

// Вывод пользователей с isRoot == true
if (isset($_POST["show_root_users"])) {
    $sql = "SELECT * FROM users WHERE isRoot = 1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<h2>Пользователи с isRoot == true:</h2>";
        echo "<ul>";
        while ($row = $result->fetch_assoc()) {
            echo "<li>" . $row["username"] . "</li>";
        }
        echo "</ul>";
    } else {
        echo "Нет пользователей с isRoot == true.";
    }
}

// Чтение данных пользователя
if (isset($_POST["read_user"])) {
    $user_id_to_read = $_POST["user_id_to_read"];
    $sql = "SELECT * FROM users WHERE user_id = $user_id_to_read";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        echo "<h2>Данные пользователя с ID $user_id_to_read:</h2>";
        echo "Имя пользователя: " . $row["username"] . "<br>";
        echo "Email: " . $row["email"] . "<br>";
        echo "isRoot: " . ($row["isRoot"] ? "Да" : "Нет") . "<br>";
    } else {
        echo "Пользователь не найден.";
    }
}

// Редактирование данных пользователя
if (isset($_POST["edit_user"])) {
    $user_id_to_edit = $_POST["user_id_to_edit"];
    $new_username = $_POST["edited_username"];
    $new_email = $_POST["edited_email"];
    $isRoot = isset($_POST["edited_is_root"]) ? 1 : 0;

    $sql = "UPDATE users SET username='$new_username', email='$new_email', isRoot='$isRoot' WHERE user_id='$user_id_to_edit'";

    if ($conn->query($sql) === TRUE) {
        log_action("Изменены данные пользователя с ID $user_id_to_edit");
    } else {
        echo "Ошибка при редактировании пользователя: " . $conn->error;
    }
}


// Удаление пользователя
if (isset($_POST["delete_user"])) {
    $user_id_to_delete = $_POST["user_id_to_delete"];
    
    try {
        $sql = "DELETE FROM users WHERE user_id = $user_id_to_delete";
        if ($conn->query($sql) === TRUE) {
            log_action("Удален пользователь с ID $user_id_to_delete");
        } else {
            throw new Exception("Ошибка при удалении пользователя: " . $conn->error);
        }
    } catch (Exception $e) {
        echo "Произошла ошибка: " . $e->getMessage();
    } finally {
        // Любой код, который нужно выполнить вне зависимости от исключения
    }
}



// Функция для выполнения поиска пользователей
function searchUsers($conn, $query) {
    $sql = "SELECT * FROM users WHERE username LIKE '%$query%'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<h2>Результаты поиска:</h2>";
        echo "<ul>";
        while ($row = $result->fetch_assoc()) {
            echo "<li>" . $row["username"] . "</li>";
        }
        echo "</ul>";
    } else {
        echo "По вашему запросу ничего не найдено.";
    }
}


// Поиск пользователей
if (isset($_POST["search_users"])) {
    $search_query = $_POST["search_query"];
    if (!empty($search_query)) {
        searchUsers($conn, $search_query);
    } else {
        echo "Пожалуйста, введите запрос для поиска.";
    }
}



// Экспорт данных в XML-файл
if (isset($_POST["export_data"])) {
    $sql = "SELECT * FROM users";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $xml = new SimpleXMLElement("<users></users>");

        while ($row = $result->fetch_assoc()) {
            $user = $xml->addChild("user");
            $user->addChild("user_id", $row["user_id"]);
            $user->addChild("username", $row["username"]);
            $user->addChild("email", $row["email"]);
            $user->addChild("isRoot", $row["isRoot"]);
        }

        $xml->asXML("users.xml");
        log_action("Данные экспортированы в XML-файл");
        echo "Данные экспортированы в <a href='users.xml' download>users.xml</a>";
    } else {
        echo "Нет данных для экспорта.";
    }
}

// Импорт данных из XML-файла
if (isset($_POST["import_data"])) {
    if ($_FILES["import_file"]["error"] == UPLOAD_ERR_OK) {
        $xml_content = file_get_contents($_FILES["import_file"]["tmp_name"]);
        $xml = simplexml_load_string($xml_content);

        foreach ($xml->user as $user_data) {
            $username = $user_data->username;
            $email = $user_data->email;
            $isRoot = $user_data->isRoot;

            $sql = "INSERT INTO users (username, email, isRoot) VALUES ('$username', '$email', '$isRoot')";
            if ($conn->query($sql) === TRUE) {
                log_action("Импортирован пользователь: $username");
            } else {
                echo "Ошибка при импорте данных: " . $conn->error;
            }
        }

        echo "Данные успешно импортированы.";
    } else {
        echo "Ошибка при загрузке файла.";
    }
}


// Экспорт данных в DOCX-файл
if (isset($_POST["export_docx"])) {
    $sql = "SELECT * FROM users";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        // Заголовок
        $section->addTitle("Список пользователей", 1);

        // Таблица
        $table = $section->addTable();
        $table->addRow();
        $table->addCell(500)->addText("ID пользователя");
        $table->addCell(5000)->addText("Имя пользователя");
        $table->addCell(5000)->addText("Email");
        $table->addCell(1500)->addText("isRoot");

        while ($row = $result->fetch_assoc()) {
            $table->addRow();
            $table->addCell(500)->addText($row["user_id"]);
            $table->addCell(5000)->addText($row["username"]);
            $table->addCell(5000)->addText($row["email"]);
            $table->addCell(1500)->addText($row["isRoot"] ? "Да" : "Нет");
        }

        // Сохранение в DOCX-файл
        $filename = "users.docx";
        $phpWord->save($filename);
        log_action("Данные экспортированы в DOCX-файл");

        // Отправка файл на скачивание
        header("Content-Description: File Transfer");
        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        readfile($filename);
        unlink($filename); // Удаление временного файла после отправки
        exit();
    } else {
        echo "Нет данных для экспорта.";
    }
}



$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Защищенная страница</title>
</head>
<body>
    <h1>Добро пожаловать, <?php echo $_SESSION["username"]; ?>!</h1>

    <a href="ajax.php">Получение пользователей - ajax</a>

    <!-- Форма для поиска -->
    <form action="dashboard.php" method="POST">
        <label for="search_query">Поиск пользователей:</label>
        <input type="text" name="search_query" id="search_query" placeholder="Введите имя пользователя">
        <input type="submit" name="search_users" value="Искать">
    </form>


    <!-- Форма для создания нового пользователя -->
    <h2>Создать нового пользователя:</h2>
    <form action="dashboard.php" method="POST">
        Имя пользователя: <input type="text" name="new_username"><br>
        Email: <input type="email" name="new_email"><br>
        Пароль: <input type="password" name="new_password"><br>
        isRoot: <input type="checkbox" name="is_root" value="1"><br>
        <input type="submit" name="create_user" value="Создать">
    </form>

    <!-- Форма для вывода пользователей с isRoot == true -->
    <h2>Показать пользователей с isRoot == true:</h2>
    <form action="dashboard.php" method="POST">
        <input type="submit" name="show_root_users" value="Показать">
    </form>

    <!-- Форма для чтения данных пользователя -->
    <h2>Чтение данных пользователя:</h2>
    <form action="dashboard.php" method="POST">
        ID пользователя: <input type="text" name="user_id_to_read"><br>
        <input type="submit" name="read_user" value="Читать">
    </form>

    <!-- Форма для редактирования данных пользователя -->
    <h2>Редактирование данных пользователя:</h2>
    <form action="dashboard.php" method="POST">
        ID пользователя: <input type="text" name="user_id_to_edit"><br>
        Новое имя пользователя: <input type="text" name="edited_username"><br>
        Новый Email: <input type="email" name="edited_email"><br>
        isRoot: <input type="checkbox" name="edited_is_root" value="1"><br>
        <input type="submit" name="edit_user" value="Редактировать">
    </form>

    <!-- Форма для экспорта данных в XML -->
    <h2>Экспорт данных в XML:</h2>
    <form action="dashboard.php" method="POST">
        <input type="submit" name="export_data" value="Экспортировать в XML">
    </form>

    <!-- Форма для импорта данных из XML -->
    <h2>Импорт данных из XML:</h2>
    <form action="dashboard.php" method="POST" enctype="multipart/form-data">
        Выберите XML-файл: <input type="file" name="import_file"><br>
        <input type="submit" name="import_data" value="Импортировать из XML">
    </form>


    <!-- Форма для экспорта данных в DOCX -->
    <h2>Экспорт данных в DOCX:</h2>
    <form action="dashboard.php" method="POST">
        <input type="submit" name="export_docx" value="Экспортировать в DOCX">
    </form>



    <!-- Форма для удаления пользователя -->
    <h2>Удалить пользователя:</h2>
    <form action="dashboard.php" method="POST">
        ID пользователя: <input type="text" name="user_id_to_delete"><br>
        <input type="submit" name="delete_user" value="Удалить">
    </form>

    <!-- Другие CRUD операции и вывод данных могут быть добавлены здесь -->
    
    <a href="logout.php">Выйти</a>
</body>
</html>
