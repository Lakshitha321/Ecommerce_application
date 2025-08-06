<?php
require_once __DIR__ . '/../includes/functions.php';

// Destroy all session data
session_destroy();

// Redirect to home page
header('Location: index.php');
exit();
?>