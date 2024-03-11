<?php
$host = 'localhost';
$dbname = 'googlewebsite_ass2';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Can't connect to the database $dbname :" . $e->getMessage());
}
