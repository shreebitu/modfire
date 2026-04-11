import { authenticate } from '../_shared/auth';

export async function onRequestGet({ request, env }) {
    try {
        const user = await authenticate(request, env);
        if (!user) {
            return new Response(JSON.stringify({ error: 'Unauthorized' }), { status: 401 });
        }

        const { results } = await env.DB.prepare(
            "SELECT id, file_name, file_type, r2_key, is_public, metadata, created_at FROM files WHERE user_id = ? ORDER BY created_at DESC"
        ).bind(user.userId).all();

        return new Response(JSON.stringify({ success: true, files: results }), { status: 200 });

    } catch (err) {
        return new Response(JSON.stringify({ error: err.message }), { status: 500 });
    }
}
