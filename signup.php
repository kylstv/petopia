<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/PHPMailer/PHPMailer/src/Exception.php';
require 'PHPMailer/PHPMailer/PHPMailer/src/PHPMailer.php';
require 'PHPMailer/PHPMailer/PHPMailer/src/SMTP.php';

$firstName = $middleName = $lastName = $email = $username = $password = $confirmPassword = "";
$error = $success = "";
$showCaptcha = false;
$userData = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check which form step we're processing
    if (isset($_POST['initial_signup'])) {
        // Processing the initial signup form
        $firstName = $_POST['first-name'];
        $middleName = $_POST['middle-name'];
        $lastName = $_POST['last-name'];
        $email = $_POST['email'];
        $username = $_POST['username'];
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirm-password'];

        if ($password !== $confirmPassword) {
            $error = "Passwords do not match!";
        } else {
            $conn = new mysqli("localhost", "root", "", "kitter");

            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            $checkEmailSql = "SELECT * FROM signup WHERE email = ?";
            $checkStmt = $conn->prepare($checkEmailSql);
            $checkStmt->bind_param("s", $email);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();

            if ($checkResult->num_rows > 0) {
                $error = "Email already exists!";
            } else {
                // Store user data in session for the next step
                session_start();
                $_SESSION['user_data'] = [
                    'firstName' => $firstName,
                    'middleName' => $middleName,
                    'lastName' => $lastName,
                    'email' => $email,
                    'username' => $username,
                    'password' => $password
                ];
                
                // Show the captcha form
                $showCaptcha = true;
                $userData = $_SESSION['user_data'];
            }
            $checkStmt->close();
            $conn->close();
        }
    } elseif (isset($_POST['captcha_verification'])) {
        // Processing the captcha verification before sending OTP
        session_start();
        if (!isset($_SESSION['user_data'])) {
            $error = "Session expired. Please try again.";
        } else {
            $userData = $_SESSION['user_data'];
            
            // Verify reCAPTCHA
            $recaptchaResponse = $_POST['g-recaptcha-response'];
            // Updated with your actual reCAPTCHA secret key
            $secretKey = '6LcMHRArAAAAAA6OI4aAjkfrWBsweWyakzuAq_Za';
            
            // Make request to Google to verify the token
            $url = 'https://www.google.com/recaptcha/api/siteverify';
            $data = [
                'secret' => $secretKey,
                'response' => $recaptchaResponse
            ];
            
            $options = [
                'http' => [
                    'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method' => 'POST',
                    'content' => http_build_query($data)
                ]
            ];
            
            $context = stream_context_create($options);
            $verify = file_get_contents($url, false, $context);
            $captchaSuccess = json_decode($verify);
            
            if (!$recaptchaResponse) {
                $error = "Please complete the reCAPTCHA.";
                $showCaptcha = true;
            } elseif (!$captchaSuccess->success) {
                $error = "reCAPTCHA verification failed. Please try again.";
                $showCaptcha = true;
            } else {
                // Captcha is valid, proceed with account creation and OTP sending
                $conn = new mysqli("localhost", "root", "", "kitter");
                
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }
                
                // Generate a random 6-digit OTP
                $otp = rand(100000, 999999);
                
                $sql = "INSERT INTO signup (first_name, middle_name, last_name, email, username, password, status, otp) 
                        VALUES (?, ?, ?, ?, ?, ?, 'unverified', ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssssss", 
                    $userData['firstName'], 
                    $userData['middleName'], 
                    $userData['lastName'], 
                    $userData['email'], 
                    $userData['username'], 
                    $userData['password'], 
                    $otp
                );

                if ($stmt->execute()) {
                    $mail = new PHPMailer(true);
                    try {
                        $mail->isSMTP();
                        $mail->Host = 'smtp.gmail.com';
                        $mail->SMTPAuth = true;
                        $mail->Username = 'kyleoriana@gmail.com';
                        $mail->Password = 'uhkl mkdo gfjq yevs';
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port = 587;

                        $mail->setFrom('kyleoriana@gmail.com', 'Stargazer');
                        $mail->addAddress($userData['email']);

                        $mail->isHTML(true);
                        $mail->Subject = 'Your OTP Verification Code';
                        $mail->Body = "Your OTP is: <b>$otp</b>. Please enter this code to verify your account.";
                        $mail->send();
                        
                        // Clear the session data
                        unset($_SESSION['user_data']);
                        
                        // Redirect to OTP verification page
                        header("Location: otpverify.php?email=" . urlencode($userData['email']));
                        exit;
                    } catch (Exception $e) {
                        $error = "Verification email could not be sent. Mailer Error: {$mail->ErrorInfo}";
                        $showCaptcha = true;
                    }
                } else {
                    $error = "Error: " . $stmt->error;
                    $showCaptcha = true;
                }
                $stmt->close();
                $conn->close();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create an Account | Kitter</title>
    <link rel="stylesheet" href="./assets/css/style.css">
    
    <!-- Ionicons -->
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bangers&family=Carter+One&family=Nunito+Sans:wght@400;700&display=swap" rel="stylesheet">

    <?php if ($showCaptcha): ?>
    <!-- Include reCAPTCHA v2 API -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <?php endif; ?>
    <style>
        .signup {
    max-width: 500px;
    margin: 3rem auto;
    background-color: var(--white);
    padding: 3rem;
    border-radius: var(--radius-10);
    box-shadow: var(--shadow-1);
}

.signup-form {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.signup-form input {
    padding: 12px 15px;
    border: 1px solid var(--light-gray);
    border-radius: var(--radius-4);
    font-family: var(--ff-nunito_sans);
    transition: var(--transition-1);
}

.signup-form input:focus {
    border-color: var(--portland-orange);
    outline: none;
}

.signup-form .btn {
    background-color: var(--portland-orange);
    color: var(--white);
    font-weight: var(--fw-700);
    padding: 12px;
    border-radius: 50px;
    margin-top: 10px;
}

.signup-form .btn:hover {
    background-color: var(--black);
}

.signin-link {
    text-align: center;
    margin-top: 2rem;
    color: var(--sonic-silver);
}

.signin-link a {
    color: var(--portland-orange);
    font-weight: var(--fw-700);
    transition: var(--transition-1);
}

.signin-link a:hover {
    color: var(--black);
}

.error-message {
    color: var(--bittersweet);
    margin-bottom: 15px;
    text-align: center;
}

.user-info {
    background-color: var(--platinum);
    padding: 15px;
    border-radius: var(--radius-4);
    margin-bottom: 20px;
    border-left: 4px solid var(--portland-orange);
}

.user-info p {
    margin: 5px 0;
}

.user-info .label {
    font-weight: var(--fw-700);
    display: inline-block;
    width: 100px;
}

.g-recaptcha {
    margin: 15px 0;
    display: flex;
    justify-content: center;
}

.captcha-info {
    text-align: center;
    margin-bottom: 15px;
    color: var(--sonic-silver);
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

<section class="section">
    <div class="container">
        <div class="signup">
            <h2 class="h2">Create an Account</h2>
            <?php if (!empty($error)) echo "<p class='error-message'>$error</p>"; ?>
            <?php if (!empty($success)) echo "<p style='color: var(--portland-orange);'>$success</p>"; ?>

            <?php if ($showCaptcha): ?>
                <!-- Show captcha verification form -->
                <div class="user-info">
                    <p><span class="label">Name:</span> <?php echo htmlspecialchars($userData['firstName'] . ' ' . $userData['lastName']); ?></p>
                    <p><span class="label">Email:</span> <?php echo htmlspecialchars($userData['email']); ?></p>
                    <p><span class="label">Username:</span> <?php echo htmlspecialchars($userData['username']); ?></p>
                </div>
                
                <p class="captcha-info">Please complete the captcha verification below to receive your OTP verification code.</p>
                
                <form action="signup.php" method="post" class="signup-form">
                    <!-- Updated reCAPTCHA widget with the correct site key -->
                    <div class="g-recaptcha" data-sitekey="6LcMHRArAAAAANW6I3w7EbklU0ubbB_kdK4jH5-D"></div>
                    
                    <input type="hidden" name="captcha_verification" value="1">
                    <button type="submit" class="btn">Verify & Send OTP</button>
                </form>
            <?php else: ?>
                <!-- Show initial signup form -->
                <form action="signup.php" method="post" class="signup-form">
                    <input type="text" id="first-name" name="first-name" placeholder="Enter your first name" required>
                    <input type="text" id="middle-name" name="middle-name" placeholder="Enter your middle name (optional)">
                    <input type="text" id="last-name" name="last-name" placeholder="Enter your last name" required>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                    <input type="text" id="username" name="username" placeholder="Choose a username" required>
                    <input type="password" id="password" name="password" placeholder="Create a password" required>
                    <input type="password" id="confirm-password" name="confirm-password" placeholder="Confirm your password" required>
                    
                    <input type="hidden" name="initial_signup" value="1">
                    <button type="submit" class="btn">Continue</button>
                </form>
            <?php endif; ?>

            <div class="signin-link">
                <p>Already have an account? <a href="login.php">Sign in</a></p>
            </div>
        </div>
    </div>
</section>
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

<!-- JavaScript -->
<script src="script.js"></script>

<!-- Ionicon -->
<script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>

</body>
</html>