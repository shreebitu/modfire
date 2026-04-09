import { hashPassword } from '../utils/crypto';
import { createJwt } from '../utils/jwt';

export async function onRequestPost({ request, env }) {
    try {
        const { email, password } = await request.json();

        if (!email || !password) {
            return new Response(JSON.stringify({ error: 'Email and password are required' }), { status: 400 });
        }

        const user = await env.DB.prepare("SELECT id, password_hash FROM users WHERE email = ?").bind(email).first();
        if (!user) {
            return new Response(JSON.stringify({ error: 'Invalid credentials' }), { status: 401 });
        }

        const hashedPassword = await hashPassword(password);
        if (hashedPassword !== user.password_hash) {
            return new Response(JSON.stringify({ error: 'Invalid credentials' }), { status: 401 });
        }

        // Generate JWT
        const token = await createJwt({ userId: user.id, email: email, exp: Math.floor(Date.now() / 1000) + (86400 * 7) }, env.JWT_SECRET);

        const headers = new Headers();
        headers.set('Set-Cookie', `session=${token}; HttpOnly; Secure; Path=/; SameSite=Strict; Max-Age=${86400 * 7}`);
        headers.set('Content-Type', 'application/json');

        return new Response(JSON.stringify({ success: true, message: 'Logged in successfully' }), {
            status: 200,
            headers: headers
        });

    } catch (err) {
        return new Response(JSON.stringify({ error: err.message }), { status: 500 });
    }
}
