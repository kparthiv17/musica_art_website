<?php
session_start();
include 'db_connect.php';

if (isset($_POST['submit_payment'])) {
    // Ensure the user is logged in or verified
    $transaction_id = $_POST['transaction_id'];

    // 1. Check if Transaction ID already exists
    $check_sql = "SELECT id FROM enrollments WHERE transaction_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $transaction_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        // Redirect with an error flag
        header("Location: payment.php?error=duplicate&course=" . urlencode($_POST['course_name']) . "&fee=" . $_POST['amount']);
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $course = $_POST['course_name'];
    $amount = $_POST['amount'];
    $tr_id = $_POST['transaction_id'];
    $payer = $_POST['payer_name'];

    // Using Prepared Statements for Security
    $stmt = $conn->prepare("INSERT INTO enrollments (user_id, course_name, amount, transaction_id, payer_name) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $user_id, $course, $amount, $tr_id, $payer);

    if ($stmt->execute()) {
        echo "<script>alert('Payment Details Submitted! Admin will verify soon.'); window.location.href='index.html';</script>";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>