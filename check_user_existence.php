<?php
include 'db_connect.php';
header('Content-Type: application/json');

$phone = isset($_GET['phone']) ? mysqli_real_escape_string($conn, $_GET['phone']) : '';

if (empty($phone)) {
    echo json_encode(['exists' => false]);
    exit();
}

$query = "SELECT id FROM users WHERE phone = '$phone' LIMIT 1";
$result = mysqli_query($conn, $query);

if ($row = mysqli_fetch_assoc($result)) {
    // Return both existence and the ID
    echo json_encode(['exists' => true, 'user_id' => $row['id']]);
} else {
    echo json_encode(['exists' => false]);
}
?>