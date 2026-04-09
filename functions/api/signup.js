import { hashPassword } from '../utils/crypto';
import { createJwt } from '../utils/jwt';

export async function onRequestPost({ request, env }) {
    try {
        const { email, password } = await request.json();

        if (!email || !password) {
            return new Response(JSON.stringify({ error: 'Email and password are required' }), { status: 400 });
        }

        // Check if user already exists
        const existingUser = await env.DB.prepare("SELECT id FROM users WHERE email = ?").bind(email).first();
        if (existingUser) {
            return new Response(JSON.stringify({ error: 'Email already registered' }), { status: 400 });
        }

        const id = crypto.randomUUID();
        const hashedPassword = await hashPassword(password);
        const createdAt = Date.now();

        // Insert new user
        await env.DB.prepare(
            "INSERT INTO users (id, email, password_hash, created_at) VALUES (?, ?, ?, ?)"
        ).bind(id, email, hashedPassword, createdAt).run();

        // Generate JWT
        const token = await createJwt({ userId: id, email: email, exp: Math.floor(Date.now() / 1000) + (86400 * 7) }, env.JWT_SECRET); // 7 days expiry

        const headers = new Headers();
        headers.set('Set-Cookie', `session=${token}; HttpOnly; Secure; Path=/; SameSite=Strict; Max-Age=${86400 * 7}`);
        headers.set('Content-Type', 'application/json');

        return new Response(JSON.stringify({ success: true, message: 'Account created' }), {
            status: 201,
            headers: headers
        });

    } catch (err) {
        return new Response(JSON.stringify({ error: err.message }), { status: 500 });
    }
}
