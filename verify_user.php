<?php
session_start();
include 'db_connect.php'; // Ensure this connects to 'hc_institute'

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    
    $course = $_POST['course'];
    $fee = $_POST['fee'];

    // Updated table name to 'users' based on your database image
    $query = "SELECT id FROM users WHERE username='$username' AND email='$email' AND phone='$phone'";
    $result = mysqli_query($conn, $query);

    if ($row = $result->fetch_assoc()) {
    $_SESSION['user_id'] = $row['id'];
    // User exists! Send to payment
    header("Location: payment.php?course=" . urlencode($course) . "&fee=" . urlencode($fee));
    exit();
} else {
    
    // User does NOT exist. Show alert and open Signup
    echo "<script>
            alert('Record not found. Please Signup first.');
            window.location.href='login.html?action=signup'; 
          </script>";
    exit();
}
}
?>