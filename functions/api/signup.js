// Fully self-contained signup function - no imports needed
// Cloudflare Pages Functions cannot use relative imports between /functions files

// --- Inline crypto helper ---
async function hashPassword(password) {
    const enc = new TextEncoder();
    const pepper = "shreebitu-pepper-2026";
    const data = enc.encode(password + pepper);
    const hashBuffer = await crypto.subtle.digest('SHA-256', data);
    return Array.from(new Uint8Array(hashBuffer))
        .map(b => b.toString(16).padStart(2, '0')).join('');
}

// --- Inline JWT helper ---
function bufferToBase64Url(buffer) {
    const bytes = new Uint8Array(buffer);
    let binary = '';
    for (let i = 0; i < bytes.byteLength; i++) binary += String.fromCharCode(bytes[i]);
    return btoa(binary).replace(/\+/g, '-').replace(/\//g, '_').replace(/=/g, '');
}

async function createJwt(payload, secret) {
    const enc = new TextEncoder();
    const key = await crypto.subtle.importKey(
        'raw', enc.encode(secret),
        { name: 'HMAC', hash: 'SHA-256' },
        false, ['sign']
    );
    const header = bufferToBase64Url(enc.encode(JSON.stringify({ alg: 'HS256', typ: 'JWT' })));
    const body = bufferToBase64Url(enc.encode(JSON.stringify(payload)));
    const sig = bufferToBase64Url(await crypto.subtle.sign('HMAC', key, enc.encode(`${header}.${body}`)));
    return `${header}.${body}.${sig}`;
}

// --- Main handler ---
export async function onRequestPost(context) {
    try {
        // Safe JSON parse
        let data;
        try {
            data = await context.request.json();
        } catch {
            return new Response(JSON.stringify({ error: "Invalid JSON body" }), {
                status: 400,
                headers: { "Content-Type": "application/json" }
            });
        }

        const { email, password } = data;

        // Validate
        if (!email || !password) {
            return new Response(JSON.stringify({ error: "Email and password are required" }), {
                status: 400,
                headers: { "Content-Type": "application/json" }
            });
        }

        // Check DB binding
        if (!context.env.DB) {
            return new Response(JSON.stringify({ error: "Database not configured. Bind D1 as 'DB' in Cloudflare Pages settings." }), {
                status: 500,
                headers: { "Content-Type": "application/json" }
            });
        }

        // Check existing user
        const existing = await context.env.DB.prepare(
            "SELECT id FROM users WHERE email = ?"
        ).bind(email).first();

        if (existing) {
            return new Response(JSON.stringify({ error: "Email already registered" }), {
                status: 400,
                headers: { "Content-Type": "application/json" }
            });
        }

        // Create user
        const id = crypto.randomUUID();
        const hashedPassword = await hashPassword(password);
        const createdAt = Date.now();

        await context.env.DB.prepare(
            "INSERT INTO users (id, email, password_hash, created_at) VALUES (?, ?, ?, ?)"
        ).bind(id, email, hashedPassword, createdAt).run();

        // Create JWT session
        const jwtSecret = context.env.JWT_SECRET || "shreebitu-fallback-secret-2026";
        const token = await createJwt(
            { userId: id, email, exp: Math.floor(Date.now() / 1000) + (86400 * 7) },
            jwtSecret
        );

        const headers = new Headers();
        headers.set("Content-Type", "application/json");
        headers.set("Set-Cookie", `session=${token}; HttpOnly; Secure; Path=/; SameSite=Strict; Max-Age=${86400 * 7}`);

        return new Response(JSON.stringify({ success: true, message: "Account created successfully" }), {
            status: 201,
            headers
        });

    } catch (err) {
        return new Response(JSON.stringify({ error: err.message || "Internal server error" }), {
            status: 500,
            headers: { "Content-Type": "application/json" }
        });
    }
}
