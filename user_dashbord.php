<?php
session_start();
include 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch current user details
// Using prepared statements is safer, but keeping your mysqli_query style for consistency
$query = "SELECT * FROM users WHERE id = '$user_id'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

// Fetch Enrollments
$my_enrolls = mysqli_query($conn, "SELECT * FROM enrollments WHERE user_id = '$user_id' ORDER BY id DESC");
$enroll_count = mysqli_num_rows($my_enrolls);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | HC's Institute</title>
    <link rel="stylesheet" href="css/user_dashbord.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>

    <div class="dashboard-wrapper">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    HC's Institute
                    <span>Music & Arts</span>
                </div>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-item active" onclick="switchTab('dashboard')">
                    <i class="fa fa-home"></i>
                    <span>Dashboard</span>
                </div>
                <div class="nav-item" onclick="switchTab('my-courses')">
                    <i class="fa fa-book"></i>
                    <span>My Courses</span>
                </div>
                <div class="nav-item" onclick="switchTab('demo-status')">
                    <i class="fa fa-calendar-check-o"></i>
                    <span>Demo Status</span>
                </div>
                <div class="nav-item" onclick="switchTab('edit-profile')">
                    <i class="fa fa-user"></i>
                    <span>Edit Profile</span>
                </div>
            </nav>

            <div class="logout-section">
                <div class="logout-btn" onclick="confirmLogout()">
                    <i class="fa fa-sign-out"></i>
                    <span>Logout</span>
                </div>
            </div>
        </aside>




        <div class="main-content">
            <header class="dashboard-header">
                <div style="display: flex; align-items: center;">
                    <div class="mobile-menu-btn" onclick="toggleSidebar()">
                        <i class="fa fa-bars"></i>
                    </div>
                    <div class="header-logo-mobile">HC's Institute</div>
                </div>
                <button class="header-contact-btn" onclick="openContactModal()">
                    <i class="fa fa-envelope-o"></i> Contact
                </button>
            </header>

            <div class="content-scroll" id="main-scroll">
                



                <div id="view-dashboard">
                    <div class="welcome-banner">
                        <h2>Welcome, <span style="color: var(--accent-color);"><?php echo htmlspecialchars($user['full_name']); ?></span>!</h2>
                        <p>Track your progress and manage your music classes.</p>
                    </div>
                </div>






                <div id="view-my-courses" style="display: none;">
                    <h2 style="font-family: 'Playfair Display', serif; margin-bottom: 20px;">My Enrollments & Payments</h2>
                    <div class="enrollment-list">
                        <?php if ($enroll_count > 0): ?>
                            <?php while($enroll = mysqli_fetch_assoc($my_enrolls)): ?>
                                <div class="enrollment-card" style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 15px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                        <div>
                                            <h3 style="color: #653f00; margin-bottom: 5px;"><?php echo htmlspecialchars($enroll['course_name']); ?></h3>
                                            <p style="font-size: 0.9rem; color: #666;">
                                                <strong>Transaction ID:</strong> <?php echo htmlspecialchars($enroll['transaction_id']); ?><br>
                                                <strong>Amount:</strong> ₹<?php echo htmlspecialchars($enroll['amount']); ?><br>
                                                <strong>Date:</strong> <?php echo date("M d, Y", strtotime($enroll['created_at'])); ?>
                                            </p>
                                        </div>
                                        <div>
                                            <span class="pay-status pay-<?php echo $enroll['status']; ?>" style="padding: 5px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: bold;">
                                                <?php echo strtoupper($enroll['status']); ?>
                                            </span>
                                        </div>
                                    </div>

                                        

                                    <?php if($enroll['status'] == 'approved'): ?>
                        <div style="margin-top: 15px; padding-top: 10px; border-top: 1px dashed #ddd; display: flex; justify-content: flex-end; align-items: center; gap: 10px;">
                            
                            <?php if(isset($enroll['cert_approved']) && $enroll['cert_approved'] == 1): ?>
                                <a href="generate_certificate.php?id=<?php echo $enroll['id']; ?>" class="btn-download" target="_blank" style="background: #27ae60; color: white; padding: 8px 15px; border-radius: 4px; text-decoration: none; font-size: 0.85rem; display: inline-block;">
                                    <i class="fa fa-certificate"></i> Download Certificate
                                </a>
                            <?php else: ?>
                                <button onclick="showCertWarning()" class="btn-download" style="background: #95a5a6; color: white; padding: 8px 15px; border: none; border-radius: 4px; cursor: pointer; font-size: 0.85rem; display: inline-block;">
                                    <i class="fa fa-certificate"></i> Download Certificate
                                </button>
                            <?php endif; ?>

                            <a href="pdf/generate_invoice.php?id=<?php echo $enroll['id']; ?>" class="btn-download" style="background: #653f00; color: white; padding: 8px 15px; border-radius: 4px; text-decoration: none; font-size: 0.85rem; display: inline-block;">
                                <i class="fa fa-file-pdf-o"></i> Download Invoice
                            </a>
                        </div>

                    <?php elseif($enroll['status'] == 'pending'): ?>
                        <div style="margin-top: 15px; padding-top: 10px; border-top: 1px dashed #ddd;">
                            <span style="color: #d4a017; font-size: 0.85rem;"><i class="fa fa-clock-o"></i> Verification in progress. Documents will be available once approved.</span>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="card" style="text-align: center; padding: 40px;">
                <i class="fa fa-money" style="font-size: 3rem; color: #ccc; margin-bottom: 15px;"></i>
                <p>You haven't enrolled in any courses yet.</p>
            </div>
        <?php endif; ?>
    </div>
</div>





                <div id="view-demo-status" style="display: none;">
                    <h2 style="font-family: 'Playfair Display', serif; margin-bottom: 20px;">Your Demo Requests</h2>
                    <div class="dashboard-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;">
                        <?php
                        $demo_query = "SELECT * FROM demo_requests WHERE user_id = '$user_id' ORDER BY id DESC";
                        $demo_res = mysqli_query($conn, $demo_query);
                        
                        if (mysqli_num_rows($demo_res) > 0):
                            while($row = mysqli_fetch_assoc($demo_res)):
                        ?>
                            <div class="card" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                                <h3><?php echo htmlspecialchars(ucfirst($row['course'])); ?> Demo</h3>
                                <div class="value" style="font-size: 1.1rem; margin-bottom: 10px; color: #653f00;">
                                    <?php echo htmlspecialchars($row['instrument_or_art'] ?? 'General'); ?>
                                </div>
                                <p style="color: #666; line-height: 1.6;">
                                    <i class="fa fa-calendar"></i> <?php echo date("M d, Y", strtotime($row['demo_date'])); ?><br>
                                    <i class="fa fa-clock-o"></i> <?php echo htmlspecialchars($row['time_slot'] ?? 'TBD'); ?>
                                </p>
                                <br>
                                <span class="status-badge status-<?php echo $row['status']; ?>">
                                    <?php echo ucfirst($row['status']); ?>
                                </span>
                            </div>
                        <?php 
                            endwhile; 
                        else: 
                        ?>
                            <div class="card" style="grid-column: 1 / -1; text-align: center; padding: 40px;">
                                <i class="fa fa-calendar-times-o" style="font-size: 3rem; color: #ccc; margin-bottom: 15px;"></i>
                                <p>You haven't booked any demos yet.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>






                <div id="view-edit-profile" class="profile-section" style="display: none;">
                    <h2 style="font-family: 'Playfair Display', serif; margin-bottom: 25px;">Edit Profile</h2>
                    <form action="update_profile.php" method="POST">
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label style="display: block; margin-bottom: 5px;">Full Name</label>
                            <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                        </div>
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label style="display: block; margin-bottom: 5px;">Email Address</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                        </div>
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label style="display: block; margin-bottom: 5px;">Phone Number</label>
                            <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                        </div>
                        <button type="submit" class="btn-save" style="background: #653f00; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;">Save Changes</button>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <div class="modal-overlay" id="customModal">
        <div class="modal-box">
            <h3 id="modalTitle">Title</h3>
            <p id="modalMessage">Message content goes here.</p>
            <div id="modalActions"></div>
        </div>
    </div>

    <script src="js/user_dashbord.js"></script>
</body>
</html>