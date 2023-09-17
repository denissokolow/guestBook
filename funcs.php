<?php

function debug($data)
{
    echo '<pre' . print_r($data, 1) . '<pre';
}

function login(): bool //Авторизация по логину и паролю
{
    global $pdo;

    $login = !empty($_POST['login']) ? trim($_POST['login']) : '';//Проверка на только пробелы
    $pass = !empty($_POST['pass']) ? trim($_POST['pass']) : '';//Проверка на только пробелы

    if (empty($login) || empty($pass)) {//Проверка на пустое поле
        $_SESSION['errors'] = 'Поля логин/пароль обязательны';
        return false;
    }
    $res = $pdo->prepare("SELECT * FROM users WHERE login=?");//Поиск логина в таблице
    $res->execute([$login]);
    if (!$user = $res->fetch()) {
        $_SESSION['errors'] = 'Логин или пароль введены не верно';
        return false;
    }
    if (!password_verify($pass, $user['pass'])) {//Сравнение паролей
        $_SESSION['errors'] = 'Логин или пароль введены не верно';
        return false;
    } else {
        $_SESSION['success'] = 'Вы успешно аторизовались';//Запись данных в сессию
        $_SESSION['user']['name'] = $user['login'];
        $_SESSION['user']['id'] = $user['id'];
        return true;
    }
}

function registration(): bool //Регистрация нового пользователя
{
    global $pdo;

    $login = !empty($_POST['login']) ? trim($_POST['login']) : '';//Проверка на только пробелы
    $pass = !empty($_POST['pass']) ? trim($_POST['pass']) : '';//Проверка на только пробелы

    if (empty($login) || empty($pass)) {//Проверка на пустое поле
        $_SESSION['errors'] = 'Поля логин/пароль обязательны';
        return false;
    }

    $res = $pdo->prepare("SELECT COUNT(*) FROM users WHERE login = ?");//Проверка на существующий логин
    $res->execute([$login]);

    if ($res->fetchColumn()) {
        $_SESSION['errors'] = 'Данное имя уже используется';
        return false;
    }

    $pass = password_hash($pass, PASSWORD_DEFAULT);
    $res = $pdo->prepare("INSERT INTO users (login, pass) VALUES (?,?)");//Добавляем нового юзера в базу

    if ($res->execute([$login, $pass])) {
        $_SESSION['success'] = 'Успешная регистрация';
        return true;
    } else {
        $_SESSION['errors'] = 'Ошибка регистрации';
        return false;
    }

}