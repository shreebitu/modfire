class GlobalNavbar extends HTMLElement {
    constructor() {
        super();
    }

    connectedCallback() {
        const depth = window.location.pathname.split('/').length - 2;
        const prefix = depth > 0 && !window.location.pathname.startsWith('/C:') ? '../'.repeat(depth) : './';
        const rootPath = window.location.protocol === 'file:' ? prefix : '/';

        this.innerHTML = `
        <header class="main-header">
            <div class="nav-container">
                <a href="${rootPath}index.html" class="logo">SHREEBITU</a>
                
                <nav class="nav-links" id="mainNav">
                    <a href="${rootPath}index.html" class="nav-link">Home</a>
                    <a href="${rootPath}explore.html" class="nav-link">Explore</a>
                    <a href="${rootPath}about.html" class="nav-link">About</a>
                    <a href="${rootPath}contact.html" class="nav-link">Contact</a>
                </nav>

                <div id="authButtons" class="auth-group">
                    <a href="${rootPath}login.html" class="btn-outline">Login</a>
                    <a href="${rootPath}signup.html" class="btn-primary-sm">Sign Up</a>
                </div>
            </div>
        </header>

        <style>
            .main-header {
                position: sticky;
                top: 0;
                z-index: 1000;
                background: rgba(3, 7, 18, 0.6);
                backdrop-filter: blur(20px);
                border-bottom: 1px solid rgba(255, 255, 255, 0.05);
                height: 72px;
                display: flex;
                align-items: center;
            }
            .nav-container {
                max-width: 1200px;
                width: 100%;
                margin: 0 auto;
                padding: 0 24px;
                display: flex;
                align-items: center;
                justify-content: space-between;
            }
            .logo {
                font-size: 24px;
                font-weight: 900;
                letter-spacing: 2px;
                background: linear-gradient(135deg, #6366f1, #a855f7);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                text-decoration: none;
            }
            .nav-links {
                display: flex;
                gap: 32px;
            }
            .nav-link {
                color: #94a3b8;
                text-decoration: none;
                font-size: 14px;
                font-weight: 500;
                transition: all 0.3s ease;
            }
            .nav-link:hover {
                color: #f8fafc;
            }
            .auth-group {
                display: flex;
                gap: 16px;
                align-items: center;
            }
            .btn-outline {
                color: #f8fafc;
                text-decoration: none;
                font-size: 14px;
                font-weight: 500;
                padding: 8px 18px;
                border-radius: 10px;
                border: 1px solid rgba(255, 255, 255, 0.1);
                transition: all 0.3s ease;
            }
            .btn-outline:hover {
                background: rgba(255, 255, 255, 0.05);
            }
            .btn-primary-sm {
                background: linear-gradient(135deg, #6366f1, #a855f7);
                color: white;
                text-decoration: none;
                font-size: 14px;
                font-weight: 600;
                padding: 8px 20px;
                border-radius: 10px;
                transition: all 0.3s ease;
            }
            .btn-primary-sm:hover {
                box-shadow: 0 8px 20px rgba(99, 102, 241, 0.3);
                transform: translateY(-1px);
            }
            @media (max-width: 768px) {
                .nav-links { display: none; }
            }
        </style>
        `;

        // Run auth check
        setTimeout(() => {
            fetch('/api/authcheck')
                .then(r => r.ok ? r.json() : { authenticated: false })
                .then(data => {
                    if (data.authenticated) {
                        const nav = this.querySelector('#mainNav');
                        if (nav) nav.innerHTML += `<a href="${rootPath}dashboard.html" class="nav-link" style="color:#6366f1">Dashboard</a>`;
                        const btns = this.querySelector('#authButtons');
                        if (btns) btns.innerHTML = `<a href="${rootPath}upload.html" class="btn-primary-sm">Upload File</a>`;
                    }
                })
                .catch(() => {});
        }, 100);
    }
}

customElements.define('global-navbar', GlobalNavbar);
