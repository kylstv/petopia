<?php
//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'C:\xampp\htdocs\kitter\PHPMailer\PHPMailer\PHPMailer\src\Exception.php';
require 'C:\xampp\htdocs\kitter\PHPMailer\PHPMailer\PHPMailer\src\PHPMailer.php';
require 'C:\xampp\htdocs\kitter\PHPMailer\PHPMailer\PHPMailer\src\SMTP.php';

//Create an instance; passing true enables exceptions
$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->SMTPDebug = 0;                     // Enable verbose debug output
    $mail->isSMTP();                                            // Send using SMTP
    $mail->Host       = 'smtp.gmail.com';                         // Set the SMTP server to send through
    $mail->SMTPAuth   = true;                                    // Enable SMTP authentication
    $mail->Username   = 'kyleoriana@gmail.com';                // SMTP username (your Gmail address)
    $mail->Password   = 'uhkl mkdo gfjq yevs';                   // SMTP password (use App Password if 2FA is enabled)
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;          // Enable implicit TLS encryption
    $mail->Port       = 587;                                     // TCP port to connect to (587 for TLS)

    // Recipients
    $mail->setFrom('kyleoriana@gmail.com', 'Petopia');
    $mail->addAddress('kyleoriana@gmail.com', 'Steven Merenciano'); // Add a recipient

   // Generate a random 6-digit code
    $code = '';
        for ($i = 0; $i < 6; $i++) {
    $code .= rand(0, 9); // Random digit (0-9)
}

    // Content
    $mail->isHTML(true);                                         // Set email format to HTML
    $mail->Subject = 'OTP';
    $mail->Body    = "Thank you for Registering to our website. Please use this code to proceed to our website: <b>$code</b>";
    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

    // Send the message
    $mail->send();
    echo 'Message has been sent';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>
