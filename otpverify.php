<?php

$email = $_GET['email'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $otp = $_POST['otp'];

    include "connect.php";

    $sql = "SELECT * FROM signup WHERE email = ? AND otp = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $otp);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $updateSql = "UPDATE signup SET status = 'verified', otp = NULL WHERE email = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("s", $email);
        $updateStmt->execute();
        $message = "Your account has been verified successfully! <a href='login.php'>Log in</a>";
    } else {
        $error = "Invalid OTP.";
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
    <title>Account Verification | Kitter</title>
    <link rel="stylesheet" href="./assets/css/style.css">
    
    <!-- Ionicons -->
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bangers&family=Carter+One&family=Nunito+Sans:wght@400;700&display=swap" rel="stylesheet">

    <style>
        .otp-container {
            max-width: 500px;
            margin: 60px auto;
            background-color: var(--white);
            padding: 30px;
            border-radius: var(--radius-10);
            box-shadow: var(--shadow-1);
        }

        .otp-container .title {
            font-family: var(--ff-carter_one);
            font-size: var(--fs-3);
            text-align: center;
            color: var(--eerie-black);
            margin-bottom: 30px;
        }

        .otp-form {
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
            text-align: center;
            letter-spacing: 5px;
            font-weight: var(--fw-700);
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

        .error, .message {
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

        .message {
            color: #155724;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
        }

        .message a {
            color: var(--portland-orange);
            font-weight: var(--fw-700);
            text-decoration: none;
        }

        .message a:hover {
            text-decoration: underline;
        }

        /* Responsive */
        @media (max-width: 575px) {
            .otp-container {
                margin: 30px 15px;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- HEADER -->
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

    </div>
  </header>

    <!-- MAIN CONTENT -->
    <main>
        <div class="otp-container">
            <h2 class="title">Verify Your Account</h2>
            
            <?php if (isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if (isset($message)): ?>
                <div class="message"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if (!isset($message)): ?>
                <form class="otp-form" action="otpverify.php?email=<?php echo urlencode($email); ?>" method="post">
                    <div class="email-display">
                        <div class="email-label">We've sent a verification code to:</div>
                        <div class="email-value"><?php echo htmlspecialchars($email); ?></div>
                    </div>
                    
                    <label for="otp" class="form-label">Enter Verification Code</label>
                    <input type="text" name="otp" id="otp" class="form-input" placeholder="Enter OTP" maxlength="6" required>
                    
                    <button type="submit" class="form-button">Verify</button>
                </form>
            <?php endif; ?>
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