<?php
/** @var PDO $pdo */
require "database.php";
require '../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 2));
$dotenv->load();

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Returns JWT with user email&password
function generate_jwt(int $user_id): string {
    global $secret_key;
    if (!isset($secret_key, $user_id)) {
        throw new Exception("Secret key or user_id not set while generating jwt");
    }

    $payload = [
        'iat' => time(),
        'user_id' => $user_id
    ];

    return JWT::encode($payload, $secret_key, 'HS256');
}

// Extract userdata from JWT. Return assocArray with 'user_id'
function parse_jwt(string $jwt) {
    global $secret_key;

    if (empty($jwt) && !is_string($jwt)) {
        throw new Exception("JWT not provided or provided not string when try parse it");
    }

    try {
        $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));
        $decoded_array = (array) $decoded; // Convert to array

        $user_id = $decoded_array['user_id'];

        return ['user_id' => $user_id];
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

// Insert user in DB. Check that all fields provided. Suppose to accept filtered data
function insert_user($user): void {
    global $pdo;

    if (!isset($user['username'], $user['password'], $user['phone'], $user['email'])) {
        throw new Exception("Incorrect userdata while inserting user!");
    }

    $name =  $user['username'];
    $pass = $user['password']; # Hash user password
    $phone = $user['phone'];
    $email = $user['email'];

    $stmt = $pdo->prepare('INSERT INTO users (username, password, phone_number, email) VALUES (?, ?, ?, ?)');
    $stmt->execute([$name, $pass, $phone, $email]);
}

// Return user id, if user not found/incorrect argument  - null
function get_userid(string $user_email) : int {
    global $pdo;

    if (empty($user_email) || !is_string($user_email)) {
        throw new Exception("User email not provided or provided not string when try get user id");
    }

    $sql = $pdo->prepare('SELECT user_id FROM users WHERE email = ?');
    $sql->execute([$user_email]);
    $user_id = $sql->fetch(PDO::FETCH_ASSOC);

    if ($user_id && (int)$user_id['user_id'] !== 0) {
        return (int)$user_id['user_id'];
    } else {
        return null;
    }
}

// Return true if user with given email/username exist, otherwise - false
function user_already_exist(string $type, string $user_field): bool  {
    global $pdo;

    if (!isset($type, $user_field)) {
        throw new Exception("Incorrect userdata while checking that user exist!");
    }

    $stmt = null;

    if ($type === 'email') {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = ?');
    } else if ($type === 'username') {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ?');
    } else {
        http_response_code(400);
        echo json_encode(['message' => 'Invalid type provided while checking that user exist!!']);
        exit;
    }

    $stmt->execute([$user_field]);
    $result = $stmt->fetchColumn();

    return $result > 0;
}

// Return true, if user email and pass fits
function verify_user($pdo, $user): bool {
    if (!isset($user['email'], $user['password'])) {
        throw new Exception("Userdata not provided, mamybe it lost on way");
    }

    $email = $user['email'];
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user_from_db = $stmt->fetch();

    if (isset($user_from_db['password']) && $user['password'] === $user_from_db['password']) {
        return true;
    }
    
    return false;
}

// Handle email and password, and try sign in
function sign_in(array $user): void {
    global $pdo;

    // Check if the user exists
    if (user_already_exist('email', $user['email'])) {
        // Verify email and password
        if (verify_user($pdo, $user)) {
            $user_id = get_userid($user['email']);
            $jwt = generate_jwt($user_id);
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

// Handle JWT, and try sign in
function sign_in_jwt(string $jwt): void {
    global $pdo;

    if (!isset($jwt)) {
        throw new Exception("JWT not provided while sign in via JWT");
    }

    $user_id = parse_jwt($jwt)['user_id'];

    $sql = $pdo->prepare('SELECT user_id FROM users WHERE user_id = ?');
    $sql->execute([$user_id]);
    $user_id_db = $sql->fetch();

    if ($user_id_db) {
        echo json_encode(['status' => true, 'message' => 'Logged via JWT in successfully!']);
    } else {
        http_response_code(400);
        echo json_encode(['status' => false, 'message' => "Incorrect user_id while sign in via JWT"]);
    }
}

$secret_key = $_ENV['JWT_SECRET'];

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

                $user['username'] = htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8');
                $user['email'] = htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8');
                $user['password'] = password_hash($user['password'], PASSWORD_DEFAULT);
                $user['phone'] = htmlspecialchars($user['phone'], ENT_QUOTES, 'UTF-8');

                if (!user_already_exist('username', $user['username'])) {
                    insert_user($user);
                    $user_id = get_userid($user['email']);
                    $jwt = generate_jwt($user_id);
                    echo json_encode(['status' => true, 'message' => 'User added successfully!', 'jwt' => $jwt]);
                } else {
                    http_response_code(409);
                    echo json_encode(['status' => false, 'message' => "User with this username already exist!"]);
                }
            } else {
                http_response_code(400);
                echo json_encode(['status' => false, 'message' => 'Incorrect userdata while sign up!']);
            }
            break;
        case '/signin':
            if (isset($userdata['jwt'])) {
                sign_in_jwt($userdata['jwt']);
            } else if (isset($userdata['email'], $userdata['password'])) {
                $user = [
                    'email' => $userdata['email'],
                    'password' => $userdata['password']
                ];
                sign_in($user);
            } else {
                http_response_code(400);
                echo json_encode(['status' => false, 'message' => "Incorrect JSON data while sign in!"]);
            }
            break;
        default:
            http_response_code(404);
            echo json_encode(["message" => "Route not found"]);
            break;
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    switch ($route) {
        case '/sign_in_jwt':
            if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
                $headers = $_SERVER['HTTP_AUTHORIZATION'];
                $token = preg_match('/Bearer\s(\S+)/', $headers, $matches);
                $jwt = $matches[1];
                sign_in_jwt($jwt);
            } else {
                http_response_code(400);
                echo json_encode(['status' => false, 'message' => "Incorrect json data while sign in via JWT!"]);
            }
            break;
        case '/get_user_id':
            if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
                $headers = $_SERVER['HTTP_AUTHORIZATION'];
                $token = preg_match('/Bearer\s(\S+)/', $headers, $matches);
                $jwt = $matches[1];
                $user_id = parse_jwt($jwt)['user_id'];
        
                echo json_encode(['user_id' => $user_id, 'message' => 'user_id fetched successfully!']);
            } else {
                http_response_code(400);
                echo json_encode(['message' => "Incorrect json data while getting user id!"]);
            }
            break;
        default:
            http_response_code(400);
            echo json_encode(['status' => false, 'message' => "GET-route not found!"]);
    }
}