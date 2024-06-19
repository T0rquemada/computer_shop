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
    $stmt->execute([$user_id, json_encode([$items])]);
}

function get_cart($pdo, $user_id) {
    $stmt = $pdo->prepare('SELECT * FROM cart WHERE user_id = ?');
    $stmt->execute([$user_id]);
    $cart = $stmt->fetch(PDO::FETCH_ASSOC);

    return $cart;
}

// Unite items with same item_id, and sum their quantity, to avoid duplicates in 'items'
function unite_items($items) {
    $united_items = [];
    
    foreach ($items as $item) {
        $item_id = $item['id'];
        $item_category = $item['category'];
        $quantity = (int)$item['quantity'];

        // Composite key based on id and category
        $composite_key = $item_id . '-' . $item_category;

        if (isset($united_items[$composite_key])) {
            // Add quantity to the existing quantity
            $united_items[$composite_key]['quantity'] += $quantity;
        } else {
            // Add the item to united_items
            $united_items[$composite_key] = $item;
        }
    }

    // Convert to indexed array
    $united_items = array_values($united_items);

    return $united_items;
}

function update_cart($user_id, $new_item) {
    global $pdo;

    $cart = get_cart($pdo, $user_id);
    
    if ($cart) {
        $items = json_decode($cart['items'], true); 
        try {
            $items[] = $new_item;
            $items = unite_items($items);
            $updated_items = json_encode($items);

            $stmt = $pdo->prepare('UPDATE cart SET items = ? WHERE user_id = ?');
            $stmt->execute([$updated_items, $user_id]);
        } catch (Exception $e) {echo $e;}
    }
}

function delete_cart($user_id) {
    global $pdo;
    $stmt = $pdo->prepare('DELETE FROM cart WHERE user_id = ?');
}

function get_item($item_id, $category) {
    global $pdo;

    $id = $category . "_id";

    $sql = $pdo->prepare("SELECT * FROM $category WHERE $id = ?");
    $sql->execute([$item_id]);
    $item = $sql->fetch(PDO::FETCH_ASSOC);
    
    if ($item) {
        return $item;
    } else {
        return 0;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cart_data = get_json_input();
    $requested_url = $_SERVER['REQUEST_URI'];
    $base_url = '/php/cart.php';
    $route = str_replace($base_url, '', $requested_url);    // Cut off base_url from requested_url

    switch($route) {
        case '/updatecart': 
            $correct_data = isset($cart_data['user_id'], $cart_data['item']);
            if ($correct_data) {
                if (cart_exist($cart_data['user_id'])) {
                    update_cart($cart_data['user_id'], $cart_data['item']);
                    echo 'update existing cart';
                } else {
                    create_cart($cart_data['user_id'], $cart_data['item']);
                    echo 'create cart';
                }
            } else echo 'Incorrect json cart data while updating cart!';
            
            break;
        default:
            echo 'Default case for switch.';
            break;
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['user_id'])) {
        $user_id = $_GET['user_id'];
        $cart = get_cart($pdo, $user_id);
        echo json_encode($cart);
    } else if (isset($_GET['item_id'], $_GET['category'])) {
        $item_id = $_GET['item_id'];
        $category = $_GET['category'];

        $item = get_item($item_id, $category);
        
        if ($item === 0) echo json_encode("Error while get item from cart.");
        else echo json_encode($item); 
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($cart_data['user_id'])) {
    $user_id = $cart_data['user_id'];

    if (cart_exist($user_id)) {
        delete_cart($user_id);
    } else echo "Cart user with id: " . $user_id . ", doesn't exist.";
}