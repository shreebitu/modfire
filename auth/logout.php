<?php
/**
 * ============================================================
 * LOGOUT HANDLER
 * ============================================================
 * Purpose: Destroys the user's session and redirects to login
 * Input:   None (session data is read internally)
 * Output:  Redirect to auth/login.php
 * Flow:    Log activity → clear session → destroy session → redirect
 * ============================================================
 */
require_once '../includes/init.php';

// Log the logout action before destroying the session
// (we need session data to know WHO is logging out)
if (isLoggedIn()) {
    logActivity('logout', 'User logged out');
}

// Remove all session variables from memory
session_unset();

// Destroy the session file on the server
session_destroy();

// Send the user back to the login page
redirect($base_url . 'auth/login.php');
?>
