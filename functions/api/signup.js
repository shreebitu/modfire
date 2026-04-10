export async function onRequestPost(context) {
  try {
    const data = await context.request.json();

    if (!data.email || !data.password) {
      return new Response(JSON.stringify({
        error: "Email and password required"
      }), {
        status: 400,
        headers: { "Content-Type": "application/json" }
      });
    }

    return new Response(JSON.stringify({
      success: true,
      message: "Signup working"
    }), {
      status: 200,
      headers: { "Content-Type": "application/json" }
    });

  } catch (err) {
    return new Response(JSON.stringify({
      error: "Invalid request"
    }), {
      status: 400,
      headers: { "Content-Type": "application/json" }
    });
  }
}
