<?php


//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
    if(isset($_POST['send']))

    {

    $name    = isset($_POST['name']) ? $_POST['name'] : '';
    $email   = isset($_POST['email']) ? $_POST['email'] : '';
    $phone   = isset($_POST['phone']) ? $_POST['phone'] : '';
    $subject = isset($_POST['subject']) ? $_POST['subject'] : '';
    $msg     = isset($_POST['msg']) ? $_POST['msg'] : '';




//Load Composer's autoloader (created by composer, not included with PHPMailer)
require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

//Create an instance; passing `true` enables exceptions
$mail = new PHPMailer(true);

try {
    //Server settings
    $mail->isSMTP();                                            //Send using SMTP
    $mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
    $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
    $mail->Username   = 'pkadia525@gmail.com';                     //SMTP username
    $mail->Password   = 'amdw czyl baql agem';                               //SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
    $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`


    //Recipients
    $mail->setFrom('pkadia525@gmail.com', 'HC Institute Inquiry');
    $mail->addAddress('parthpatel452510@gmail.com');     //Add a recipient
   

   //Optional name

    //Content
    $mail->isHTML(true);                                  //Set email format to HTML
    $mail->Subject = "New Inquiry: $subject";
    $mail->Body    = "


    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden;'>
        <div style='background-color: #8B4513; color: #ffffff; padding: 20px; text-align: center;'>
            <h2 style='margin: 0;'>HC'S INSTITUTE OF MUSIC & ARTS</h2>
            <p style='margin: 5px 0 0;'>Student Inquiry Received</p>
        </div>
        <div style='padding: 20px; color: #333;'>
            <p style='border-bottom: 2px solid #8B4513; padding-bottom: 10px; font-weight: bold; font-size: 1.1rem;'>Sender Information</p>
            <table style='width: 100%; border-collapse: collapse;'>
                <tr>
                    <td style='padding: 10px 0; width: 30%; color: #666;'><strong>Full Name:</strong></td>
                    <td style='padding: 10px 0;'>$name</td>
                </tr>
                <tr>
                    <td style='padding: 10px 0; color: #666;'><strong>Email Address:</strong></td>
                    <td style='padding: 10px 0;'><a href='mailto:$email' style='color: #8B4513; text-decoration: none;'>$email</a></td>
                </tr>
                <tr>
                    <td style='padding: 10px 0; color: #666;'><strong>Phone Number:</strong></td>
                    <td style='padding: 10px 0;'>$phone</td>
                </tr>
                <tr>
                    <td style='padding: 10px 0; color: #666;'><strong>Inquiry Subject:</strong></td>
                    <td style='padding: 10px 0;'>$subject</td>
                </tr>
            </table>

            <p style='border-bottom: 2px solid #8B4513; padding-top: 20px; padding-bottom: 10px; font-weight: bold; font-size: 1.1rem;'>Message Content</p>
            <div style='background-color: #f9f9f9; padding: 15px; border-radius: 4px; border-left: 4px solid #8B4513; margin-top: 10px;'>
                " . nl2br($msg) . "
            </div>
        </div>
        <div style='background-color: #f1f1f1; padding: 15px; text-align: center; font-size: 0.8rem; color: #999;'>
            This email was generated from the HC's Institute contact form.
        </div>
    </div>

    " ;
  

    $mail->send();

        // REDIRECT to the same page with a success flag
        header("Location: contact.php?status=success");
        exit(); // Stop further script execution

    } catch (Exception $e) {
        header("Location: contact.php?status=error");
        exit();
    }
}

if (isset($_GET['status'])) {
    if ($_GET['status'] == 'success') {
        echo "<script>alert('Message has been sent successfully! Our team will contact you shortly.');</script>";
    } elseif ($_GET['status'] == 'error') {
        echo "<script>alert('Error: Message could not be sent. Please try again later.');</script>";
    }
    // Clean URL: Remove the ?status=success from the address bar
    echo "<script>window.history.replaceState({}, document.title, 'contact.php');</script>";
}
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>contact - HC'S INSTITUTE OF MUSIC & ARTS</title>
        <link rel="stylesheet" href="css/login.css">
		<link rel="stylesheet" href="css/contact.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" >

</head>
<body>
		 <!-- Header -->
    <header>
        <nav class="topnav">
            <div class="logo">
                <img src="image/logo2.png" alt="HC Institute Logo">
                <div class="logo-text">
                    <span class="logo-title">HC'S INSTITUTE OF</span>
                    <span class="logo-subtitle">MUSIC & ARTS</span>
                </div>
            </div>

            <div id="myLinks" class="nav-links">
                <a href="index.html">Home</a>
               <div class="dropdown">
                    <a  class="dropbtn">Courses <i class="fa fa-caret-down"></i></a>
                         <div class="dropdown-content">
                          <a href="music_course.html">Music Courses <i class="fa fa-caret-right"></i></a>
                         <a href="art_course.html">Art Courses <i class="fa fa-caret-right"></i></a>
                        </div>
                </div>
              <a href="javascript:void(0)" onclick="openLogin()">Login</a>
                <a href="about.html">About Us</a>
                <a href="contact.php" class="active">Contact</a>
            </div>

            <a href="javascript:void(0);" class="icon" onclick="myFunction()">
                <i class="fa fa-bars"></i>
            </a>
        </nav>
    </header>


    <!-- Contact Section -->
    <div class="contact-wrapper">
        <div class="contact-header">
            <h2>Get In Touch</h2>
            <p>Have questions about our courses or enrollment? We are here to help you start your musical & Art journey.</p>
        </div>

        <div class="contact-container">
            <!-- Left Column: Contact Info -->
            <div class="contact-info">
                <div class="info-item">
                    <i class="fa fa-map-marker"></i>
                    <div class="info-content">
                        <h4>Our Location</h4>
                        <p>Triveni Park Society, 1, Ghatlodia Rd, Near Prabhat Chowk, opp. HDFC Bank, Ghatlodiya ,<br>Ahmedabad, Gujarat 380061</p>
                    </div>
                </div>
                
                <div class="info-item">
                    <i class="fa fa-phone"></i>
                    <div class="info-content">
                        <h4>Call Us</h4>
                        <p>+91  099259 82229<br>Mon - Sat: 9am - 6pm</p>
                    </div>
                </div>

                <div class="info-item">
                    <i class="fa fa-envelope"></i>
                    <div class="info-content">
                        <h4>Email Us</h4>
                        <p>admissions@hcinstitute.com</p>
                    </div>
                </div>

                <!-- Map Placeholder -->
                <div class="map-container">
                    <!-- Generic Map Embed -->
                    <iframe src="https://www.google.com/maps/place/HC's+INSTITUTE+OF+MUSIC+%26+ARTS/data=!4m2!3m1!1s0x0:0xc45d1b87e0f341c6?sa=X&ved=1t:2428&ictx=111" allowfullscreen="" loading="lazy"></iframe>
                </div>
            </div>



            <!-- Right Column: Contact Form -->
            <div class="contact-form">
                <h3 style="color:#653f00; margin-bottom: 20px; font-size: 1.5rem;">Send Message</h3>



                <form action="contact.php" method="POST">
                    <div class="form-group">
                        <label >Full Name</label>
                        <input type="text" name="name" class="form-control" placeholder="Your Name" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="Your Email Id" required>
                    </div>

                       <div class="form-group">
                        <label>Enter Your Mobile Number</label>
                        <input 
                            type="tel" 
                             
                            name="phone"
                            class="form-control" 
                            placeholder="10-digit Mobile Number" 
                            required 
                            maxlength="10" 
                            pattern="\d{10}" 
                            oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');"
                            title="Please enter exactly 10 digits"
                        >
                    </div>
                    <div class="form-group">
                        <label>Subject</label>
                        <select id="subject" name="subject" class="form-control">
                            <option>Course Inquiry</option>
                            <option>Admissions</option>
                            <option>Pricing</option>
                            <option>Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea  name="msg" class="form-control" placeholder="How can we help you?"></textarea>
                    </div>

                    <button type="submit" class="btn-submit" name="send">SEND MESSAGE</button>
                </form>

            </div>
        </div>
    </div>



    <!-- Footer -->
    <footer>
        <div class="footer-container">
            <div class="footer-logo">
                <div class="logof">
                    <img src="image/logo2.png" alt="HC Institute Logo">
                    <p class="logof-title">HC'S INSTITUTE OF MUSIC & ARTS</p>
                </div>
            </div>
            <div class="footer-links">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="index.html">Home</a></li>
                    <li class="dropdown footer-dropdown">
                        <a class="dropbtn">Courses <i class="fa fa-caret-down"></i></a>
                        <div class="dropdown-content">
                            <a href="music_course.html">Music Courses <i class="fa fa-caret-right"></i></a>
                            <a href="art_course.html">Art Courses <i class="fa fa-caret-right"></i></a>
                        </div>
                    </li>
                    <li><a href="javascript:void(0)" onclick="openLogin()">Login</a></li>
                    <li><a href="about.html">About Us</a></li>
                    <li><a href="contact.php">Contact</a></li>
                </ul>
            </div>
            <div class="footer-social">
                <h4>Follow Us</h4>
                <div class="social-icons">
                    <a href="https://www.instagram.com/hcs_institute_of_music_n_arts/" target="_blank" class="insta"><i class="fa-brands fa-instagram"></i></a>
                <a href="https://www.youtube.com/@harshcontractor47" target="_blank" class="yt"><i class="fa-brands fa-youtube"></i></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2026 HC's Institute of Music & Arts. All Rights Reserved.</p>
        </div>
    </footer>

    <div id="loginModal" class="modal-overlay">
    <div class="login-card">
        <span class="close-btn" onclick="closeLogin()">&times;</span>
        
        <div id="loginSection">
            <h2 class="login-title">Welcome Back!</h2>
            <form class="popup-form" action="auth.php" method="POST">
                <div class="input-box">
                    <input type="text" name="username" placeholder="Username" required>
                </div>
                <div class="input-box">
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <p style="text-align: right; margin-top: -10px; margin-bottom: 5px;">
                    <a href="javascript:void(0)" onclick="toggleForm('forgot')" style="font-size: 0.8rem; color: #666;">Forgot Password?</a>
                </p>
                <button type="submit" name="login_user" class="popup-login-btn">LOGIN</button>
            </form>
            <p class="footer-text">Don't have an account? <a href="javascript:void(0)" onclick="toggleForm('signup')">Sign up</a></p>
        </div>



        <div id="signupSection" style="display: none;">
            <h2 class="login-title">Create Account</h2>
            <form class="popup-form" action="auth.php" method="POST">
                <div class="input-box">
                    <input type="text" name="full_name" placeholder="Full Name" required>
                </div>
                <div class="gender-container">
                        <div class="radio-group">
                            <select name="gender" id="genderSelect" class="placeholder-blur" required 
                                    style="width: 100%; border: none; background: transparent; padding: 12px; outline: none; cursor: pointer;">
                                <option value="" disabled selected>Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>

                <div class="input-box">
                        <input type="text" name="birth_date" placeholder="Birth Date" onfocus="(this.type='date')" 
                               onblur="if(!this.value)this.type='text'" class="placeholder-date" required>
                    </div>
                <div class="input-box">
                    <input type="tel" name="phone" placeholder="Phone number" required>
                </div>
                <div class="input-box">
                    <input type="text" name="aadhar_number" id="aadharInput" placeholder="Aadhar Number (12 Digits)" maxlength="12" required>
                </div>
                <div class="input-box">
                    <input type="email" name="email" placeholder="Email Address" required>
                </div>
                <div class="input-box">
                    <input type="text" name="username" placeholder="Username" required>
                </div>
                <div class="input-box">
                    <input type="text" name="address" placeholder="Address" required>
                </div>
                <div class="input-box">
                    <input type="password" name="password" id="signupPass" placeholder="Create Password" required>
                </div>
                <div class="input-box">
                    <input type="password" id="signupConfirm" placeholder="Confirm Password" required>
                </div>
                <div class="terms-container">
                    <input type="checkbox" id="termsCheckbox" required>
                    <label for="termsCheckbox">I agree to the Terms & Conditions</label>
                </div>
                <br>
                <button type="submit" name="reg_user" id="signupBtn" class="popup-login-btn" disabled>SIGN UP</button>
            </form>
            <p class="footer-text">Already have an account? <a href="javascript:void(0)" onclick="toggleForm('login')">Login</a></p>
        </div>
                


        <div id="forgotSection" style="display: none;">
            <h2 class="login-title">Reset Password</h2>
            <form class="popup-form" action="auth.php" method="POST">
                <p style="font-size: 0.8rem; color: #666; margin-bottom: 10px;">Enter details to verify identity</p>
                <div class="input-box">
                    <input type="text" name="username" placeholder="Your Username" required>
                </div>
                <div class="input-box">
                    <input type="text" name="aadhar_verify" id="forgotAadhar" placeholder="12 Digit Aadhar Number" maxlength="12" required>
                </div>
                <div class="input-box">
                    <input type="password" name="new_password" placeholder="Enter New Password" required>
                </div>
                <button type="submit" name="reset_password" class="popup-login-btn">UPDATE PASSWORD</button>
            </form>
            <p class="footer-text"><a href="javascript:void(0)" onclick="toggleForm('login')">Back to Login</a></p>
        </div>
        </div>
    </div>



    <script src="js/login.js"></script> 
</body>
</html>