<?php
/** @var PDO $pdo */
require "database.php";

header('Content-Type: application/json');

// Return array of objects from .json
function read_json($filename) {
    if (!file_exists($filename)) die("File not found.");

    $jsonContent = file_get_contents($filename);
    $orders = json_decode($jsonContent, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        die("Error decoding JSON: " . json_last_error_msg());
    }

    return $orders;
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

function get_mail_id($mail): int | null {
    global $pdo;

    ['company' => $company, 'department' => $department, 'city' => $city] = $mail;
    
    $stmt = $pdo->prepare('SELECT mail_id FROM mail WHERE company = ? AND department_number = ? AND city = ?');
    $stmt->execute([$company, $department, $city]);
    $mail_id = $stmt->fetchColumn();

    if ($mail_id !== false) return (int) $mail_id;
    else return null;
}

# Return mail (company, department number and city in ASSOC)
function extract_mail($unformatted_mail) {
    $first_space = strpos($unformatted_mail, ' ');
    $second_part = substr($unformatted_mail, $first_space+1);

    $mail_company = substr($unformatted_mail, 0, $first_space);
    $mail_department = substr($second_part, 0, strpos($second_part, ' '));
    $mail_city = substr($second_part, strpos($second_part, ' ')+1); 

    $mail = [
        'company' => $mail_company,
        'department' => $mail_department,
        'city' => $mail_city
    ];

    return $mail;
}

$filePath = '../orders.json';
$orders = read_json($filePath);


# Read last order in .json and return order_id+1
function calc_order_id($orders): int | null {
    $last_order = end($orders);

    if ($last_order === null) return null;

    $new_id = $last_order['order_id'];
    return $new_id + 1;
}

# Read POST-request body
$jsonData = file_get_contents('php://input');
$data = json_decode($jsonData, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($data['mail'], $data['user_id'], $data['total_price'])) {
        $unformatted_mail = $data['mail'];
        $mail = extract_mail($unformatted_mail);
        $mail_id = get_mail_id($mail);
        if ($mail_id === null) throw new Exception("mail_id can't be null");
        
        $user_id = $data['user_id'];
        if ($user_id === null) throw new Exception("user_id can't be null");

        $items = json_decode(get_cart($user_id)['items'], true);
    
        $total_price = $data['total_price'];
        if ($total_price === null) throw new Exception("total_price can't be null");
    
        $order_id;

        if ($orders !== []) {
            $order_id = calc_order_id($orders);
            if ($order_id === null) throw new Exception("order_id can't be null");
        } else $order_id = 1;

        $order = [
            'order_id' => $order_id,
            'user_id' => $user_id,
            'mail_id' => $mail_id,
            'total_price' => $total_price,
            'items' => $items
        ];

        $orders[] = $order; # Append order in existed orders

        $json = json_encode($orders, JSON_PRETTY_PRINT);

        if ($json === false) die('Error encoding JSON');

        file_put_contents($filePath, $json);

        echo json_encode('Orders stored in .json successfully!');
    } else {
        echo json_encode('error');
    }
}
