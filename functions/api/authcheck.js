// Cloudflare Pages Function: Auth Check Handler
// Path: /functions/api/authcheck.js -> checks session validity

import { verifyJwt } from '../_shared/jwt';

export async function onRequestGet({ request, env }) {
    const headers = new Headers({
        "Content-Type": "application/json",
        "Access-Control-Allow-Origin": "*",
    });

    try {
        const cookie = request.headers.get('Cookie') || '';
        const token = cookie.match(/session=([^;]+)/)?.[1];

        if (!token) {
            return new Response(JSON.stringify({ authenticated: false }), { status: 200, headers });
        }

        const jwtSecret = env.JWT_SECRET || "shreebitu-fallback-secret-2026";
        const payload = await verifyJwt(token, jwtSecret);

        if (!payload) {
            return new Response(JSON.stringify({ authenticated: false, error: "Invalid or expired session" }), { status: 200, headers });
        }

        return new Response(JSON.stringify({ 
            authenticated: true, 
            user: { id: payload.userId, email: payload.email } 
        }), { status: 200, headers });

    } catch (err) {
        console.error("Auth check error:", err);
        return new Response(JSON.stringify({ authenticated: false, error: "Server error" }), { status: 500, headers });
    }
}

export async function onRequestOptions() {
    return new Response(null, {
        status: 204,
        headers: {
            "Access-Control-Allow-Origin": "*",
            "Access-Control-Allow-Methods": "GET, OPTIONS",
            "Access-Control-Allow-Headers": "Content-Type",
            "Access-Control-Max-Age": "86400",
        }
    });
}
