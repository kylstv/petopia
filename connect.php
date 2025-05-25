<?php

$host = "localhost";
$user = "root";
$pass = "";
$db = "kitter";  // Assigning the database name to the variable
$conn = new mysqli($host, $user, $pass, $db);  // Use $db to specify the database

if ($conn->connect_error) {
    echo "Failed to connect DB: " . $conn->connect_error;
}

?>
