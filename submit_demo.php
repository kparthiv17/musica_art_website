<?php
// Turn off error reporting to the screen so it doesn't break JSON
error_reporting(0);
include 'db_connect.php';

// Clear any previous output (like accidental spaces or warnings)
if (ob_get_length()) ob_clean();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $course = mysqli_real_escape_string($conn, $_POST['course']);
    $whatsapp = mysqli_real_escape_string($conn, $_POST['whatsapp_number']);
    $demo_date = mysqli_real_escape_string($conn, $_POST['demoDate']);
    $time_slot = mysqli_real_escape_string($conn, $_POST['time_slot']);
    $instrument_or_art = mysqli_real_escape_string($conn, $_POST['instrument_or_art'] ?? '');

    $query = "INSERT INTO demo_requests (user_id, name, course, instrument_or_art, demo_date, time_slot, whatsapp_number, status) 
              VALUES ('$user_id', '$full_name', '$course', '$instrument_or_art', '$demo_date', '$time_slot', '$whatsapp', 'pending')";

    if (mysqli_query($conn, $query)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid Request']);
}
exit(); // Ensure nothing else is printed
?>