<?php
// api/auth/logout.php

// Start session nếu chưa có
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Hủy tất cả session variables
$_SESSION = array();

// Hủy session
if (session_destroy()) {
    // Redirect về trang chủ
    header("Location: ../../index.php");
} else {
    // Nếu có lỗi, vẫn redirect
    header("Location: ../../index.php");
}
exit;