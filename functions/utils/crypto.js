// A simple salted hash using Web Crypto API. 

export async function hashPassword(password) {
    const enc = new TextEncoder();
    // Using a static pepper for simplicity.
    const pepper = "shreebitu-pepper-2026";
    const data = enc.encode(password + pepper);
    const hashBuffer = await crypto.subtle.digest('SHA-256', data);
    const hashArray = Array.from(new Uint8Array(hashBuffer));
    return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
}
