<?php
/** @var PDO $pdo */
require "database.php";
require '../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

// Returns JWT with user email&password
function generate_jwt(?array $user): string {
    global $secret_key;
    if (!$secret_key) throw new ExpiredException("Secret key not set");
    if (!$user) throw new ExpiredException("User not found");

    $payload = [
        'iat' => time(),
        'email' => $user['email'],
        'password' => $user['password'],
//        'user_id' => $user_id
    ];

    return JWT::encode($payload, $secret_key, 'HS256');
}

// Return array with email and password, which parsed from jwt
function parse_jwt(string $jwt): ?array {
    global $secret_key;

    try {
        $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));
        $decoded_array = (array) $decoded; // Convert to array

        $email = $decoded_array['email'];
        $password = $decoded_array['password'];

        return ['email' => $email, 'password' => $password];
    } catch (Exception $e) {
        error_log('JWT decode error: ' . $e->getMessage());
        return null;
    }
}

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

# Handle email and password, and try sign in
function sign_in(array $user): void {
    global $pdo;

    if (empty($user['email'])) {
        http_response_code(500);
        echo json_encode(['status' => false, 'message' => 'user email not provided!']);
    }

    if (empty($user['password'])) {
        http_response_code(500);
        echo json_encode(['status' => false, 'message' => 'user password not provided!']);
    }

    // Check if the user exists
    if (user_already_exist('email', $user['email'])) {
        // Verify email and password
        if (verify_user($pdo, $user)) {
            $jwt = generate_jwt($user);
            http_response_code(200);
            echo json_encode(['status' => true, 'message' => 'Logged in successfully!', 'jwt' => $jwt]);
        } else {
            http_response_code(400);
            echo json_encode(['status' => false, 'message' => "Incorrect userdata while sign in"]);
        }
    } else {
        http_response_code(404);
        echo json_encode(['status' => false, 'message' => "User does not exist!"]);
    }
}

$secret_key = 'YMAMWhdVsg0Qyw0Ei6TzcPm4CAeOnKGKRtNw2PdnE2Q=';

$requested_url = $_SERVER['REQUEST_URI'];
$base_url = '/php/users.php';
$route = str_replace($base_url, '', $requested_url);    // Cut off base_url from requested_url

// Routing for POST-methods
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userdata = get_json_input();
    check_json_error();

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
                    $jwt = generate_jwt($user);
                    echo json_encode(['jwt' => $jwt, 'message' => 'User added successfully!']);
                } else {
                    http_response_code(409);
                    echo json_encode(['message' => "User with this username already exist!"]);
                }
            } else {
                http_response_code(400);
                echo 'Incorrect json data while sign up!';
            }

            break;
        case '/signin':
            $user = [];
            if (isset($userdata['jwt'])) {
                $jwt = parse_jwt($userdata['jwt']);
                $user = [
                    'email' => $jwt['email'],
                    'password' => $jwt['password']
                ];
            } else if (isset($userdata['email'], $userdata['password'])) {
                $user = [
                    'email' => $userdata['email'],
                    'password' => $userdata['password']
                ];
            } else {
                http_response_code(400);
                echo json_encode(['status' => false, 'message' => "Incorrect JSON data while sign in!"]);
            }

            sign_in($user);
            break;
        default:
            http_response_code(404);
            echo json_encode(["message" => "Route not found"]);
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