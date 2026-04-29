        <?php
        session_start();
        include 'db_connect.php';

        // --- 1. ADMIN AUTHENTICATION CHECK ---
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header('location: index.html');
            exit();
        }

        // --- 2. DATA FETCHING ---

        // 1. Fetch All Students (Fixed: Using ID ASC as requested)
        $students_result = mysqli_query($conn, "SELECT id, full_name, phone, email, aadhar_number FROM users WHERE is_visible = 1 ORDER BY id ASC");
        if (!$students_result) { 
            die("Query failed (All Students): " . mysqli_error($conn)); 
        }

        // 2. Fetch New Registrations (Fixed: Using 'created_at' instead of 'date_joined')
        $new_reg_query = "SELECT id, full_name, email, phone 
                          FROM users 
                          WHERE created_at >= NOW() - INTERVAL 1 DAY 
                          AND is_visible = 1 
                          ORDER BY id ASC";
        $new_reg_result = mysqli_query($conn, $new_reg_query);
        if (!$new_reg_result) {
            die("Query failed (New Registrations): " . mysqli_error($conn));
        }

        // 3. Fetch Demo Requests
        $demo_result = mysqli_query($conn, "SELECT * FROM demo_requests WHERE status = 'pending' ORDER BY id ASC");

        // 4. Fetch All Demo History (Approved/Rejected/Pending)
        $all_demo_history_result = mysqli_query($conn, "SELECT * FROM demo_requests ORDER BY id DESC");
        $all_demo_count = mysqli_num_rows($all_demo_history_result);

        // Counts for Overview
        $total_students = mysqli_num_rows($students_result);
        $new_reg_count = ($new_reg_result) ? mysqli_num_rows($new_reg_result) : 0;
        $demo_count = ($demo_result) ? mysqli_num_rows($demo_result) : 0;
        ?>


        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Admin Dashboard | HC's Institute</title>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
            <link rel="stylesheet" href="css/admin.css">
            <style>
                .view-section { display: none; }
                .view-section.active { display: block; }
            </style>
        </head>
        <body>

            <div class="admin-wrapper">
                <aside class="sidebar" id="sidebar">
                     <div class="sidebar-header">
                        <div>
                            <h2>HC's Admin</h2>
                            <span>INSTITUTE PANEL</span>
                        </div>
                         <i class="fa fa-times sidebar-close" onclick="toggleSidebar()"></i>
                    </div>
                    
                    <ul class="sidebar-menu">
                        <li class="menu-item active" onclick="showSection('overview', this)">
                            <i class="fa fa-dashboard"></i> Overview
                        </li>
                        <li class="menu-item" onclick="showSection('all-students', this)">
                            <i class="fa fa-users"></i> All Students
                        </li>
                        <li class="menu-item" onclick="showSection('new-registrations', this)">
                            <i class="fa fa-user-plus"></i> New Registrations
                        </li>
                        <li class="menu-item" onclick="showSection('demo-requests', this)">
                            <i class="fa fa-calendar"></i> Demo Requests
                        </li>
                        <li class="menu-item" onclick="showSection('all-demo-students', this)">
                           <i class="fa fa-address-book"></i> Demo Schedules & History 
                        </li>
                        <li class="menu-item" onclick="showSection('enrollments', this)">
                           <i class="fa fa-money"></i> Course Payments
                        </li>
                        <li class="menu-item" onclick="showSection('all-enrollments', this)">
                           <i class="fa fa-check-square-o"></i> Enrollment History
                        </li>

                        <li class="menu-item" onclick="showSection('certificate-center', this)">
    <i class="fa fa-certificate"></i> Certificate Center
</li>
                                       </ul>
                    <div class="logout-btn" style="cursor:pointer;" onclick="openLogoutModal()">
                        <i class="fa fa-sign-out"></i> Logout
                    </div>
                </aside>

                <div class="main-area" id="main-area">
                   <div class="top-bar">
                        <div style="display: flex; align-items: center;">
                            <!-- Hamburger Menu -->
                            <i class="fa fa-bars menu-toggle" onclick="toggleSidebar()"></i>
                            <div class="admin-title">Dashboard</div>
                        </div>
                        <div class="admin-profile">Welcome, Admin</div>
                    </div>


                    <div class="content-scroll">
                        <div id="overview" class="view-section active">
                            <h2 class="section-title">Overview</h2>
                            <div class="stats-grid">
                                <div class="stat-card"><h3>Total Students</h3><div class="number"><?php echo $total_students; ?></div></div>
                                <div class="stat-card"><h3>New (24h)</h3><div class="number" style="color:orange;"><?php echo $new_reg_count; ?></div></div>
                                <div class="stat-card"><h3>Pending Demos</h3><div class="number" style="color:red;"><?php echo $demo_count; ?></div></div>
                            </div>
                        </div>



                           <!-- All Students Section -->
                        <div id="all-students" class="view-section">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 2px solid #eee;">
                                <h2 class="section-title" style="margin: 0; font-size: 28px; color: #653f00;">Registered Students</h2>
                                
                                <div class="search-wrapper" style="position: relative; display: flex; align-items: center;">
                                    <input type="text" id="studentSearch" onkeyup="filterStudents()" placeholder="Search name..." 
                                        style="width: 100%; padding: 10px 40px 10px 15px; border: 1.5px solid #653f00; border-radius: 25px; outline: none; font-size: 14px; background: #fff;">
                                    <i class="fa fa-search" style="position: absolute; right: 15px; color: #653f00;"></i>
                                </div>
                            </div>

            <div class="table-container">
                <table id="studentTable">
                    <thead>
                        <tr>
                            <th>SR. NO</th> 
                            <th>Name</th>
                            <th>Contact Details</th>
                            <th>Aadhar Number</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($total_students > 0): ?>
                            <?php 
                            $serial_no = 1; 
                            mysqli_data_seek($students_result, 0); // Reset pointer for clean start
                            while($row = mysqli_fetch_assoc($students_result)): 
                            ?>
                            <tr>
                                <td>#<?php echo $serial_no++; ?></td> 
                                <td class="student-name" style="font-weight: 600;"><?php echo htmlspecialchars($row['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['phone']); ?><br><small style="color: #666;"><?php echo htmlspecialchars($row['email']); ?></small></td>
                                <td><?php echo htmlspecialchars($row['aadhar_number']); ?> </td>
                                <td>
                                    <button class="btn-reject" onclick="adminAction('delete_user', <?php echo $row['id']; ?>)">Delete</button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="text-align:center; padding: 30px; color: #888;">No students registered yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                
                <div id="noResults" style="display:none; text-align:center; padding: 40px; color: #888; border: 1px dashed #ccc; margin-top: 10px; border-radius: 8px;">
                    <i class="fa fa-search" style="font-size: 24px; display: block; margin-bottom: 10px;"></i>
                    No students found matching that name.
                </div>
            </div>
        </div>




                        <div id="new-registrations" class="view-section">
                            <h2 class="section-title">New Registrations (Last 24 Hours)</h2>
                            <div class="table-container">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>SR. NO</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if($new_reg_count > 0): ?>
                                            <?php 
                                            $new_serial_no = 1; 
                                            while($reg = mysqli_fetch_assoc($new_reg_result)): 
                                            ?>
                                            <tr>
                                                <td>#<?php echo $new_serial_no++; ?></td>
                                                <td><?php echo htmlspecialchars($reg['full_name']); ?></td>
                                                <td><?php echo htmlspecialchars($reg['email']); ?></td>
                                                <td><?php echo htmlspecialchars($reg['phone']); ?></td>
                                            </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr><td colspan="4" style="text-align:center; color:gray; padding:20px;">No new students in the last 24 hours.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>



                        <div id="demo-requests" class="view-section">
                            <h2 class="section-title">Demo Requests</h2>
                            <div class="table-container">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Course</th>
                                            <th>Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if($demo_count > 0): ?>
                                            <?php while($demo = mysqli_fetch_assoc($demo_result)): ?>
                                            <tr>
                                                <td>#<?php echo $demo['id']; ?></td>
                                                <td><?php echo htmlspecialchars($demo['name']); ?></td>
                                                <td><?php echo htmlspecialchars($demo['course']); ?></td>
                                                <td><?php echo $demo['time_slot']; ?></td>
                                                <td>
                                                    <button class="btn-approve" onclick="adminAction('accept_demo', <?php echo $demo['id']; ?>)">Accept</button>
                                                    <button class="btn-reject" onclick="adminAction('reject_demo', <?php echo $demo['id']; ?>)">Reject</button>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr><td colspan="5" style="text-align:center;">No demo requests found.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>



<div id="certificate-center" class="view-section">
    <h2 class="section-title">Certificate Issuance</h2>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Course</th>
                    <th>Grade</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $cert_res = mysqli_query($conn, "SELECT e.*, u.full_name FROM enrollments e JOIN users u ON e.user_id = u.id WHERE e.status = 'approved' ORDER BY e.id DESC");
                if(mysqli_num_rows($cert_res) > 0):
                    while($row = mysqli_fetch_assoc($cert_res)):
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['course_name']); ?></td>
                    <td>
                        <select id="grade_<?php echo $row['id']; ?>" style="padding: 6px; border-radius: 4px; border: 1px solid #ddd; outline: none;">
                            <option value="A+" <?php if($row['grade'] == 'A+') echo 'selected'; ?>>A+</option>
                            <option value="A" <?php if($row['grade'] == 'A') echo 'selected'; ?>>A</option>
                            <option value="B" <?php if($row['grade'] == 'B') echo 'selected'; ?>>B</option>
                            <option value="C" <?php if($row['grade'] == 'C') echo 'selected'; ?>>C</option>
                        </select>
                    </td>
                    <td>
                        <?php echo ($row['cert_approved']) ? 
                            '<span style="color:green; font-weight:bold; font-size: 12px; text-transform: uppercase;">Approved</span>' : 
                            '<span style="color:orange; font-weight:bold; font-size: 12px; text-transform: uppercase;">Pending</span>'; ?>
                    </td>
                    <td>
                        <div class="cert-actions" style="display: flex; gap: 10px; align-items: center;">
                            <button class="btn-approve" onclick="approveCertificate(<?php echo $row['id']; ?>)" style="min-width: 140px; padding: 8px 12px;">
                                <i class="fa fa-refresh"></i>Update & approve
                            </button>
                            
                            <?php if($row['cert_approved']): ?>
                                <a href="generate_certificate.php?id=<?php echo $row['id']; ?>" target="_blank" class="btn-approve" 
                                   style="background: #653f00; color: white; text-decoration: none; border-color: #4a2e00; min-width: 80px; padding: 8px 12px;">
                                    <i class="fa fa-eye"></i> View
                                </a>
                            <?php endif; ?>
                        </div>
                    </td> 
                </tr>
                <?php endwhile; else: ?>
                    <tr><td colspan="5" style="text-align:center; padding: 30px; color: #888;">No completed enrollments found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

            <div id="all-demo-students" class="view-section">

            <div>
                <h2 class="section-title" >Demo Request History</h2>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>SR. NO</th>
                            <th>Student Name</th>
                            <th>Course & Item</th>
                            <th>Scheduled Slot</th>
                            <th>WhatsApp</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($all_demo_count > 0): ?>
                            <?php 
                            $demo_history_sr = 1;
                            while($row = mysqli_fetch_assoc($all_demo_history_result)): 
                                // Color coding for status
                                $status_color = ($row['status'] == 'approved') ? 'green' : (($row['status'] == 'rejected') ? 'red' : 'orange');
                            ?>
                            <tr>
                                <td>#<?php echo $demo_history_sr++; ?></td>
                                <td style="font-weight: 600;"><?php echo htmlspecialchars($row['name']); ?></td>
                                <td>
                                    <strong><?php echo ucfirst(htmlspecialchars($row['course'])); ?></strong><br>
                                    <small><?php echo htmlspecialchars($row['instrument_or_art']); ?></small>
                                </td>
                                <td><?php echo $row['demo_date']; ?><br><small><?php echo $row['time_slot']; ?></small></td>
                                <td><?php echo htmlspecialchars($row['whatsapp_number']); ?></td>
                                <td>
                                    <span style="color: <?php echo $status_color; ?>; font-weight: bold; text-transform: uppercase; font-size: 12px;">
                                        <?php echo $row['status']; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6" style="text-align:center; padding: 30px; color: #888;">No demo requests found in history.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
                    </div>




    <div id="enrollments" class="view-section">
        <h2 class="section-title">Pending Enrollments</h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Course</th>
                        <th>TR ID</th>
                        <th>Amount</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
        <?php
        $pay_res = mysqli_query($conn, "SELECT e.*, u.full_name FROM enrollments e JOIN users u ON e.user_id = u.id WHERE e.status = 'pending'");
        
        // Check if there are rows first
        if(mysqli_num_rows($pay_res) > 0): 
            while($row = mysqli_fetch_assoc($pay_res)):
        ?>
            <tr>
                <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                <td><?php echo htmlspecialchars($row['course_name']); ?></td>
                <td><?php echo htmlspecialchars($row['transaction_id']); ?></td>
                <td>₹<?php echo htmlspecialchars($row['amount']); ?></td>
                <td>
                    <button class="btn-approve" onclick="paymentAction('approve', <?php echo $row['id']; ?>)">Verify</button>
                    <button class="btn-reject" onclick="paymentAction('reject', <?php echo $row['id']; ?>)">Decline</button>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="5" style="text-align:center; padding: 30px; color: #888;">No pending enrollments found.</td></tr>
        <?php endif; ?>
    </tbody>
            </table>
        </div>
    </div>



    <div id="all-enrollments" class="view-section">
        <div >
           
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 2px solid #eee;">
                                <h2 class="section-title" style="margin: 0; font-size: 28px; color: #653f00;">Enrollment History</h2>
                                
                                <div class="search-wrapper" style="position: relative; display: flex; align-items: center;">
                                    <input type="text" id="studentSearch" onkeyup="filterStudents()" placeholder="Search name..." 
                                        style="width: 100%; padding: 10px 40px 10px 15px; border: 1.5px solid #653f00; border-radius: 25px; outline: none; font-size: 14px; background: #fff;">
                                    <i class="fa fa-search" style="position: absolute; right: 15px; color: #653f00;"></i>
                                </div>
        </div>

        <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>SR. NO</th>
                    <th>Student Name</th>
                    <th>Course</th>
                    <th>Transaction ID</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Action / Grade</th> 
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetching ALL enrollments that are NOT pending (Approved or Rejected)
                $history_query = "SELECT e.*, u.full_name 
                                  FROM enrollments e 
                                  JOIN users u ON e.user_id = u.id 
                                  WHERE e.status != 'pending' 
                                  ORDER BY e.id DESC";
                $history_res = mysqli_query($conn, $history_query);
                
                if(mysqli_num_rows($history_res) > 0):
                    $h_sr = 1;
                    while($row = mysqli_fetch_assoc($history_res)):
                        $status_class = ($row['status'] == 'approved') ? 'color: green;' : 'color: red;';
                ?>
                <tr>
                    <td>#<?php echo $h_sr++; ?></td>
                    <td style="font-weight: 600;"><?php echo htmlspecialchars($row['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['course_name']); ?></td>
                    <td><small><?php echo htmlspecialchars($row['transaction_id']); ?></small></td>
                    <td>₹<?php echo htmlspecialchars($row['amount']); ?></td>
                    <td>
                        <span style="<?php echo $status_class; ?> font-weight: bold; text-transform: uppercase; font-size: 12px;">
                            <?php echo $row['status']; ?>
                        </span>
                    </td>
                    <td>
                        <?php if($row['status'] == 'approved'): ?>
                            <button class="btn-approve" onclick="sendInvoiceEmail(<?php echo $row['id']; ?>, this)" style="padding: 5px 10px; font-size: 11px; background: #eed2a4;">
                                <i class="fa fa-envelope"></i> Send Email
                            </button>
                        <?php else: ?>
                            <small style="color: #221715;">N/A</small>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                    <tr><td colspan="7" style="text-align:center; padding: 30px; color: #888;">No enrollment history available.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>




    </div>
        

            </div>


            <div id="logoutModal" class="modal-overlay" style="display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 1000; justify-content: center; align-items: center;">
                <div class="modal-content" id="modal-box" style="background: #fff; padding: 30px; border-radius: 8px; width: 350px; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.3);">
                    <div id="logout-content">
                        <i class="fa fa-sign-out" style="font-size: 3rem; color: #522505; margin-bottom: 15px;"></i>
                        <h3 style="margin-bottom: 10px;">Confirm Logout</h3>
                        <p style="color: #666; margin-bottom: 25px;">Are you sure you want to log out?</p>
                        <div style="display: flex; gap: 10px; justify-content: center;">
                            <button onclick="closeLogoutModal()" style="padding: 10px 20px; border: 1px solid #ddd; background: #eee; cursor: pointer; border-radius: 4px;">Cancel</button>
                            <button onclick="performLogout()" style="padding: 10px 20px; border: none; background: #522505; color: #fff; cursor: pointer; border-radius: 4px;">Yes, Logout</button>
                        </div>
                    </div>
                </div>
            </div>

            <script src="js/admin.js"></script> 
        </body>
        </html>