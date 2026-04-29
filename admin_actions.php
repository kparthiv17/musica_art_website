<?php

        use PHPMailer\PHPMailer\PHPMailer;
        use PHPMailer\PHPMailer\Exception;
session_start();
include 'db_connect.php';

// --- 1. ADMIN AUTHENTICATION CHECK ---
// Ensures only a logged-in admin can execute these actions
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    exit("Unauthorized access");
}


// Merge POST and GET so we can handle both types of requests
$request = array_merge($_GET, $_POST);

if (isset($request['action']) && isset($request['id'])) {
    $id = mysqli_real_escape_string($conn, $request['id']);
    $action = $request['action'];

    // --- User & Demo Logic (Existing) ---
    if ($action === 'delete_user') {
        mysqli_query($conn, "UPDATE users SET is_visible = 0 WHERE id = '$id'");
        header("Location: admin.php?msg=hidden#all-students");
        exit();
    } 
    elseif ($action === 'accept_demo') {
        mysqli_query($conn, "UPDATE demo_requests SET status = 'approved' WHERE id = '$id'");
        header("Location: admin.php#demo-requests");
        exit();
    }
    elseif ($action === 'reject_demo') {
        mysqli_query($conn, "UPDATE demo_requests SET status = 'rejected' WHERE id = '$id'");
        header("Location: admin.php#demo-requests");
        exit();
    }

    // --- Enrollment Payment Logic (The Fix) ---
    elseif ($action === 'approve_payment') {
        $stmt = $conn->prepare("UPDATE enrollments SET status = 'approved' WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo "success"; 
            exit();
        }
    } 
    elseif ($action === 'reject_payment') {
        $stmt = $conn->prepare("UPDATE enrollments SET status = 'rejected' WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo "success";
            exit();
        }
    }




// Add this inside the action checking block in admin_actions.php
elseif ($action === 'send_invoice_email') {
    // 1. Correct paths and include libraries
    require('pdf/fpdf.php'); 
    require 'PHPMailer/Exception.php';
    require 'PHPMailer/PHPMailer.php';
    require 'PHPMailer/SMTP.php';

    // 2. UPDATED QUERY: Added u.phone and e.created_at (Crucial to stop the warnings)
    $query = "SELECT e.*, u.full_name, u.email, u.phone 
              FROM enrollments e 
              JOIN users u ON e.user_id = u.id 
              WHERE e.id = '$id' AND e.status = 'approved'";
    $res = mysqli_query($conn, $query);
    $data = mysqli_fetch_assoc($res);

    if (!$data) { 
        exit("Error: Invoice not found or enrollment not approved."); 
    }

    // 3. Generate PDF in Memory
    $pdf = new FPDF('P', 'mm', 'A4');
    $pdf->AddPage();

    // Design Header
    $pdf->SetFont('Arial', 'B', 20);
    $pdf->SetTextColor(101, 63, 0); 
    $pdf->Cell(0, 10, "HC'S INSTITUTE OF MUSIC & ARTS", 0, 1, 'C');

    $pdf->SetFont('Arial', '', 10);
    $pdf->SetTextColor(100);
    $pdf->Cell(0, 5, "Official Payment Receipt", 0, 1, 'C');
    $pdf->Ln(10);
    $pdf->Line(10, 35, 200, 35);

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetTextColor(0);
    $pdf->Cell(100, 7, "Bill To:", 0, 0);
    $pdf->Cell(0, 7, "Details:", 0, 1);

    // No more warnings here because 'created_at' and 'phone' are now in the $data array
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(100, 5, $data['full_name'], 0, 0);
    $pdf->Cell(0, 5, "Date: " . date("d-M-Y", strtotime($data['created_at'])), 0, 1);
    
    $pdf->Cell(100, 5, "Email: " . $data['email'], 0, 0);
    $pdf->Cell(0, 5, "Receipt #: " . str_pad($data['id'], 6, "0", STR_PAD_LEFT), 0, 1);
    
    $pdf->Cell(100, 5, "Phone: " . $data['phone'], 0, 0);
    $pdf->Cell(0, 5, "Transaction ID: " . $data['transaction_id'], 0, 1);
    $pdf->Ln(15);

    // Table
    $pdf->SetFillColor(238, 210, 164);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(140, 10, "Course Name", 1, 0, 'C', true);
    $pdf->Cell(50, 10, "Amount Paid", 1, 1, 'C', true);

    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(140, 15, $data['course_name'], 1, 0, 'L');
    $pdf->Cell(50, 15, "INR " . $data['amount'], 1, 1, 'C');

    $pdf->Ln(20);
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(0, 10, "This is a computer-generated receipt and does not require a physical signature.", 0, 1, 'C');
    
    // Capture the PDF string
    $pdf_content = $pdf->Output('S'); 

    // 4. Send Email
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'pkadia525@gmail.com'; // Your email
        $mail->Password = 'amdw czyl baql agem'; // Your app password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('pkadia525@gmail.com', "HC's Institute");
        $mail->addAddress($data['email']);


        
        $mail->Subject = "Payment Receipt: " . $data['course_name'];
        $mail->Body    = "Dear " . $data['full_name'] . ",\n\nPlease find attached the receipt for your enrollment.\n\nThank you!";
        
        $safe_filename = str_replace(' ', '_', $data['full_name']) . '_Receipt.pdf';
        $mail->addStringAttachment($pdf_content, $safe_filename);

        if ($mail->send()) {
            echo "success";
        }
    } catch (Exception $e) {
        echo "Mailer Error: " . $mail->ErrorInfo;
    }
    exit();
}
}
// Redirect back if no valid action was matched
header("Location: admin.php");
exit();
?>