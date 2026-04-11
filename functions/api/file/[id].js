import { authenticate } from '../../_shared/auth';

export async function onRequestPut({ request, env, params }) {
    try {
        const user = await authenticate(request, env);
        if (!user) return new Response(JSON.stringify({ error: 'Unauthorized' }), { status: 401 });

        const id = params.id;
        const { file_name, is_public, metadata } = await request.json();

        // Verify ownership
        const file = await env.DB.prepare("SELECT id FROM files WHERE id = ? AND user_id = ?").bind(id, user.userId).first();
        if (!file) return new Response(JSON.stringify({ error: 'File not found or unauthorized' }), { status: 404 });

        await env.DB.prepare(
            "UPDATE files SET file_name = ?, is_public = ?, metadata = ? WHERE id = ?"
        ).bind(file_name, is_public ? 1 : 0, JSON.stringify(metadata), id).run();

        return new Response(JSON.stringify({ success: true, message: 'File updated successfully' }), { status: 200 });

    } catch (err) {
        return new Response(JSON.stringify({ error: err.message }), { status: 500 });
    }
}

export async function onRequestDelete({ request, env, params }) {
    try {
        const user = await authenticate(request, env);
        if (!user) return new Response(JSON.stringify({ error: 'Unauthorized' }), { status: 401 });

        const id = params.id;

        // Verify ownership and get r2_key
        const file = await env.DB.prepare("SELECT id, r2_key FROM files WHERE id = ? AND user_id = ?").bind(id, user.userId).first();
        if (!file) return new Response(JSON.stringify({ error: 'File not found or unauthorized' }), { status: 404 });

        // Delete from R2
        if (file.r2_key) {
            await env.R2.delete(file.r2_key);
        }

        // Delete from DB
        await env.DB.prepare("DELETE FROM files WHERE id = ?").bind(id).run();

        return new Response(JSON.stringify({ success: true, message: 'File deleted successfully' }), { status: 200 });

    } catch (err) {
        return new Response(JSON.stringify({ error: err.message }), { status: 500 });
    }
}
