import { hashPassword } from '../utils/crypto';
import { createJwt } from '../utils/jwt';

export async function onRequestPost(context) {
    try {
        const data = await context.request.json();
        const { email, password } = data;

        if (!email || !password) {
            return new Response(JSON.stringify({ error: "Missing fields" }), {
                status: 400,
                headers: { "Content-Type": "application/json" }
            });
        }

        if (!context.env.DB) {
            return new Response(JSON.stringify({ error: "Database not connected. Please bind D1 Database in Cloudflare Pages settings." }), {
                status: 500,
                headers: { "Content-Type": "application/json" }
            });
        }

        // Check if user already exists
        const existingUser = await context.env.DB.prepare("SELECT id FROM users WHERE email = ?").bind(email).first();
        if (existingUser) {
            return new Response(JSON.stringify({ error: "Email already registered" }), {
                status: 400,
                headers: { "Content-Type": "application/json" }
            });
        }

        const id = crypto.randomUUID();
        const hashedPassword = await hashPassword(password);
        const createdAt = Date.now();

        // Insert new user
        await context.env.DB.prepare(
            "INSERT INTO users (id, email, password_hash, created_at) VALUES (?, ?, ?, ?)"
        ).bind(id, email, hashedPassword, createdAt).run();

        // Generate JWT
        const token = await createJwt({ userId: id, email: email, exp: Math.floor(Date.now() / 1000) + (86400 * 7) }, context.env.JWT_SECRET);

        const headers = new Headers();
        headers.set('Set-Cookie', `session=${token}; HttpOnly; Secure; Path=/; SameSite=Strict; Max-Age=${86400 * 7}`);
        headers.set('Content-Type', 'application/json');

        return new Response(JSON.stringify({ success: true, message: "Account created" }), {
            status: 201,
            headers: headers
        });

    } catch (err) {
        return new Response(JSON.stringify({ error: err.message }), {
            status: 500,
            headers: { "Content-Type": "application/json" }
        });
    }
}
