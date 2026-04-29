<?php
session_start();
include 'db_connect.php';

// --- 1. SIGNUP LOGIC ---
if (isset($_POST['reg_user'])) {
    $full_name     = mysqli_real_escape_string($conn, $_POST['full_name']);
    $gender        = mysqli_real_escape_string($conn, $_POST['gender']);
    $birth_date    = mysqli_real_escape_string($conn, $_POST['birth_date']);
    $phone         = mysqli_real_escape_string($conn, $_POST['phone']);
    $aadhar_number = mysqli_real_escape_string($conn, $_POST['aadhar_number']); 
    $email         = mysqli_real_escape_string($conn, $_POST['email']);
    $username      = mysqli_real_escape_string($conn, $_POST['username']);
    $address       = mysqli_real_escape_string($conn, $_POST['address']);
    $password      = $_POST['password'];


    // Check if user already exists (Username, Email, or Aadhar)
    $user_check = "SELECT * FROM users WHERE username='$username' OR email='$email' OR aadhar_number='$aadhar_number' LIMIT 1";
    $result = mysqli_query($conn, $user_check);
    $user = mysqli_fetch_assoc($result);


    if ($user) {
        if ($user['username'] === $username) { echo "<script>alert('Username already exists!'); window.history.back();</script>"; exit(); }
        if ($user['email'] === $email) { echo "<script>alert('Email already exists!'); window.history.back();</script>"; exit(); }
        if ($user['aadhar_number'] === $aadhar_number) { echo "<script>alert('Aadhar Number already registered!'); window.history.back();</script>"; exit(); }
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $query = "INSERT INTO users (full_name, gender, birth_date, phone, aadhar_number, email, username, address, password) 
              VALUES ('$full_name', '$gender', '$birth_date', '$phone', '$aadhar_number', '$email', '$username', '$address', '$hashed_password')";

    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Account created successfully! Please login.'); window.location.href='index.html';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}


// --- 2. LOGIN LOGIC ---
if (isset($_POST['login_user'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    // Admin check
    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['user_id'] = 'ADMIN';
        $_SESSION['username'] = 'admin';
        $_SESSION['full_name'] = 'System Administrator';
        $_SESSION['role'] = 'admin';
        header('location: admin.php');
        exit();
    }

    $query = "SELECT * FROM users WHERE username='$username'";
    $results = mysqli_query($conn, $query);

    if (mysqli_num_rows($results) == 1) {
        $user = mysqli_fetch_assoc($results);
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['username']  = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];

            // --- THE FIX: Make user visible again upon login ---
            mysqli_query($conn, "UPDATE users SET is_visible = 1 WHERE id = " . $user['id']);

            header('location: user_dashbord.php');
            exit(); // Good practice to exit after header
        } else {
            echo "<script>alert('Wrong username/password combination'); window.location.href='index.html';</script>";
        }
    } else {
        echo "<script>alert('User not found. Please Signup first.'); window.location.href='index.html';</script>";
    }
}


// --- 3. FORGOT PASSWORD (AADHAR VERIFICATION) ---
if (isset($_POST['reset_password'])) {
    $username      = mysqli_real_escape_string($conn, $_POST['username']);
    $aadhar_verify = mysqli_real_escape_string($conn, $_POST['aadhar_verify']);
    $new_password  = $_POST['new_password'];


    // Verify if the username and Aadhar Number belong to the same account
    $query = "SELECT * FROM users WHERE username='$username' AND aadhar_number='$aadhar_verify' LIMIT 1";
    $result = mysqli_query($conn, $query);


    if (mysqli_num_rows($result) == 1) {
        // Success: Encrypt and update the new password
        $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_query = "UPDATE users SET password='$new_hashed_password' WHERE username='$username'";
        
        if (mysqli_query($conn, $update_query)) {
            echo "<script>alert('Password updated successfully! Please login with your new password.'); window.location.href='index.html';</script>";
        } else {
            echo "<script>alert('Database error. Please try again.'); window.history.back();</script>";
        }
    } else {
        // Failure: Data mismatch
        echo "<script>alert('Verification failed! Username and Aadhar Number do not match.'); window.history.back();</script>";
    }
}
?>