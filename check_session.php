<?php
require_once 'config.php';

header('Content-Type: application/json');

$response = [
    'isLoggedIn' => isset($_SESSION['user_id']),
    'userName' => $_SESSION['user_name'] ?? null
];

echo json_encode($response);