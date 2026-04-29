<?php
require('pdf/fpdf.php');
include 'db_connect.php';
session_start();

// --- 1. SECURITY CHECK ---
if (!isset($_SESSION['user_id'])) {
    die("Access Denied. Please log in.");
}

$user_id = $_SESSION['user_id'];
$enroll_id = mysqli_real_escape_string($conn, $_GET['id']);

// --- 2. FETCH DATA ---
$query = "SELECT e.*, u.full_name
          FROM enrollments e
          JOIN users u ON e.user_id = u.id
          WHERE e.id = '$enroll_id'";

$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

// --- 3. VALIDATION ---
if (!$data || ($data['user_id'] != $user_id && $_SESSION['role'] !== 'admin')) {
    die("You are not authorized to view this certificate.");
}

if ($data['cert_approved'] == 0) {
    die("This certificate has not been approved by the admin yet.");
}

// --- 4. PDF GENERATION ---
$pdf = new FPDF('L', 'mm', 'A4'); // Landscape orientation
$pdf->SetAutoPageBreak(false);    // DISABLE auto page break to keep it on one page
$pdf->AddPage();

// Draw a decorative double border
$pdf->SetLineWidth(1.5);
$pdf->Rect(10, 10, 277, 190); // Outer
$pdf->SetLineWidth(0.5);
$pdf->Rect(13, 13, 271, 184); // Inner

// Header - Reduced top margin from 20 to 15
$pdf->SetY(25); 
$pdf->SetFont('Arial', 'B', 30); // Reduced font size slightly
$pdf->SetTextColor(101, 63, 0); 
$pdf->Cell(0, 15, "HC'S INSTITUTE OF MUSIC & ARTS", 0, 1, 'C');

$pdf->SetFont('Arial', 'I', 16);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(0, 12, "CERTIFICATE OF COMPLETION", 0, 1, 'C');

$pdf->Ln(5);
$pdf->SetFont('Arial', '', 14);
$pdf->Cell(0, 10, "This is to certify that", 0, 1, 'C');

// Student Name - Reduced cell height
$pdf->SetFont('Times', 'B', 36);
$pdf->SetTextColor(139, 69, 19); 
$pdf->Cell(0, 20, strtoupper($data['full_name']), 0, 1, 'C');

// Course Info
$pdf->SetFont('Arial', '', 14);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(0, 10, "has successfully completed the professional course in", 0, 1, 'C');

$pdf->SetFont('Arial', 'B', 20);
$pdf->Cell(0, 15, $data['course_name'], 0, 1, 'C');

// THE GRADE
// --- THE GRADE (Reference point) ---
$pdf->SetFont('Arial', 'B', 26);
$pdf->SetTextColor(200, 0, 0); 
$pdf->Cell(0, 12, $data['grade'], 0, 1, 'C');

// --- SIGNATURES SECTION (Centered Below Grade) ---
$pdf->Ln(10); // Gap between Grade and Signature
$currentY = $pdf->GetY();

// 1. Place the Signature Image in the Middle
// Page width is 297mm. To center a 40mm image: (297 - 40) / 2 = 128.5
$pdf->Image('image/signature.png', 128.5, $currentY, 40, 15); 

// 2. Draw the Line and Text (Centered)
$pdf->SetY($currentY + 12); // Position the line slightly below the top of the signature
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, "__________________________", 0, 1, 'C'); // Width 0 centers on whole page

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 5, "Director, HC's Institute", 0, 1, 'C');

// Footer Date
$pdf->SetY(185);
$pdf->SetFont('Arial', 'I', 10);
$pdf->Cell(0, 10, "Certificate issued on: " . date("d-m-Y"), 0, 0, 'C');

// Output the PDF
$pdf->Output('I', 'HC_Certificate_' . $data['full_name'] . '.pdf');
?>