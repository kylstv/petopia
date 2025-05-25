<?php
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Database connection
    $conn = new mysqli("localhost", "root", "", "kitter");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if token exists
    $sql = "SELECT * FROM signup WHERE verification_token = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        // Update status to 'verified'
        $updateSql = "UPDATE signup SET status = 'verified', verification_token = NULL WHERE verification_token = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("s", $token);
        $updateStmt->execute();

        echo "Your account has been verified successfully! <a href='login.php'>Log in</a>";
    } else {
        echo "Invalid or expired verification link.";
    }

    $stmt->close();
    $conn->close();
}
?>
