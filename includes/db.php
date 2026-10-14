<?php
/**
 * Smart Store Credit and Customer Dues Management System
 * Database Configuration, Session Guard & Global Helpers
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "smart_store_credit_db";

$conn = mysqli_connect($host, $user, $pass, $dbname);

if ($conn) {
    mysqli_set_charset($conn, "utf8mb4");
}

/**
 * Authentication Gatekeeper
 * Automatically redirects unauthenticated users to login.php
 */
function checkAuth() {
    $currentScript = basename($_SERVER['PHP_SELF']);
    if ($currentScript !== 'login.php' && $currentScript !== 'index.php') {
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            header("Location: login.php");
            exit;
        }
    }
}

/**
 * Fetch Current Administrator Profile
 */
function getAdminProfile($conn) {
    $default = [
        'id' => 1,
        'store_name' => 'Al-Noor General Store',
        'full_name' => 'Store Administrator',
        'email' => 'admin@smartstorecredit.com',
        'phone' => '03001234567',
        'username' => 'admin',
        'profile_image' => ''
    ];
    if (!$conn) return $default;
    
    $res = mysqli_query($conn, "SELECT * FROM admins WHERE id = 1 LIMIT 1");
    if ($res && $row = mysqli_fetch_assoc($res)) {
        return $row;
    }
    return $default;
}
?>
