<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/PHPMailer/PHPMailer/src/Exception.php';
require 'PHPMailer/PHPMailer/PHPMailer/src/PHPMailer.php';
require 'PHPMailer/PHPMailer/PHPMailer/src/SMTP.php';

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    include "connect.php";

    // Check if email exists
    $sql = "SELECT * FROM signup WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        // Generate OTP
        $otp = mt_rand(100000, 999999);

        // Save OTP to database
        $updateSql = "UPDATE signup SET otp = ? WHERE email = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("ss", $otp, $email);
        
        if ($updateStmt->execute()) {
            // Store email in session for the verification page
            $_SESSION['reset_email'] = $email;
            
            // Send email with OTP
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'kyleoriana@gmail.com';
                $mail->Password = 'uhkl mkdo gfjq yevs'; // App password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('kyleoriana@gmail.com', 'Kitter');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = 'Password Reset OTP';
                $mail->Body = "Here is your OTP for password reset: <b>$otp</b>";
                $mail->send();

                $success = "OTP has been sent to your email.";
                header("Location: forgotVerify.php");
                exit();
            } catch (Exception $e) {
                $error = "Failed to send OTP. Error: {$mail->ErrorInfo}";
            }
        } else {
            $error = "Failed to update OTP in database.";
        }
        $updateStmt->close();
    } else {
        $error = "No account found with this email.";
    }
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | Kitter</title>
    
  <!-- 
    - favicon
  -->
  <link rel="shortcut icon" href="./favicon.svg" type="image/svg+xml">

<!-- 
  - custom css link
-->
<link rel="stylesheet" href="./assets/css/style.css">

<!-- 
  - google font link
-->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link
  href="https://fonts.googleapis.com/css2?family=Bangers&family=Carter+One&family=Nunito+Sans:wght@400;700&display=swap"
  rel="stylesheet">

<!-- 
  - preload images
-->
<link rel="preload" as="image" href="./assets/images/hero-banner.jpg">
    <style>
        .forgot-password-container {
            max-width: 500px;
            margin: 60px auto;
            background-color: var(--white);
            padding: 30px;
            border-radius: var(--radius-10);
            box-shadow: var(--shadow-1);
        }

        .forgot-password-container .title {
            font-family: var(--ff-carter_one);
            font-size: var(--fs-3);
            text-align: center;
            color: var(--eerie-black);
            margin-bottom: 30px;
        }

        .forgot-password-form {
            display: flex;
            flex-direction: column;
        }

        .form-label {
            font-family: var(--ff-nunito_sans);
            font-weight: var(--fw-700);
            color: var(--eerie-black);
            margin-bottom: 8px;
        }

        .form-input {
            padding: 12px 15px;
            margin-bottom: 20px;
            border: 1px solid var(--platinum);
            border-radius: var(--radius-4);
            font-family: var(--ff-nunito_sans);
            font-size: var(--fs-6);
            transition: border-color var(--transition-1);
        }

        .form-input:focus {
            border-color: var(--portland-orange);
            outline: none;
        }

        .form-button {
            background-color: var(--portland-orange);
            color: var(--white);
            padding: 12px 15px;
            border: none;
            border-radius: var(--radius-4);
            font-family: var(--ff-nunito_sans);
            font-weight: var(--fw-700);
            font-size: var(--fs-6);
            cursor: pointer;
            transition: background-color var(--transition-1);
        }

        .form-button:hover {
            background-color: var(--eerie-black);
        }

        .error, .success {
            font-family: var(--ff-nunito_sans);
            text-align: center;
            margin-bottom: 20px;
            padding: 10px;
            border-radius: var(--radius-4);
        }

        .error {
            color: #721c24;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
        }

        .success {
            color: #155724;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
        }

        .back-to-login {
            text-align: center;
            margin-top: 20px;
        }

        .back-to-login a {
            color: var(--portland-orange);
            font-weight: var(--fw-700);
            transition: color var(--transition-1);
        }

        .back-to-login a:hover {
            color: var(--eerie-black);
        }

        /* Responsive */
        @media (max-width: 575px) {
            .forgot-password-container {
                margin: 30px 15px;
                padding: 20px;
            }
        }
    </style>
</head>
<body>

<header class="header" data-header>
    <div class="container">

      <button class="nav-toggle-btn" aria-label="toggle manu" data-nav-toggler>
        <ion-icon name="menu-outline" aria-hidden="true" class="menu-icon"></ion-icon>
        <ion-icon name="close-outline" aria-label="true" class="close-icon"></ion-icon>
      </button>

      <a href="#" class="logo">Petopia</a>

      <nav class="navbar" data-navbar>
        <ul class="navbar-list">

          <li class="navbar-item">
            <a href="index.html" class="navbar-link" data-nav-link>Home</a>
          </li>

          <li class="navbar-item">
            <a href="shop.html" class="navbar-link" data-nav-link>Shop</a>
          </li>

          <li class="navbar-item">
            <a href="#" class="navbar-link" data-nav-link>Collections</a>
          </li>

          <li class="navbar-item">
            <a href="#" class="navbar-link" data-nav-link>Blogs</a>
          </li>

          <li class="navbar-item">
            <a href="#" class="navbar-link" data-nav-link>Contact</a>
          </li>

        </ul>

        <a href="#" class="navbar-action-btn">Log In</a>
      </nav>

  </header>

    <!-- MAIN CONTENT -->
    <main>
        <div class="forgot-password-container">
            <h2 class="title">Forgot Password</h2>
            
            <?php if (!empty($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST" class="forgot-password-form">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" name="email" id="email" class="form-input" required placeholder="Enter your email">
                
                <button type="submit" class="form-button">Send OTP</button>
            </form>
            
            <div class="back-to-login">
                <a href="login.php">Back to Login</a>
            </div>
        </div>
    </main>
    <footer class="footer" style="background-image: url('./assets/images/footer-bg.jpg')">

<div class="footer-top section">
  <div class="container">

    <div class="footer-brand">

      <a href="#" class="logo">Kitter</a>

      <p class="footer-text">
        If you have any question, please contact us at <a href="mailto:jnamillvillareal@gmail.com"
          class="link">jnamillvillareal@gmail.com</a>
      </p>

      <ul class="contact-list">

        <li class="contact-item">
          <ion-icon name="location-outline" aria-hidden="true"></ion-icon>

          <address class="address">
            Bustos, Bulacan
          </address>
        </li>

        <li class="contact-item">
          <ion-icon name="call-outline" aria-hidden="true"></ion-icon>

          <a href="09942516738" class="contact-link">09942516738</a>
        </li>

      </ul>

      <ul class="social-list">

        <li>
          <a href="#" class="social-link">
            <ion-icon name="logo-facebook"></ion-icon>
          </a>
        </li>

        <li>
          <a href="#" class="social-link">
            <ion-icon name="logo-twitter"></ion-icon>
          </a>
        </li>

        <li>
          <a href="#" class="social-link">
            <ion-icon name="logo-pinterest"></ion-icon>
          </a>
        </li>

        <li>
          <a href="#" class="social-link">
            <ion-icon name="logo-instagram"></ion-icon>
          </a>
        </li>

      </ul>

    </div>

    <ul class="footer-list">

      <li>
        <p class="footer-list-title">Corporate</p>
      </li>

      <li>
        <a href="#" class="footer-link">Careers</a>
      </li>

      <li>
        <a href="#" class="footer-link">About Us</a>
      </li>

      <li>
        <a href="#" class="footer-link">Contact Us</a>
      </li>

      <li>
        <a href="#" class="footer-link">FAQs</a>
      </li>

      <li>
        <a href="#" class="footer-link">Vendors</a>
      </li>

      <li>
        <a href="#" class="footer-link">Affiliate Program</a>
      </li>

    </ul>

    <ul class="footer-list">

      <li>
        <p class="footer-list-title">Information</p>
      </li>

      <li>
        <a href="#" class="footer-link">Online Store</a>
      </li>

      <li>
        <a href="#" class="footer-link">Privacy Policy</a>
      </li>

      <li>
        <a href="#" class="footer-link">Refund Policy</a>
      </li>

      <li>
        <a href="#" class="footer-link">Shipping Policy</a>
      </li>

      <li>
        <a href="#" class="footer-link">Terms of Service</a>
      </li>

      <li>
        <a href="#" class="footer-link">Track Order</a>
      </li>

    </ul>

    <ul class="footer-list">

      <li>
        <p class="footer-list-title">Services</p>
      </li>

      <li>
        <a href="#" class="footer-link">Grooming</a>
      </li>

      <li>
        <a href="#" class="footer-link">Positive Dog Training</a>
      </li>

      <li>
        <a href="#" class="footer-link">Veterinary Services</a>
      </li>

      <li>
        <a href="#" class="footer-link">Petco Insurance</a>
      </li>

      <li>
        <a href="#" class="footer-link">Pet Adoption</a>
      </li>

      <li>
        <a href="#" class="footer-link">Resource Center</a>
      </li>

    </ul>

  </div>
</div>

<div class="footer-bottom">
  <div class="container">

    <p class="copyright">
      &copy; 2025 Made with ♥ by <a href="#" class="copyright-link">Web2.</a>
    </p>

    <img src="./assets/images/payment.png" width="397" height="32" loading="lazy" alt="payment method" class="img">

  </div>
</div>

</footer>

    <!-- BACK TO TOP -->
    <a href="#top" class="back-top-btn" aria-label="back to top" data-back-top-btn>
        <ion-icon name="chevron-up" aria-hidden="true"></ion-icon>
    </a>

    <!-- Custom Script -->
    <script>
        // Mobile nav toggle
        const navToggler = document.querySelector("[data-nav-toggler]");
        const navbar = document.querySelector("[data-navbar]");
        const header = document.querySelector("[data-header]");

        const toggleNavbar = function () {
            navbar.classList.toggle("active");
            navToggler.classList.toggle("active");
        }

        navToggler.addEventListener("click", toggleNavbar);

        // Header active on scroll
        window.addEventListener("scroll", function () {
            if (window.scrollY > 80) {
                header.classList.add("active");
            } else {
                header.classList.remove("active");
            }
        });

        // Back to top button
        const backTopBtn = document.querySelector("[data-back-top-btn]");

        window.addEventListener("scroll", function () {
            if (window.scrollY >= 100) {
                backTopBtn.classList.add("active");
            } else {
                backTopBtn.classList.remove("active");
            }
        });
    </script>
</body>
</html>