// Cloudflare Pages Function: Signup Handler
// Path: /functions/api/signup.js -> routes to /api/signup

// --- Inline crypto helper for portability ---
async function hashPassword(password) {
    const enc = new TextEncoder();
    const pepper = "shreebitu-pepper-2026"; // Consistent with login
    const data = enc.encode(password + pepper);
    const hashBuffer = await crypto.subtle.digest('SHA-256', data);
    return Array.from(new Uint8Array(hashBuffer))
        .map(b => b.toString(16).padStart(2, '0')).join('');
}

// --- Inline JWT helper for portability ---
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

/**
 * Handle POST /api/signup
 */
export async function onRequestPost(context) {
    const headers = new Headers({
        "Content-Type": "application/json",
        "Access-Control-Allow-Origin": "*", // Allow local development testing
    });

    try {
        // 1. Parse JSON Body
        let data;
        try {
            data = await context.request.json();
        } catch (e) {
            return new Response(JSON.stringify({ error: "Invalid JSON in request body." }), { status: 400, headers });
        }

        const { email, password } = data;

        // 2. Validate Input
        if (!email || !password) {
            return new Response(JSON.stringify({ error: "Email and password are required." }), { status: 400, headers });
        }

        if (password.length < 6) {
            return new Response(JSON.stringify({ error: "Password must be at least 6 characters long." }), { status: 400, headers });
        }

        // 3. Database Connection Check
        if (!context.env.DB) {
            console.error("D1 Database binding 'DB' not found.");
            return new Response(JSON.stringify({ error: "Database configuration error. Please bind D1 as 'DB'." }), { status: 500, headers });
        }

        const db = context.env.DB;

        // 4. Ensure Table Exists (Schema defined in schema.sql)
        await db.prepare(`
            CREATE TABLE IF NOT EXISTS users (
                id TEXT PRIMARY KEY,
                email TEXT UNIQUE NOT NULL,
                password_hash TEXT NOT NULL,
                created_at INTEGER NOT NULL
            )
        `).run();

        // 5. Check if User Exists
        const existing = await db.prepare("SELECT id FROM users WHERE email = ?").bind(email.toLowerCase()).first();
        if (existing) {
            return new Response(JSON.stringify({ error: "This email is already registered." }), { status: 409, headers });
        }

        // 6. Create New User
        const id = crypto.randomUUID();
        const hashedPassword = await hashPassword(password);
        const createdAt = Date.now();

        await db.prepare(
            "INSERT INTO users (id, email, password_hash, created_at) VALUES (?, ?, ?, ?)"
        ).bind(id, email.toLowerCase(), hashedPassword, createdAt).run();

        // 7. Generate Authentication Session (JWT)
        const jwtSecret = context.env.JWT_SECRET || "shreebitu-fallback-secret-2026";
        const token = await createJwt(
            { userId: id, email: email.toLowerCase(), exp: Math.floor(Date.now() / 1000) + (86400 * 7) }, // 7 days
            jwtSecret
        );

        // 8. Set Secure Cookie
        headers.set("Set-Cookie", `session=${token}; HttpOnly; Secure; Path=/; SameSite=Strict; Max-Age=${86400 * 7}`);

        return new Response(JSON.stringify({ 
            success: true, 
            message: "Welcome to ShreeBitu! Your account has been created successfully." 
        }), { status: 201, headers });

    } catch (err) {
        console.error("Signup error:", err);
        return new Response(JSON.stringify({ error: "Internal Server Error: " + err.message }), { status: 500, headers });
    }
}

/**
 * Handle OPTIONS (CORS preflight)
 */
export async function onRequestOptions() {
    return new Response(null, {
        status: 204,
        headers: {
            "Access-Control-Allow-Origin": "*",
            "Access-Control-Allow-Methods": "POST, OPTIONS",
            "Access-Control-Allow-Headers": "Content-Type",
            "Access-Control-Max-Age": "86400",
        }
    });
}
