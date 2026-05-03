<?php
session_start();
ob_start();

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Global Configuration
define('DEBUG_MODE', true); // Set to false in production

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Base URL of the project
$base_url = '/shreebitu/';
$assets_url = $base_url . 'assets/';

require_once 'db.php';

// Load Global Settings
try {
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
    $site_settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (PDOException $e) {
    $site_settings = [];
}

// IP Block Check
$current_ip = $_SERVER['REMOTE_ADDR'];
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM blocked_ips WHERE ip_address = ?");
    $stmt->execute([$current_ip]);
    if ($stmt->fetchColumn() > 0) {
        die("Your access to this platform has been restricted by the administrator.");
    }
} catch (PDOException $e) {
    // Fail silently if table doesn't exist yet
}

// Maintenance Mode Check (Except for Admins and Login Page)
$current_page = $_SERVER['SCRIPT_NAME'];
$is_auth_page = strpos($current_page, 'auth/') !== false;

if (($site_settings['maintenance_mode'] ?? '0') == '1' && !isAdmin() && !$is_auth_page) {
    die("
    <div style='font-family: sans-serif; display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100vh; background: #f8fafc; color: #1e293b; text-align: center; padding: 20px;'>
        <div style='background: white; padding: 40px; border-radius: 24px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); max-width: 500px;'>
            <h1 style='font-size: 2rem; margin-bottom: 10px; color: #1f108e;'>Site Under Maintenance</h1>
            <p style='color: #64748b; line-height: 1.6;'>We are currently performing scheduled updates to improve your experience. Please check back shortly.</p>
            <div style='margin-top: 30px; border-top: 1px solid #e2e8f0; pt: 20px;'>
                <a href='{$base_url}auth/login.php' style='font-size: 0.8rem; color: #94a3b8; text-decoration: none;'>Admin Entry</a>
            </div>
        </div>
    </div>
    ");
}

// Session Token Check (Force Logout)
if (isLoggedIn()) {
    try {
        $stmt = $pdo->prepare("SELECT session_token FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $db_token = $stmt->fetchColumn();
        if ($db_token !== null && (!isset($_SESSION['session_token']) || $_SESSION['session_token'] !== $db_token)) {
            session_destroy();
            redirect($base_url . "auth/login.php?error=session_expired");
        }
    } catch (PDOException $e) {}
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function logActivity($action, $details = null) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $_SESSION['user_id'] ?? null,
            $action,
            $details,
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT']
        ]);
    } catch (PDOException $e) {}
}

function requireLogin() {
    global $base_url;
    if (!isLoggedIn()) {
        header("Location: " . $base_url . "auth/login.php");
        exit();
    }
}

function redirect($url) {
    header("Location: " . $url);
    exit();
}

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function isOwner($app_id) {
    global $pdo;
    if (!isLoggedIn()) return false;
    try {
        $stmt = $pdo->prepare("SELECT user_id FROM apps WHERE id = ?");
        $stmt->execute([$app_id]);
        return $stmt->fetchColumn() == $_SESSION['user_id'];
    } catch (PDOException $e) {
        return false;
    }
}

function verifyCsrfToken($token) {
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        die("CSRF token validation failed. Please try again.");
    }
}
?>
