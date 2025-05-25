<?php
// verify_admin_password.php
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$inputPassword = $data['password'] ?? '';

$host = 'localhost';
$dbname = 'kitter';
$dbUser = 'root'; // Change if needed
$dbPass = ''; // Change if needed

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Fetch admin password hash
    $stmt = $pdo->prepare("SELECT password FROM admin LIMIT 1");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($admin && password_verify($inputPassword, $admin['password'])) {
        // Also get current customer status if needed
        $customerId = isset($_POST['customer_id']) ? $_POST['customer_id'] : null;
        if ($customerId !== null) {
            $stmt2 = $pdo->prepare("SELECT status FROM signup WHERE id = :id");
            $stmt2->bindParam(':id', $customerId);
            $stmt2->execute();
            $result = $stmt2->fetch(PDO::FETCH_ASSOC);
            $status = $result['status'] ?? 'active';
            echo json_encode(['success' => true, 'currentStatus' => $status]);
        } else {
            echo json_encode(['success' => true, 'currentStatus' => 'active']);
        }
    } else {
        echo json_encode(['success' => false]);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false]);
}
?>