import { verifyJwt } from './jwt';

// Helper to authenticate requests in specific endpoints
export async function authenticate(request, env) {
    const cookie = request.headers.get('Cookie') || '';
    const match = cookie.match(/session=([^;]+)/);
    const token = match ? match[1] : null;

    if (!token) return null;

    const payload = await verifyJwt(token, env.JWT_SECRET);
    if (!payload) return null;

    return payload; // { userId, email, exp }
}
