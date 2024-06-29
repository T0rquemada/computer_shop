<?php
/** @var PDO $pdo */
require "database.php";

header('Content-Type: application/json');

function send_json_log(string $status, string $message) {
    echo json_encode(['status' => $status, 'message' => $message]);
}

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

function create_cart($user_id, $items): void {
    global $pdo;
    $stmt = $pdo->prepare('INSERT INTO cart (user_id, items) VALUES (?, ?)');
    $stmt->execute([$user_id, json_encode([$items])]);
}

function get_cart($user_id) {
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM cart WHERE user_id = ?');
    $stmt->execute([$user_id]);
    $cart = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!is_array($cart)) {
        return null;
    }

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

function update_cart($user_id, $new_item): void {
    global $pdo;

    $cart = get_cart($user_id);
    
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
    $stmt->execute([$user_id]);
    send_json_log('Success', 'Cart cleared successfully!');
}

function get_item($item_id, $category, $user_id=null, bool $cart=false) {
    global $pdo;

    $sql;
    $item;
    $items;
    
    // If item from cart
    if ($cart) {
        $items = get_cart($user_id);
    } else {
        if ($category === "motherboards") $id = 'motherboard' . "_id";
        else $id = $category . "_id";
        $sql = $pdo->prepare("SELECT * FROM $category WHERE $id = ?");
        $sql->execute([$item_id]);
        $item = $sql->fetch(PDO::FETCH_ASSOC);
    }
    
    // Return specific item from cart depending on id & category
    if ($cart) {
        $items = $items['items']; // Extract all items from cart

        $items = json_decode($items, true);

        foreach ($items as $element) {
            if ($element['id'] == $item_id && $element['category'] == $category) {
                return $element;
            }
        }
        return null;
    }

    if ($item) {
        return $item;
    } else {
        return 0;
    }
}

function set_items($items, int $user_id): void {
    global $pdo;

    $stmt = $pdo->prepare('UPDATE cart SET items = ? WHERE user_id = ?');
    $stmt->execute([json_encode($items), $user_id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Items updated!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No rows affected.']);
    }
}

// Replace item in cart['items']
function replace_item($new_item, $old_item, int $user_id): void {
    $cart = get_cart($user_id);
    $items = json_decode($cart['items'], true);
    
    foreach ($items as &$item) {
        if ($item['id'] === $old_item['id'] && $item['category'] === $old_item['category']) {
            $item['quantity'] = $new_item['quantity'];
        }
    }

    unset($item);

    set_items($items, $user_id);
}

function delete_item($item_to_delete, $user_id): void {
    global $pdo;
    $cart = get_cart($user_id);
    $items = json_decode($cart['items'], true);

    foreach ($items as $key => $item) {
        if ($item['id'] === $item_to_delete['id'] && $item['category'] === $item_to_delete['category']) {
            unset($items[$key]);
            
            if (!empty($items)) {
                set_items($items, $user_id);
                send_json_log('success', 'Item removed from cart');
            } else {
                delete_cart($user_id);
            }
            
            break;
        }
    }
    send_json_log('error', 'Item not removed from cart!');
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
        $cart = get_cart($user_id);
        echo json_encode($cart);
    } else if (isset($_GET['item_id'], $_GET['category'])) {
        $item_id = $_GET['item_id'];
        $category = $_GET['category'];

        $item = get_item($item_id, $category);
        
        if ($item === 0) echo json_encode("Error while get item from cart.");
        else echo json_encode($item); 
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($cart_data['user_id'])) {
    // Delete item from cart
    if (isset($cart_data['category'], $cart_data['item_id'], $cart_data['user_id'])) {
        $user_id = $cart_data['user_id'];
        $item_id = $cart_data['item_id'];
        $category = $cart_data['category'];

        $item_to_delete = [
            "id" => $item_id,
            "category" => $category
        ];

        delete_item($item_to_delete, $user_id);
    } 
    // Delete entire cart
    else {
        $user_id = $cart_data['user_id'];

        if (cart_exist($user_id)) {
            delete_cart($user_id);
        } else echo "Cart user with id: " . $user_id . ", doesn't exist.";
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'PUT' && isset($cart_data['category'], $cart_data['item_id'], $cart_data['new_quantity'])) {
    $category = $cart_data['category'];
    $item_id = $cart_data['item_id'];
    $new_quantity = $cart_data['new_quantity'];
    $user_id = $cart_data['user_id'];

    try {
        $old_item = get_item($item_id, $category, $user_id, true);
        $new_item = array_merge($old_item, ['quantity' => $new_quantity]);  // Item with updated 'quantity'

        replace_item($new_item, $old_item, $user_id);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error updating quantity: ' . $e->getMessage()]);
    }
} else echo json_encode(['status' => 'Undefined route', 'message' => 'Request method not handled']);