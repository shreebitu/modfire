<?php
/**
 * ============================================================
 * GLOBAL INITIALIZATION FILE (BOOTSTRAP)
 * ============================================================
 * Purpose: This is the MOST IMPORTANT file in the project.
 *          It runs before every page and sets up the environment:
 *          - Starts the PHP session
 *          - Connects to the database
 *          - Loads configuration and helper functions
 *          - Generates CSRF tokens for form security
 *          - Loads site-wide settings from the database
 *          - Checks if the visitor's IP is blocked
 *          - Enforces maintenance mode
 *          - Validates active user sessions
 *
 * Used by: Every public page, auth page, user page, admin page
 * Includes: db.php, config.php, functions.php
 * ============================================================
 */

// ── Step 1: Start PHP Session ──
// Sessions store user login state, CSRF tokens, and flash messages
// Only start if not already active (prevents "session already started" warnings)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Step 2: Enable Output Buffering ──
// This allows us to send headers (like redirects) even after HTML output
// Without this, calling header() after echo would cause errors
ob_start();

// ── Step 3: Include Core Files ──
// db.php      → Creates $pdo (database connection)
// config.php  → Sets $base_url, $assets_url, DEBUG_MODE
// functions.php → Loads all helper functions (isLoggedIn, sanitize, etc.)
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../config.php'; // We will clean this up next
require_once __DIR__ . '/functions.php';

// ── Step 4: Generate CSRF Token ──
// CSRF (Cross-Site Request Forgery) tokens prevent malicious form submissions
// A unique token is created per session and must be included in every form
// The token is verified when the form is submitted (see verifyCsrfToken())
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // 64-char hex string
}

// ── Step 5: Load Global Site Settings ──
// Settings are stored as key-value pairs in the 'settings' table
// Examples: maintenance_mode, registration_enabled, upload_enabled
// These control site behavior without changing code
try {
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
    // fetchAll with FETCH_KEY_PAIR creates an associative array: ['key' => 'value']
    $site_settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (PDOException $e) {
    // If settings table doesn't exist yet, use empty defaults
    $site_settings = [];
}

// ── Step 6: IP Block Check ──
// Admins can block specific IP addresses from the security panel
// Every visitor's IP is checked against the blocked_ips table
$current_ip = $_SERVER['REMOTE_ADDR'];
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM blocked_ips WHERE ip_address = ?");
    $stmt->execute([$current_ip]);
    // If this IP is found in the blocked list, deny access immediately
    if ($stmt->fetchColumn() > 0) {
        die("Your access to this platform has been restricted by the administrator.");
    }
} catch (PDOException $e) {
    // If blocked_ips table doesn't exist, fail silently and allow access
}

// ── Step 7: Maintenance Mode Check ──
// When maintenance_mode = '1' in settings, only admins can access the site
// Auth pages are exempt (so admins can still login)
$current_page_uri = $_SERVER['SCRIPT_NAME'];
$is_auth_uri = strpos($current_page_uri, 'auth/') !== false;

if (($site_settings['maintenance_mode'] ?? '0') == '1' && !isAdmin() && !$is_auth_uri) {
    // Show a styled maintenance page and stop execution
    die("
    <div style='font-family: sans-serif; display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100vh; background: #f8fafc; color: #1e293b; text-align: center; padding: 20px;'>
        <div style='background: white; padding: 40px; border-radius: 24px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); max-width: 500px;'>
            <h1 style='font-size: 2rem; margin-bottom: 10px; color: #1f108e;'>Site Under Maintenance</h1>
            <p style='color: #64748b; line-height: 1.6;'>We are currently performing scheduled updates to improve your experience. Please check back shortly.</p>
            <div style='margin-top: 30px; border-top: 1px solid #e2e8f0; padding-top: 20px;'>
                <a href='{$base_url}auth/login.php' style='font-size: 0.8rem; color: #94a3b8; text-decoration: none;'>Admin Entry</a>
            </div>
        </div>
    </div>
    ");
}

// ── Step 8: Session Token Validation ──
// Each logged-in user has a session_token stored in both:
//   - The PHP session ($_SESSION['session_token'])
//   - The database (users.session_token column)
// If an admin changes the token in the DB (e.g., force logout), the session becomes invalid
// This allows admins to remotely invalidate user sessions
if (isLoggedIn()) {
    try {
        // Fetch the current token stored in the database for this user
        $stmt = $pdo->prepare("SELECT session_token FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $db_token = $stmt->fetchColumn();

        // If DB token exists but doesn't match the session token → force logout
        if ($db_token !== null && (!isset($_SESSION['session_token']) || $_SESSION['session_token'] !== $db_token)) {
            session_destroy();
            redirect($base_url . "auth/login.php?error=session_expired");
        }
    } catch (PDOException $e) {
        // If query fails, allow the session to continue (fail-open)
    }
}
