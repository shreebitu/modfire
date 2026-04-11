import { authenticate } from '../_shared/auth';

export async function onRequestPost({ request, env }) {
    try {
        const user = await authenticate(request, env);
        if (!user) {
            return new Response(JSON.stringify({ error: 'Unauthorized' }), { status: 401 });
        }

        const formData = await request.formData();
        const file = formData.get('file');
        const fileName = formData.get('file_name');
        const fileType = formData.get('file_type'); // 'ppt', 'apk', 'zip', 'github'
        const isPublic = formData.get('is_public') === 'true';
        const metadataStr = formData.get('metadata') || '{}';
        
        let metadata = {};
        try {
            metadata = JSON.parse(metadataStr);
        } catch (e) {
            return new Response(JSON.stringify({ error: 'Invalid metadata format' }), { status: 400 });
        }

        if (!fileName || !fileType) {
            return new Response(JSON.stringify({ error: 'Missing required fields' }), { status: 400 });
        }

        const fileId = crypto.randomUUID();
        let r2Key = null;

        // If a physical file is uploaded (not just a github submission)
        if (file && file.size > 0) {
            r2Key = `${user.userId}/${fileId}-${file.name.replace(/[^a-zA-Z0-9.-]/g, '_')}`;
            await env.R2.put(r2Key, file);
        }

        const createdAt = Date.now();

        await env.DB.prepare(
            "INSERT INTO files (id, user_id, file_name, file_type, r2_key, is_public, metadata, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        ).bind(
            fileId,
            user.userId,
            fileName,
            fileType,
            r2Key,
            isPublic ? 1 : 0,
            JSON.stringify(metadata),
            createdAt
        ).run();

        return new Response(JSON.stringify({ success: true, message: 'File uploaded successfully', fileId }), { status: 201 });

    } catch (err) {
        return new Response(JSON.stringify({ error: err.message }), { status: 500 });
    }
}
