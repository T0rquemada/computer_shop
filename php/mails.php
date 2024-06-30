<?php
/** @var PDO $pdo */
require "database.php";

# Return all mails from DB
function get_mails() {
    global $pdo;

    $query = $pdo->prepare('SELECT * FROM mail');
    $query->execute();
    $mails = $query->fetchAll(PDO::FETCH_ASSOC);

    return $mails;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $mails = get_mails();
    echo json_encode($mails);
}