<?php
session_start();
// Check if user is verified/logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html"); // Redirect if they skipped verification
    exit();
}

// Get details from URL (e.g., payment.php?course=Guitar&fee=5000)
$course = isset($_GET['course']) ? $_GET['course'] : 'Selected Course';
$fee = isset($_GET['fee']) ? $_GET['fee'] : '0';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Payment | HC's Institute</title>
    <link rel="stylesheet" href="css/payment.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>

<div class="payment-card">
    <div class="payment-left">
        <h3 style="margin:0;">Scan & Pay</h3>
        <p style="font-size: 13px; opacity: 0.9;">Open any UPI App (GPay, PhonePe, Paytm)</p>
        
        <div class="qr-container">
            <img src="image/qr.png" alt="Payment QR Code">
        </div>

        <div class="upi-info">
            <p style="margin-bottom:5px; font-size:14px; opacity:0.8;">Official UPI ID</p>
            <div class="upi-id-box">
                <span id="upiId">hcinstitute@okaxis</span>
                <button class="copy-btn" onclick="copyUPI()">COPY</button>
            </div>
        </div>
    </div>

    <div class="payment-right">
        <div class="course-details">
            <h2>Enrollment Checkout</h2>
            <p style="color: #666; font-size: 14px;">Course: <strong><?php echo htmlspecialchars($course); ?></strong></p>
        </div>

        <form action="process_enrollment.php" method="POST">
            <input type="hidden" name="course_name" value="<?php echo htmlspecialchars($course); ?>">

            <div class="form-group">
                <label>Payable Amount (₹)</label>
                <input type="text" name="amount" value="<?php echo htmlspecialchars($fee); ?>" readonly>
            </div>

            <div class="form-group">
                <label>Transaction ID / UTR Number</label>
               <input type="text" name="transaction_id" placeholder="12-digit UPI Ref No." 
       pattern="\d{12}" title="Please enter exactly 12 digits" maxlength="12" required>
            </div>

            <div class="form-group">
                <label>Payer Name (As per Bank)</label>
                <input type="text" name="payer_name" placeholder="Name of the person paying" required>
            </div>

            <button type="submit" name="submit_payment" class="pay-button">
                I Have Paid - Submit
            </button>
        </form>

        <div class="secure-text">
            <i class="fa fa-lock"></i> Secured verification by HC's Admin
        </div>
    </div>
</div>



<script>
    function copyUPI() {
        const upiText = document.getElementById('upiId').innerText;
        navigator.clipboard.writeText(upiText).then(() => {
            alert('UPI ID Copied!');
        });
    }

    document.addEventListener("DOMContentLoaded", function() {
    const urlParams = new URLSearchParams(window.location.search);
    
    if (urlParams.get('error') === 'duplicate') {
        alert("This Transaction ID has already been submitted. Please check your Transaction ID and try again.");
        // Clean the URL
        window.history.replaceState({}, document.title, window.location.pathname + window.location.search.split('&error')[0]);
    }

    if (urlParams.get('success') === 'payment') {
        alert("Payment submitted successfully! Redirecting to home...");
        window.location.href = 'index.html';
    }
});
</script>

</body>
</html>