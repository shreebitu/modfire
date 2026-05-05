<?php
/**
 * ============================================================
 * CORE HELPER FUNCTIONS
 * ============================================================
 * Purpose: Contains all reusable utility functions used across
 *          the entire application. These are loaded by init.php
 *          and available on every page.
 *
 * Functions:
 *   - isLoggedIn()      → Check if user has an active session
 *   - isAdmin()         → Check if user has admin role
 *   - sanitizeInput()   → Clean user input to prevent XSS
 *   - redirect()        → Redirect to another URL
 *   - requireLogin()    → Block access if not logged in
 *   - logActivity()     → Record user actions in activity_logs
 *   - verifyCsrfToken() → Validate CSRF token from forms
 *   - csrfField()       → Generate hidden CSRF input for forms
 *   - isOwner()         → Check if user owns a specific app
 *   - formatNumber()    → Format numbers with commas (1000 → 1,000)
 *   - timeAgo()         → Convert timestamp to "2 hours ago" format
 *   - renderAppCard()   → Generate HTML for an app card component
 * ============================================================
 */

/**
 * Check if a user is logged in
 * 
 * Looks for 'user_id' in the session — set during login.
 * Used throughout the app to show/hide features for authenticated users.
 * 
 * @return bool True if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if a user is an admin
 * 
 * Admins have 'role' => 'admin' set in their session during login.
 * Used to gate access to admin panel pages and admin-only features.
 * 
 * @return bool True if user has admin role
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Sanitize input to prevent XSS (Cross-Site Scripting) attacks
 * 
 * Applies three layers of protection:
 *   1. trim()           → Remove leading/trailing whitespace
 *   2. strip_tags()     → Remove all HTML/PHP tags
 *   3. htmlspecialchars()→ Convert special chars to HTML entities
 * 
 * Handles arrays recursively (e.g., for $_POST arrays).
 * 
 * @param string|array $data The input to sanitize
 * @return string|array Sanitized output
 */
function sanitizeInput($data) {
    // If input is an array, recursively sanitize each element
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    // Strip tags, trim whitespace, and convert special characters
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect to a specific URL
 * 
 * Sends a Location header and immediately stops script execution.
 * Always call exit() after header() to prevent code from continuing.
 * 
 * @param string $url The URL to redirect to
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Require login to access a page
 * 
 * If the user is not logged in, redirect them to the login page.
 * Call this at the top of any page that requires authentication.
 */
function requireLogin() {
    global $base_url;
    if (!isLoggedIn()) {
        redirect($base_url . "auth/login.php");
    }
}

/**
 * Log user activity to the activity_logs table
 * 
 * Records every important action for audit purposes:
 *   - Who did it (user_id, or null for guests)
 *   - What they did (action name)
 *   - Extra details (optional context)
 *   - Their IP address and browser info
 * 
 * @param string $action  Short action identifier (e.g., 'login', 'upload_success')
 * @param string|null $details  Optional additional context
 */
function logActivity($action, $details = null) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $_SESSION['user_id'] ?? null,   // User ID (null if guest)
            $action,                         // Action type
            $details,                        // Extra context
            $_SERVER['REMOTE_ADDR'],         // Client IP address
            $_SERVER['HTTP_USER_AGENT']      // Browser/device info
        ]);
    } catch (PDOException $e) {
        // Fail silently — logging should never break the main application
    }
}

/**
 * Verify CSRF token from a form submission
 * 
 * Compares the token sent in the form with the one stored in the session.
 * Uses hash_equals() for timing-safe comparison (prevents timing attacks).
 * If tokens don't match, the request is rejected immediately.
 * 
 * @param string $token The CSRF token from the submitted form
 */
function verifyCsrfToken($token) {
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        die("CSRF token validation failed. Please try again.");
    }
}

/**
 * Get CSRF token field for HTML forms
 * 
 * Returns a hidden input field containing the current CSRF token.
 * Include this in every HTML form that uses POST method.
 * 
 * Usage: <?php echo csrfField(); ?> inside a <form>
 * 
 * @return string HTML hidden input element
 */
function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . ($_SESSION['csrf_token'] ?? '') . '">';
}

/**
 * Check if the current user is the owner of a specific app
 * 
 * Queries the apps table to see if the app's user_id matches
 * the currently logged-in user. Used to allow editing/deleting.
 * 
 * @param int $app_id The ID of the app to check
 * @return bool True if the current user submitted this app
 */
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

/**
 * Format number for display (e.g. 1000 → 1,000)
 * 
 * @param int|string $num The number to format
 * @return string Formatted number with commas
 */
function formatNumber($num) {
    return number_format((int)$num);
}

/**
 * Get relative time string (e.g. "2 hours ago")
 * 
 * Converts a timestamp or datetime string into a human-readable
 * relative time. Falls back to formatted date for older entries.
 * 
 * @param string|int $timestamp Unix timestamp or datetime string
 * @return string Human-readable time difference
 */
function timeAgo($timestamp) {
    // Convert to Unix timestamp if it's a date string
    $time = is_numeric($timestamp) ? $timestamp : strtotime($timestamp);
    $diff = time() - $time;
    
    // Return relative time for recent events
    if ($diff < 60) return "just now";
    if ($diff < 3600) return floor($diff/60) . " mins ago";
    if ($diff < 86400) return floor($diff/3600) . " hours ago";
    if ($diff < 604800) return floor($diff/86400) . " days ago";
    
    // Return formatted date for events older than a week
    return date("M j, Y", $time);
}

/**
 * Render an app card component (used on homepage, category, favorites)
 * 
 * Generates a styled HTML card with:
 *   - App logo with fallback image
 *   - Favorite button (heart icon, toggles via AJAX)
 *   - Average rating badge
 *   - Category label
 *   - App name
 *   - Download count
 * 
 * Uses output buffering (ob_start/ob_get_clean) to capture HTML.
 * 
 * @param array  $app        App data from database query
 * @param string $base_url   Base URL of the site
 * @param string $assets_url Assets URL for fallback images
 * @return string Complete HTML for the app card
 */
function renderAppCard($app, $base_url, $assets_url) {
    // Check if current user is logged in (to show/hide favorite button)
    $is_logged_in = isLoggedIn();
    
    // Check if this app is in the user's favorites
    $is_favorited = isset($app['is_favorited']) && $app['is_favorited'];
    
    // Set CSS classes based on favorite state
    $fav_btn_class = $is_favorited ? 'bg-pink-500 text-white' : 'bg-white/80 text-slate-400 hover:text-pink-500';
    $fav_icon = $is_favorited ? 'favorite' : 'favorite_border';
    $fav_fill = $is_favorited ? 'fill-current' : '';
    
    // Determine logo URL — handle both absolute URLs and relative paths
    $logo_url = (strpos($app['logo'], 'http') === 0) ? $app['logo'] : $base_url . $app['logo'];
    
    // Fallback image if logo fails to load
    $placeholder_logo = $assets_url . 'images/logo.png';
    
    // Start capturing output
    ob_start();
    ?>
    <a href="<?php echo htmlspecialchars($app['slug']); ?>"
        class="app-card bg-white p-4 rounded-[28px] flex flex-col group h-full">
        <!-- Logo Container with aspect ratio -->
        <div class="aspect-square w-full rounded-2xl bg-slate-50 mb-4 overflow-hidden relative shadow-inner">
            <!-- App Logo Image (with fallback on error) -->
            <img src="<?php echo htmlspecialchars($logo_url); ?>"
                alt="<?php echo htmlspecialchars($app['name']); ?>"
                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                onerror="this.src='<?php echo $placeholder_logo; ?>'">

            <!-- Favorite Toggle Button (only shown to logged-in users) -->
            <?php if ($is_logged_in): ?>
                <button onclick="toggleFavorite(event, <?php echo (int)$app['id']; ?>)"
                    id="favBtn-<?php echo (int)$app['id']; ?>"
                    class="absolute top-2 left-2 w-7 h-7 rounded-lg flex items-center justify-center transition-all shadow-sm backdrop-blur-md active:scale-90 z-20 <?php echo $fav_btn_class; ?>">
                    <span class="material-symbols-outlined <?php echo $fav_fill; ?> text-[16px]"><?php echo $fav_icon; ?></span>
                </button>
            <?php endif; ?>

            <!-- Average Rating Badge (shown if app has ratings) -->
            <?php if (!empty($app['avg_rating'])): ?>
                <div class="absolute bottom-2 right-2 px-2 py-1 bg-white/90 backdrop-blur rounded-lg shadow-sm flex items-center gap-1">
                    <span class="material-symbols-outlined text-amber-400 text-[12px] fill-current">star</span>
                    <span class="text-[10px] font-bold text-slate-700"><?php echo htmlspecialchars($app['avg_rating']); ?></span>
                </div>
            <?php endif; ?>
        </div>
        <!-- App Info Section -->
        <div class="flex-1">
            <!-- Category Label -->
            <span class="text-[9px] font-black text-indigo-600 uppercase tracking-widest mb-1 block">
                <?php echo htmlspecialchars($app['category_name'] ?? 'General'); ?>
            </span>
            <!-- App Name (truncated to 2 lines) -->
            <h4 class="text-[14px] font-bold text-slate-900 leading-tight line-clamp-2 group-hover:text-indigo-600 transition-colors">
                <?php echo htmlspecialchars($app['name']); ?>
            </h4>
        </div>
        <!-- Footer: Download Count + Arrow -->
        <div class="mt-4 pt-4 border-t border-slate-50 flex items-center justify-between">
            <div class="flex items-center gap-1.5">
                <span class="material-symbols-outlined text-slate-400 text-[14px]">download_for_offline</span>
                <span class="text-[10px] font-black text-slate-500"><?php echo formatNumber($app['downloads']); ?></span>
            </div>
            <!-- Animated arrow that appears on hover -->
            <span class="material-symbols-outlined text-slate-300 group-hover:text-indigo-600 transition-colors translate-x-2 opacity-0 group-hover:opacity-100 group-hover:translate-x-0 transition-all">arrow_forward</span>
        </div>
    </a>
    <?php
    // Return the captured HTML string
    return ob_get_clean();
}
