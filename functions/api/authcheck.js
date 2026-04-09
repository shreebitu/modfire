import { verifyJwt } from '../utils/jwt';

export async function onRequestGet({ request, env }) {
    const cookie = request.headers.get('Cookie') || '';
    const match = cookie.match(/session=([^;]+)/);
    const token = match ? match[1] : null;

    if (!token) {
        return new Response(JSON.stringify({ authenticated: false }), { status: 200 });
    }

    const payload = await verifyJwt(token, env.JWT_SECRET);
    if (!payload) {
        return new Response(JSON.stringify({ authenticated: false }), { status: 200 });
    }

    return new Response(JSON.stringify({ 
        authenticated: true, 
        user: { id: payload.userId, email: payload.email } 
    }), { status: 200 });
}
