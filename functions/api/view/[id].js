import { verifyJwt } from '../../utils/jwt';

export async function onRequestGet({ request, env, params }) {
    try {
        const id = params.id;
        const file = await env.DB.prepare("SELECT id, user_id, file_name, file_type, is_public, metadata, created_at FROM files WHERE id = ?").bind(id).first();
        
        if (!file) {
            return new Response(JSON.stringify({ error: 'File not found' }), { status: 404 });
        }

        if (!file.is_public) {
            // Check auth
            const cookie = request.headers.get('Cookie') || '';
            const match = cookie.match(/session=([^;]+)/);
            const token = match ? match[1] : null;

            if (!token) return new Response(JSON.stringify({ error: 'File is private' }), { status: 403 });

            const payload = await verifyJwt(token, env.JWT_SECRET);
            if (!payload || payload.userId !== file.user_id) {
                return new Response(JSON.stringify({ error: 'File is private' }), { status: 403 });
            }
        }

        // Fetch uploader info
        const user = await env.DB.prepare("SELECT email FROM users WHERE id = ?").bind(file.user_id).first();

        return new Response(JSON.stringify({ 
            success: true, 
            file: {
                ...file,
                uploader_email: user ? user.email : 'Unknown'
            }
        }), { status: 200 });

    } catch (err) {
        return new Response(JSON.stringify({ error: err.message }), { status: 500 });
    }
}
