<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../includes/db.php';

$data = json_decode(file_get_contents('php://input'), true);
$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $response['message'] = 'Vui lòng nhập đầy đủ thông tin';
        echo json_encode($response);
        exit;
    }
    
    $db = Database::getInstance();
    $user = $db->selectOne(
        "SELECT * FROM users WHERE email = ? AND status = 'active'",
        [$email]
    );
    
    if ($user && password_verify($password, $user['password'])) {
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['full_name'] ?? $user['username'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        
        $response['success'] = true;
        $response['message'] = 'Đăng nhập thành công';
        $response['user'] = [
            'id' => $user['id'],
            'name' => $user['full_name'] ?? $user['username'],
            'email' => $user['email'],
            'role' => $user['role']
        ];
    } else {
        $response['message'] = 'Email hoặc mật khẩu không đúng';
    }
}

echo json_encode($response);
?>