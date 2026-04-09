export async function onRequestPost() {
    const headers = new Headers();
    // Clear the cookie by setting it to expire in the past
    headers.set('Set-Cookie', `session=; HttpOnly; Secure; Path=/; SameSite=Strict; Max-Age=0; Expires=Thu, 01 Jan 1970 00:00:00 GMT`);
    headers.set('Content-Type', 'application/json');

    return new Response(JSON.stringify({ success: true, message: 'Logged out successfully' }), {
        status: 200,
        headers: headers
    });
}
