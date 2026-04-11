// Using Web Crypto API for JWT
const encoder = new TextEncoder();

// Base64URL encoding/decoding
function bufferToBase64Url(buffer) {
    const bytes = new Uint8Array(buffer);
    let binary = '';
    for (let i = 0; i < bytes.byteLength; i++) {
        binary += String.fromCharCode(bytes[i]);
    }
    return btoa(binary)
        .replace(/\+/g, '-')
        .replace(/\//g, '_')
        .replace(/=/g, '');
}

function base64UrlToBuffer(base64url) {
    const base64 = base64url.replace(/-/g, '+').replace(/_/g, '/');
    const padding = '='.repeat((4 - base64.length % 4) % 4);
    const binary = atob(base64 + padding);
    const bytes = new Uint8Array(binary.length);
    for (let i = 0; i < binary.length; i++) {
        bytes[i] = binary.charCodeAt(i);
    }
    return bytes.buffer;
}

// Generate JWT signature
async function sign(data, secret) {
    const key = await crypto.subtle.importKey(
        'raw', 
        encoder.encode(secret), 
        { name: 'HMAC', hash: 'SHA-256' }, 
        false, 
        ['sign']
    );
    const signature = await crypto.subtle.sign('HMAC', key, encoder.encode(data));
    return bufferToBase64Url(signature);
}

// Create JWT
export async function createJwt(payload, secret) {
    // Fallback secret for local if env not provided
    const jwtSecret = secret || 'shreebitu-fallback-secret-2026';
    const header = { alg: 'HS256', typ: 'JWT' };
    
    const encodedHeader = bufferToBase64Url(encoder.encode(JSON.stringify(header)));
    const encodedPayload = bufferToBase64Url(encoder.encode(JSON.stringify(payload)));
    
    const dataToSign = `${encodedHeader}.${encodedPayload}`;
    const signature = await sign(dataToSign, jwtSecret);
    
    return `${dataToSign}.${signature}`;
}

// Verify JWT
export async function verifyJwt(token, secret) {
    const jwtSecret = secret || 'shreebitu-fallback-secret-2026';
    try {
        const parts = token.split('.');
        if (parts.length !== 3) return null;
        
        const [encodedHeader, encodedPayload, signature] = parts;
        const dataToSign = `${encodedHeader}.${encodedPayload}`;
        
        const expectedSignature = await sign(dataToSign, jwtSecret);
        if (signature !== expectedSignature) return null;
        
        const payloadStr = new TextDecoder().decode(base64UrlToBuffer(encodedPayload));
        
        // Check for expiration
        const payload = JSON.parse(payloadStr);
        if (payload.exp && payload.exp < Math.floor(Date.now() / 1000)) {
            return null; // Expired
        }
        
        return payload;
    } catch (e) {
        return null;
    }
}
