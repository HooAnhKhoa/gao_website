<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../includes/db.php';

$data = json_decode(file_get_contents('php://input'), true);
$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $data['full_name'] ?? '';
    $email = $data['email'] ?? '';
    $phone = $data['phone'] ?? '';
    $password = $data['password'] ?? '';
    $confirm_password = $data['confirm_password'] ?? '';
    
    // Validation
    if (empty($full_name) || empty($email) || empty($phone) || empty($password) || empty($confirm_password)) {
        $response['message'] = 'Vui lòng nhập đầy đủ thông tin';
        echo json_encode($response);
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Email không hợp lệ';
        echo json_encode($response);
        exit;
    }
    
    if (!preg_match('/^[0-9]{10,11}$/', $phone)) {
        $response['message'] = 'Số điện thoại không hợp lệ';
        echo json_encode($response);
        exit;
    }
    
    if ($password !== $confirm_password) {
        $response['message'] = 'Mật khẩu xác nhận không khớp';
        echo json_encode($response);
        exit;
    }
    
    if (strlen($password) < 6) {
        $response['message'] = 'Mật khẩu phải có ít nhất 6 ký tự';
        echo json_encode($response);
        exit;
    }
    
    $db = Database::getInstance();
    
    // Check if email exists
    $existingUser = $db->selectOne(
        "SELECT id FROM users WHERE email = ?",
        [$email]
    );
    
    if ($existingUser) {
        $response['message'] = 'Email đã được đăng ký';
        echo json_encode($response);
        exit;
    }
    
    // Check if phone exists
    $existingPhone = $db->selectOne(
        "SELECT id FROM users WHERE phone = ?",
        [$phone]
    );
    
    if ($existingPhone) {
        $response['message'] = 'Số điện thoại đã được đăng ký';
        echo json_encode($response);
        exit;
    }
    
    // Create user
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $username = explode('@', $email)[0] . '_' . time();
    
    try {
        $userId = $db->insert('users', [
            'username' => $username,
            'email' => $email,
            'password' => $hashedPassword,
            'full_name' => $full_name,
            'phone' => $phone,
            'role' => 'user',
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        if ($userId) {
            session_start();
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_name'] = $full_name;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_role'] = 'user';
            
            $response['success'] = true;
            $response['message'] = 'Đăng ký thành công';
            $response['user'] = [
                'id' => $userId,
                'name' => $full_name,
                'email' => $email
            ];
        } else {
            $response['message'] = 'Có lỗi xảy ra khi đăng ký';
        }
    } catch (Exception $e) {
        $response['message'] = 'Lỗi hệ thống: ' . $e->getMessage();
    }
}

echo json_encode($response);
?>