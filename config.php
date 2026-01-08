<?php

// إعدادات قاعدة البيانات

define('DB_HOST', 'sql301.infinityfree.com');

define('DB_NAME', 'if0_40530050_abso');

define('DB_USER', 'if0_40530050');

define('DB_PASS', '0PwZzDkuwL6');

// إعدادات الجلسة

session_start();

// الاتصال بقاعدة البيانات

try {

    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("set names utf8");

} catch(PDOException $e) {

    die("Connection failed: " . $e->getMessage());

}

?>