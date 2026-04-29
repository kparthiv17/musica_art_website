<?php
session_start();
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
    $enroll_id = $_POST['id'];
    $grade = $_POST['grade'];

    $query = "UPDATE enrollments SET grade = ?, cert_approved = 1 WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "si", $grade, $enroll_id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "success";
    } else {
        echo "error";
    }
}
?>