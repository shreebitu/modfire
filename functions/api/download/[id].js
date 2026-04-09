export async function onRequestGet({ env, params }) {
    try {
        const id = params.id;
        
        // Find file in DB
        const file = await env.DB.prepare("SELECT r2_key, file_name, file_type, is_public FROM files WHERE id = ?").bind(id).first();
        if (!file) return new Response('File not found', { status: 404 });

        // If private, we should check auth in a real app. For now, just serve if public.
        if (!file.is_public) {
            // Simplified check: if not public, deny.
            return new Response('File is private', { status: 403 });
        }

        if (!file.r2_key) {
            return new Response('No physical file found', { status: 404 });
        }

        const object = await env.R2.get(file.r2_key);
        if (object === null) {
            return new Response('File object not found', { status: 404 });
        }

        const headers = new Headers();
        object.writeHttpMetadata(headers);
        headers.set('etag', object.httpEtag);
        headers.set('Content-Disposition', `attachment; filename="${file.file_name}"`);

        return new Response(object.body, {
            headers,
        });
    } catch (err) {
        return new Response(err.message, { status: 500 });
    }
}
