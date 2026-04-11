// Cloudflare Pages Function: Login Handler
// Path: /functions/api/login.js -> routes to /api/login

import { hashPassword } from '../_shared/crypto';
import { createJwt } from '../_shared/jwt';

/**
 * Handle POST /api/login
 */
export async function onRequestPost(context) {
    const headers = new Headers({
        "Content-Type": "application/json",
        "Access-Control-Allow-Origin": "*",
    });

    try {
        // 1. Parse JSON Body
        let data;
        try {
            data = await context.request.json();
        } catch (e) {
            return new Response(JSON.stringify({ error: "Invalid JSON in request body." }), { status: 400, headers });
        }

        const { email, password } = data;

        // 2. Validate Input
        if (!email || !password) {
            return new Response(JSON.stringify({ error: "Email and password are required." }), { status: 400, headers });
        }

        // 3. Database Connection Check
        if (!context.env.DB) {
            return new Response(JSON.stringify({ error: "Database configuration error. Please bind D1 as 'DB'." }), { status: 500, headers });
        }

        const db = context.env.DB;

        // 4. Find User
        const user = await db.prepare("SELECT id, email, password_hash FROM users WHERE email = ?").bind(email.toLowerCase()).first();
        if (!user) {
            return new Response(JSON.stringify({ error: "Invalid email or password." }), { status: 401, headers });
        }

        // 5. Verify Password
        const inputHash = await hashPassword(password);
        if (inputHash !== user.password_hash) {
            return new Response(JSON.stringify({ error: "Invalid email or password." }), { status: 401, headers });
        }

        // 6. Generate Session (JWT)
        const jwtSecret = context.env.JWT_SECRET || "shreebitu-fallback-secret-2026";
        const token = await createJwt(
            { userId: user.id, email: user.email, exp: Math.floor(Date.now() / 1000) + (86400 * 7) },
            jwtSecret
        );

        // 7. Set Secure Cookie
        headers.set("Set-Cookie", `session=${token}; HttpOnly; Secure; Path=/; SameSite=Strict; Max-Age=${86400 * 7}`);

        return new Response(JSON.stringify({ 
            success: true, 
            message: "Login successful. Welcome back!" 
        }), { status: 200, headers });

    } catch (err) {
        console.error("Login error:", err);
        return new Response(JSON.stringify({ error: "Internal Server Error" }), { status: 500, headers });
    }
}

/**
 * Handle OPTIONS (CORS preflight)
 */
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
