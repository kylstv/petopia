<?php
session_start();

// Check if user is authorized to access this page
if (!isset($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true || !isset($_SESSION['reset_email'])) {
    header("Location: forgot_password.php");
    exit();
}

$email = $_SESSION['reset_email'];
$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate password
    if (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        
      include "connect.php";
        
        // Update the password directly without hashing and clear the OTP
        $sql = "UPDATE signup SET password = ?, otp = NULL WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $password, $email);
        
        if ($stmt->execute()) {
            $success = "Password has been reset successfully!";
            // Clear session variables
            unset($_SESSION['otp_verified']);
            unset($_SESSION['reset_email']);
            
            // After 3 seconds, redirect to login page
            header("refresh:3;url=login.php");
        } else {
            $error = "Failed to reset password. Please try again.";
        }
        
        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | Kitter</title>
    <link rel="stylesheet" href="./assets/css/style.css">
    
    <!-- Ionicons -->
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bangers&family=Carter+One&family=Nunito+Sans:wght@400;700&display=swap" rel="stylesheet">

    <style>
        .reset-container {
            max-width: 500px;
            margin: 60px auto;
            background-color: var(--white);
            padding: 30px;
            border-radius: var(--radius-10);
            box-shadow: var(--shadow-1);
        }

        .reset-container .title {
            font-family: var(--ff-carter_one);
            font-size: var(--fs-3);
            text-align: center;
            color: var(--eerie-black);
            margin-bottom: 30px;
        }

        .reset-form {
            display: flex;
            flex-direction: column;
        }

        .email-display {
            background-color: var(--platinum);
            padding: 15px;
            border-radius: var(--radius-4);
            margin-bottom: 20px;
            font-family: var(--ff-nunito_sans);
        }

        .email-label {
            font-weight: var(--fw-400);
            color: var(--sonic-silver);
            margin-bottom: 5px;
        }

        .email-value {
            font-weight: var(--fw-700);
            color: var(--eerie-black);
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

        .password-requirements {
            font-family: var(--ff-nunito_sans);
            font-size: var(--fs-7);
            color: var(--sonic-silver);
            margin-bottom: 15px;
            padding: 10px;
            background-color: var(--cultured);
            border-radius: var(--radius-4);
        }

        /* Responsive */
        @media (max-width: 575px) {
            .reset-container {
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
        <div class="reset-container">
            <h2 class="title">Reset Password</h2>
            
            <?php if (!empty($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form class="reset-form" method="post">
                <div class="email-display">
                    <div class="email-label">Creating new password for:</div>
                    <div class="email-value"><?php echo htmlspecialchars($email); ?></div>
                </div>
                
                <div class="password-requirements">
                    Password must be at least 8 characters long.
                </div>
                
                <label for="password" class="form-label">New Password</label>
                <input type="password" name="password" id="password" class="form-input" required>
                
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <input type="password" name="confirm_password" id="confirm_password" class="form-input" required>
                
                <button type="submit" class="form-button">Reset Password</button>
            </form>
        </div>
    </main>

    <!-- FOOTER -->
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
