<!DOCTYPE html>
<html>
<head>
    <title>Регистрация и Вход</title>
</head>
<body>
    <h1>Регистрация</h1>
    <form action="register.php" method="POST">
        Имя пользователя: <input type="text" name="username"><br>
        Email: <input type="email" name="email"><br>
        Пароль: <input type="password" name="password"><br>
        <input type="submit" value="Зарегистрироваться">
    </form>

    <h1>Вход</h1>
    <form action="login.php" method="POST">
        Имя пользователя: <input type="text" name="username"><br>
        Пароль: <input type="password" name="password"><br>
        <input type="submit" value="Войти">
    </form>
</body>
</html>
