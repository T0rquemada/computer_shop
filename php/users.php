<?php
/** @var PDO $pdo */
require "database.php";

function get_json_input() {
    $jsonData = file_get_contents('php://input');
    return json_decode($jsonData, true);
}

function check_json_error(): void {
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "Failed to decode JSON data: " . json_last_error_msg() . "\n";
        exit();
    }
}

function insert_user($user): void {
    global $pdo;

    $name = $user['username'];
    $pass = password_hash($user['password'], PASSWORD_DEFAULT); # Hash user password
    $phone = $user['phone'];
    $email = $user['email'];

    $stmt = $pdo->prepare('INSERT INTO users (username, password, phone_number, email) VALUES (?, ?, ?, ?)');
    $stmt->execute([$name, $pass, $phone, $email]);
}

# Return user id, if user not found/incorrect argument  - null
function get_userid($user_email) : int | null {
    global $pdo;

    if (!is_string($user_email)) {
        $user_email = (string) $user_email;
    }

    $sql = $pdo->prepare('SELECT user_id FROM users WHERE email = ?');
    $sql->execute([$user_email]);
    $user_id = $sql->fetch(PDO::FETCH_ASSOC);

    if ($user_id && (int)$user_id['user_id'] !== 0) {
        return (int)$user_id['user_id'];
    } else {
        return 0;
    }
}

# Return true if user with given email/username exist, otherwise - false
function user_already_exist(string $type, string $user_field): bool {
    global $pdo;
    $stmt = null;

    if ($type === 'email') {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = ?');
    } else if ($type === 'username') {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ?');
    }

    $stmt->execute([$user_field]);
    $result = $stmt->fetchColumn();

    return $result > 0;
}

# Return true, if user email and pass fits
function verify_user($pdo, $user): bool {
    try {
        $email = $user['email'];
        $password = $user['password'];
    } catch (ValueError $e) {
        echo "ValueError: " . $e->getMessage();
        return false;
    }

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user_from_db = $stmt->fetch();

    if ($user && password_verify($password, $user_from_db['password'])) {
        return true;
    }
    return false;
}

$requested_url = $_SERVER['REQUEST_URI'];
$base_url = '/php/users.php';
$route = str_replace($base_url, '', $requested_url);    // Cut off base_url from requested_url

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userdata = get_json_input();
    check_json_error();

    // Routing for sin in/sign up
    switch ($route) {
        case '/signup':
            $correct_data = isset($userdata['email'], $userdata['nickname'], $userdata['password'], $userdata['phone']);

            if ($correct_data) {
                $user = [
                    'username' => $userdata['nickname'],
                    'email' => $userdata['email'],
                    'password' => $userdata['password'],
                    'phone' => $userdata['phone']
                ];

                if (!user_already_exist('username', $user['username'])) {
                    insert_user($user);
                    echo "User added successfully!";
                    echo json_encode($user);
                } else echo "User with this username already exist!\n";
            } else echo 'Incorrect json data while sign up!';

            break;
        case '/signin':
            if (isset($userdata['email'], $userdata['password'])) {
                $user = [
                    'email' => $userdata['email'],
                    'password' => $userdata['password']
                ];

                if (user_already_exist('email', $user['email'])) {
                    if (verify_user($pdo, $user)) {
                        echo "Logged successfully!";
                        echo json_encode($user);
                    } else echo "Incorrect data while sign in";
                } else echo "User does not exist!\n";
            } else echo "Incorrect JSON data while sign in!\n";

            break;
        default:
            header("HTTP/1.0 404 Not Found");
            echo "Route not found";
            break;
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['email'])) {
        $email = $_GET['email'];

        if (user_already_exist("email", $email)) {
            echo get_userid($email);
        } else echo json_encode("User with this email doesn't exist!");

    } else echo json_encode("Incorrect json data while getting user id!");
 
}