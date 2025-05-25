<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$error = "";
$email = "";
$password = "";

// Check for auto-login with cookies
if (!isset($_SESSION['user']) && isset($_COOKIE['remembered_email']) && isset($_COOKIE['remembered_token'])) {
    $email = $_COOKIE['remembered_email'];
    $token = $_COOKIE['remembered_token'];
    
    $conn = new mysqli("localhost", "root", "", "kitter");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Check admin table
    $sqlAdmin = "SELECT * FROM admin WHERE email = ?";
    $stmtAdmin = $conn->prepare($sqlAdmin);
    $stmtAdmin->bind_param("s", $email);
    $stmtAdmin->execute();
    $resultAdmin = $stmtAdmin->get_result();
    
    if ($resultAdmin->num_rows == 1) {
        $user = $resultAdmin->fetch_assoc();
        if ($token === md5($user['password'] . 'salt_string')) {
            $_SESSION['user'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = 'admin';
            header("Location: admin.html");
            exit();
        }
    } else {
        $sqlCustomer = "SELECT * FROM signup WHERE email = ? AND status = 'verified'";
        $stmtCustomer = $conn->prepare($sqlCustomer);
        $stmtCustomer->bind_param("s", $email);
        $stmtCustomer->execute();
        $resultCustomer = $stmtCustomer->get_result();
        
        if ($resultCustomer->num_rows == 1) {
            $user = $resultCustomer->fetch_assoc();
            // Verify the token
            if ($token === md5($user['password'] . 'salt_string') && $user['status'] !== 'blocked') {
                $_SESSION['user'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = 'customer';
                header("Location: product.php");
                exit();
            }
        }
    }
    
    // If we get here, the cookie is invalid or expired, so clear them
    setcookie('remembered_email', '', time() - 3600, '/');
    setcookie('remembered_token', '', time() - 3600, '/');
    
    if ($stmtAdmin) $stmtAdmin->close();
    if (isset($stmtCustomer)) $stmtCustomer->close();
    $conn->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);

    include "connect.php";

    $sqlAdmin = "SELECT * FROM admin WHERE email = ?";
    $stmtAdmin = $conn->prepare($sqlAdmin);
    $stmtAdmin->bind_param("s", $email);
    $stmtAdmin->execute();
    $resultAdmin = $stmtAdmin->get_result();

    if ($resultAdmin->num_rows == 1) {
        $user = $resultAdmin->fetch_assoc();
        if ($password === $user['password']) {
            $_SESSION['user'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = 'admin';
            
            // If remember me is checked, set cookies
            if ($remember) {
                $token = md5($user['password'] . 'salt_string');
                // Set cookies to expire in 30 days
                setcookie('remembered_email', $email, time() + (86400 * 30), '/');
                setcookie('remembered_token', $token, time() + (86400 * 30), '/');
            }

            header("Location: admin.html");
            exit();
        } else {
            $error = "Incorrect password for admin.";
        }
    } else {
        $sqlCustomer = "SELECT * FROM signup WHERE email = ? AND status = 'verified'";
        $stmtCustomer = $conn->prepare($sqlCustomer);
        $stmtCustomer->bind_param("s", $email);
        $stmtCustomer->execute();
        $resultCustomer = $stmtCustomer->get_result();

        if ($resultCustomer->num_rows == 1) {
            $user = $resultCustomer->fetch_assoc();

            if ($user['status'] === 'blocked') {
                $error = "Your account has been blocked.";
            } elseif ($password === $user['password']) {
                $_SESSION['user'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = 'customer';
                
                // If remember me is checked, set cookies
                if ($remember) {
                    $token = md5($user['password'] . 'salt_string');
                    // Set cookies to expire in 30 days
                    setcookie('remembered_email', $email, time() + (86400 * 30), '/');
                    setcookie('remembered_token', $token, time() + (86400 * 30), '/');
                }

                header("Location: product.html");
                exit();
            } else {
                $error = "Incorrect password for customer.";
            }
        } else {
            $error = "No account found with that email.";
        }
    }

    if ($stmtAdmin) $stmtAdmin->close();
    if (isset($stmtCustomer)) $stmtCustomer->close();
    $conn->close();
}

// Maintain backward compatibility - use session for email suggestions
$savedAccounts = [];
if (isset($_SESSION['remembered_accounts'])) {
    $savedAccounts = $_SESSION['remembered_accounts'];
}

// Add cookie-based remembered email to the suggestions if it exists
if (isset($_COOKIE['remembered_email']) && !empty($_COOKIE['remembered_email'])) {
    $emailFound = false;
    foreach ($savedAccounts as $acc) {
        if ($acc['email'] === $_COOKIE['remembered_email']) {
            $emailFound = true;
            break;
        }
    }
    
    if (!$emailFound) {
        $savedAccounts[] = [
            'email' => $_COOKIE['remembered_email'],
            'password' => ''
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Stargazer</title>
    <!-- favicon -->
    <link rel="shortcut icon" href="./favicon.svg" type="image/svg+xml">
    <!-- custom css link -->
    <link rel="stylesheet" href="./assets/css/style.css">
    <!-- google font link -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bangers&family=Carter+One&family=Nunito+Sans:wght@400;700&display=swap" rel="stylesheet">
    <!-- preload images -->
    <link rel="preload" as="image" href="./assets/images/hero-banner.jpg">
</head>
<body>
    <!-- 
      - HEADER
    -->
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

    <!-- 
      - LOGIN SECTION
    -->
    <section class="section">
        <div class="container">
            <div style="max-width: 500px; margin: 0 auto; padding: 20px; border-radius: var(--radius-10); box-shadow: var(--shadow-1); background-color: var(--white);">
                <h2 class="h2" style="text-align: center; margin-bottom: 20px;">Sign in</h2>
                
                <?php if (!empty($error)) { echo "<p style='background-color: var(--bittersweet); color: var(--white); padding: 10px; border-radius: var(--radius-4); margin-bottom: 20px;'>$error</p>"; } ?>

                <form action="login.php" method="post" autocomplete="on" style="display: flex; flex-direction: column; gap: 15px;">
                    <input type="email" id="email" name="email" placeholder="Email" list="email_suggestions" required 
                           value="<?= isset($_COOKIE['remembered_email']) ? htmlspecialchars($_COOKIE['remembered_email']) : htmlspecialchars($email) ?>"
                           style="padding: 12px 15px; border: 1px solid var(--platinum); border-radius: var(--radius-4);">
                           
                    <datalist id="email_suggestions">
                        <?php foreach ($savedAccounts as $acc) {
                            echo "<option value='" . htmlspecialchars($acc['email']) . "'></option>";
                        } ?>
                    </datalist>
                    
                    <input type="password" id="password" name="password" placeholder="Password" required 
                           value="<?= htmlspecialchars($password) ?>"
                           style="padding: 12px 15px; border: 1px solid var(--platinum); border-radius: var(--radius-4);">
                           
                    <div style="display: flex; align-items: center; gap: 5px;">
                        <input type="checkbox" id="remember" name="remember" <?= isset($_COOKIE['remembered_email']) ? 'checked' : '' ?>>
                        <label for="remember">Remember me</label>
                    </div>
                    
                    <button type="submit" class="btn" style="margin-top: 10px; background-color: var(--portland-orange);">Login</button>
                </form>

                <div style="margin-top: 20px; text-align: center;">
                    <a href="forgot_password.php" style="color: var(--portland-orange);">Forgot Password?</a>
                    <div style="margin-top: 15px;">
                        <p>Don't have an account? <a href="signup.php" style="color: var(--portland-orange);">Sign Up</a></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 
      - FOOTER
    -->
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

    <!-- 
      - BACK TO TOP
    -->
    <a href="#top" class="back-top-btn" aria-label="back to top" data-back-top-btn>
        <ion-icon name="arrow-up"></ion-icon>
    </a>

    <!-- 
      - custom js link
    -->
    <script>
        const savedAccounts = <?= json_encode($savedAccounts); ?>;

        document.addEventListener("DOMContentLoaded", function () {
            const emailInput = document.getElementById("email");
            const passwordInput = document.getElementById("password");

            emailInput.addEventListener("change", function () {
                const selectedEmail = emailInput.value;
                const account = savedAccounts.find(acc => acc.email === selectedEmail);
                if (account && account.password) {
                    passwordInput.value = account.password;
                } else {
                    passwordInput.value = "";
                }
            });
            
            // Header scroll effect
            const header = document.querySelector("[data-header]");
            const backTopBtn = document.querySelector("[data-back-top-btn]");
            
            window.addEventListener("scroll", function () {
                if (window.scrollY >= 100) {
                    header.classList.add("active");
                    backTopBtn.classList.add("active");
                } else {
                    header.classList.remove("active");
                    backTopBtn.classList.remove("active");
                }
            });
            
            // Mobile menu toggle
            const navToggler = document.querySelector("[data-nav-toggler]");
            const navbar = document.querySelector("[data-navbar]");
            
            navToggler.addEventListener("click", function () {
                navbar.classList.toggle("active");
                this.classList.toggle("active");
            });
        });
    </script>

    <!-- 
      - ionicon link
    -->
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>
</html>