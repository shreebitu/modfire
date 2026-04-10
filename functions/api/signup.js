import { hashPassword } from '../utils/crypto';
import { createJwt } from '../utils/jwt';

export async function onRequestPost(context) {
    try {
        // Safe JSON parsing
        let data;
        try {
            data = await context.request.json();
        } catch (e) {
            return new Response(JSON.stringify({ error: "Invalid or empty request body" }), {
                status: 400,
                headers: { "Content-Type": "application/json" }
            });
        }

        const { email, password } = data;

        // Validate fields
        if (!email || !password) {
            return new Response(JSON.stringify({ error: "Email and password are required" }), {
                status: 400,
                headers: { "Content-Type": "application/json" }
            });
        }

        // Check DB binding
        if (!context.env.DB) {
            return new Response(JSON.stringify({ error: "Database not configured. Please bind D1 database as 'DB' in Cloudflare Pages settings." }), {
                status: 500,
                headers: { "Content-Type": "application/json" }
            });
        }

        // Check if user already exists
        const existing = await context.env.DB.prepare("SELECT id FROM users WHERE email = ?").bind(email).first();
        if (existing) {
            return new Response(JSON.stringify({ error: "Email already registered" }), {
                status: 400,
                headers: { "Content-Type": "application/json" }
            });
        }

        // Create user
        const id = crypto.randomUUID();
        const hashedPassword = await hashPassword(password);
        const createdAt = Date.now();

        await context.env.DB.prepare(
            "INSERT INTO users (id, email, password_hash, created_at) VALUES (?, ?, ?, ?)"
        ).bind(id, email, hashedPassword, createdAt).run();

        // Create session token
        const token = await createJwt(
            { userId: id, email, exp: Math.floor(Date.now() / 1000) + (86400 * 7) },
            context.env.JWT_SECRET || "shreebitu-fallback-secret-2026"
        );

        const headers = new Headers();
        headers.set("Content-Type", "application/json");
        headers.set("Set-Cookie", `session=${token}; HttpOnly; Secure; Path=/; SameSite=Strict; Max-Age=${86400 * 7}`);

        return new Response(JSON.stringify({ success: true, message: "Account created successfully" }), {
            status: 201,
            headers
        });

    } catch (err) {
        return new Response(JSON.stringify({ error: err.message || "Internal server error" }), {
            status: 500,
            headers: { "Content-Type": "application/json" }
        });
    }
}
