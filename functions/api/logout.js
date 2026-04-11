// Cloudflare Pages Function: Logout Handler
// Path: /functions/api/logout.js -> clears the session cookie

export async function onRequestPost() {
    const headers = new Headers({
        "Content-Type": "application/json",
        "Set-Cookie": "session=; HttpOnly; Secure; Path=/; SameSite=Strict; Max-Age=0; Expires=Thu, 01 Jan 1970 00:00:00 GMT",
        "Access-Control-Allow-Origin": "*",
    });

    return new Response(JSON.stringify({ 
        success: true, 
        message: "You have been logged out successfully." 
    }), { status: 200, headers });
}

export async function onRequestOptions() {
    return new Response(null, {
        status: 204,
        headers: {
            "Access-Control-Allow-Origin": "*",
            "Access-Control-Allow-Methods": "POST, OPTIONS",
            "Access-Control-Allow-Headers": "Content-Type",
            "Access-Control-Max-Age": "86400",
        }
    });
}
