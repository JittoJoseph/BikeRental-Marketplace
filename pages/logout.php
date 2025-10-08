<?php
require_once __DIR__ . '/../config.php';

// Destroy all session data
session_destroy();

// Redirect to home page
header('Location: ' . BASE_URL . '/index.php');
exit();
?>
