<?php
/** @var PDO $pdo */
require "database.php";

// Return body requests, decoding from JSON
function get_json_input() {
    $jsonData = file_get_contents('php://input');
    return json_decode($jsonData, true);
}

# Return true if cart for specific user already exist
function cart_exist(int $user_id) : bool {
    global $pdo;
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM cart WHERE user_id = ?');
    $stmt->execute([$user_id]);
    $result = $stmt->fetchColumn();

    return $result > 0;
}

function create_cart($user_id, $items) {
    global $pdo;
    $stmt = $pdo->prepare('INSERT INTO cart (user_id, items) VALUES (?, ?)');
    $stmt->execute([$user_id, json_encode($items)]);
}

function get_cart($pdo, $user_id) {
    $stmt = $pdo->prepare('SELECT * FROM cart WHERE user_id = ?');
    $stmt->execute([$user_id]);
    $cart = $stmt->fetch(PDO::FETCH_ASSOC);

    return $cart;
}

// Unite items with same item_id, and sum their quantity, to avoid duplicates same items
function unite_items($items) {
    $united_items = [];

    foreach ($items as $item) {
        $item_id = $item['item_id'];
        $quantity = (int)$item['quantity'];

        if (isset($united_items[$item_id])) {
            // If item_id exists, add quantity to existing quantity
            $united_items[$item_id]['quantity'] += $quantity;
        } else {
            // If item_id doesn't exist, add the item to united_items
            $united_items[$item_id] = $item;
        }
    }

    // Convert united_items array back to indexed array
    $united_items = array_values($united_items);

    return $united_items;
}

function update_cart($user_id, $new_item) {
    global $pdo;

    $cart = get_cart($pdo, $user_id);
    if ($cart) {
        $items = json_decode($cart['items'], true);   // Collect already added in DB items   
        array_push($items, $new_item[0]);  // Add new item to array
        $items = unite_items($items); // Unite items with same item_id
        $updated_items = json_encode($items);

        $stmt = $pdo->prepare('UPDATE cart SET items = ? WHERE user_id = ?');
        $stmt->execute([$updated_items, $user_id]);
    }
}

function delete_cart($user_id) {
    global $pdo;
    $stmt = $pdo->prepare('DELETE FROM cart WHERE user_id = ?');
}

$cart_data = get_json_input();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requested_url = $_SERVER['REQUEST_URI'];
    $base_url = '/php/cart.php';
    $route = str_replace($base_url, '', $requested_url);    // Cut off base_url from requested_url

    switch($route) {
        case '/updatecart': 
            $correct_data = isset($cart_data['user_id'], $cart_data['item']);
            if ($correct_data) {
                if (cart_exist($cart_data['user_id'])) {
                    update_cart($cart_data['user_id'], $cart_data['item'], $cart_data['quantity']);
                } else {
                    create_cart($cart_data['user_id'], $cart_data['item'], $cart_data['quantity']);
                }
            } else echo 'Incorrect json cart data while updateing cart!';
            
            break;
        default:
            echo 'Default case for switch.';
            break;
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($cart_data['user_id'])) {
    $user_id = $cart_data['user_id'];

    if (cart_exist($user_id)) {
        delete_cart($user_id);
    } else echo "Cart user with id: " . $user_id . ", doesn't exist.";
}