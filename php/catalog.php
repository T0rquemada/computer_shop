<?php
/** @var PDO $pdo */
require "database.php";

function get_items($pdo, $category) {
    $sql = $pdo->prepare("SELECT * FROM $category");
    $sql->execute();
    $items = $sql->fetchAll(PDO::FETCH_ASSOC);
    
    return $items;
}

$requested_url = $_SERVER['REQUEST_URI'];
$base_url = '/php/catalog.php';
$route = str_replace($base_url, '', $requested_url);

// Routes for GET-requests
if($_SERVER['REQUEST_METHOD'] == 'GET') {
    $category = null;

    switch ($route) {
        case '/cpu':
            $category = "cpu";
            break;
        case '/gpu':
            $category = "gpu";
            break;
        case '/ram':
            $category = "ram";
            break;
        case '/motherboards':
            $category = "motherboards";
            break;
        default:
            break;
    }

    $items = get_items($pdo, $category);
    echo json_encode($items);
}