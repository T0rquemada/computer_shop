<?php

// Return array of objects from .json
function read_json($filename) {
    if (!file_exists($filename)) {
        die("File not found.");
    }

    $jsonContent = file_get_contents($filename);
    $orders = json_decode($jsonContent, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        die("Error decoding JSON: " . json_last_error_msg());
    }

    return $orders;
}

// Return target order from given array of orders
function extractOrder($orders, $target_order_id): null | string {
    $orderFound = null;

    foreach ($orders as $order) {
        if ($order['order_id'] == $target_order_id) {
            $orderFound = $order;
            break;
        }
    }

    if ($orderFound) {
        return json_encode($orderFound);
    } else {
        echo "Order with order_id $target_order_id not found.\n";
        return null;
    }
}

$filename = 'orders.json';
$orders = read_json($filename);
