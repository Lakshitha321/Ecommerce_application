<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ecommerce_app";

// Create connection without selecting database first
function getConnection($selectDB = true) {
    global $servername, $username, $password, $dbname;
    
    if ($selectDB) {
        $conn = new mysqli($servername, $username, $password, $dbname);
    } else {
        $conn = new mysqli($servername, $username, $password);
    }
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}
?>