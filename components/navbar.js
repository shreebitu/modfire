class GlobalNavbar extends HTMLElement {
    constructor() {
        super();
    }

    connectedCallback() {
        // Find if we are in a subdirectory (like apk/) to fix relative paths
        const depth = window.location.pathname.split('/').length - 2;
        const prefix = depth > 0 && !window.location.pathname.startsWith('/C:') ? '../'.repeat(depth) : './';
        // Using absolute path "/" is safest for Cloudflare Pages, but we keep relative if opened locally
        const rootPath = window.location.protocol === 'file:' ? prefix : '/';

        this.innerHTML = `
        <header class="sticky top-0 z-50 backdrop-blur-xl bg-[#0B0F19]/60 border-b border-white/5 shadow-sm">
            <div class="max-w-7xl mx-auto flex items-center justify-between px-6 h-16">
                <div class="flex items-center gap-3">
                    <a href="${rootPath}index.html" class="text-2xl font-extrabold tracking-wider text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-purple-400 hover:opacity-80 drop-shadow-md transition">SHREEBITU</a>
                </div>
                
                <nav class="hidden md:flex items-center gap-8 text-sm" id="mainNav">
                    <a href="${rootPath}index.html" class="hover:text-indigo-400 transition-colors duration-300">Home</a>
                    <a href="${rootPath}explore.html" class="hover:text-indigo-400 transition-colors duration-300">Explore</a>
                    <a href="${rootPath}about.html" class="hover:text-indigo-400 transition-colors duration-300">About</a>
                    <a href="${rootPath}contact.html" class="hover:text-indigo-400 transition-colors duration-300">Contact</a>
                </nav>

                <div id="authButtons" class="flex gap-4">
                    <a href="${rootPath}login.html" class="border border-white/10 px-4 py-2 rounded-lg text-sm font-medium transition hover:bg-white/5 text-gray-200">Login</a>
                    <a href="${rootPath}signup.html" class="bg-gradient-to-r from-indigo-600 to-purple-600 shadow-lg shadow-indigo-500/25 px-4 py-2 rounded-lg text-sm font-medium text-white transition hover:scale-105 active:scale-95">Sign Up</a>
                </div>
            </div>
        </header>
        `;

        // Run auth check script (if available) safely without replacing UI if offline
        setTimeout(() => {
            fetch('/api/authcheck')
                .then(r => {
                    if (r.ok) return r.json();
                    return { authenticated: false };
                })
                .then(data => {
                    if (data.authenticated) {
                        const nav = this.querySelector('#mainNav');
                        if (nav) nav.innerHTML += `<a href="${rootPath}dashboard.html" class="hover:text-indigo-400 font-medium text-indigo-400">Dashboard</a>`;
                        const btns = this.querySelector('#authButtons');
                        if (btns) btns.innerHTML = `<a href="${rootPath}upload.html" class="bg-gradient-to-r from-indigo-600 to-purple-600 px-4 py-2 rounded-lg text-sm font-medium text-white transition hover:scale-105">Upload File</a>`;
                    }
                })
                .catch(err => {
                    console.log('Static preview mode or backend offline.');
                });
        }, 100);
    }
}

customElements.define('global-navbar', GlobalNavbar);
