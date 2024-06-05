<?php

# Configure Database
$host = 'localhost';
$db = 'computer_shop';
$user = 'root';
$pass = 'your pass';        // Change
$charset = 'utf8mb4';

$data_source_name = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    # Connection
    $pdo = new PDO($data_source_name, $user, $pass, $options);
} catch (PDOException $e) {
    throw new PDOException($e->getMessage(), (int)$e->getCode());
}
