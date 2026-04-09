export async function onRequestGet({ env }) {
    try {
        // Fetch all files marked as public
        const { results } = await env.DB.prepare(
            "SELECT id, file_name, file_type, metadata, created_at, user_id FROM files WHERE is_public = 1 ORDER BY created_at DESC"
        ).all();

        return new Response(JSON.stringify({ success: true, files: results }), { status: 200 });
    } catch (err) {
        return new Response(JSON.stringify({ error: err.message }), { status: 500 });
    }
}
