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

                <button class="mobile-toggle" id="mobileToggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
            <div class="mobile-menu" id="mobileMenu">
                <nav class="mobile-nav">
                    <a href="${rootPath}index.html" class="mobile-link">Home</a>
                    <a href="${rootPath}explore.html" class="mobile-link">Explore</a>
                    <a href="${rootPath}about.html" class="mobile-link">About</a>
                    <a href="${rootPath}contact.html" class="mobile-link">Contact</a>
                    <div class="mobile-auth" id="mobileAuth">
                        <a href="${rootPath}login.html" class="btn-outline">Login</a>
                        <a href="${rootPath}signup.html" class="btn-primary-sm">Sign Up</a>
                    </div>
                </nav>
            </div>
        </header>

        <style>
            .main-header {
                position: sticky;
                top: 0;
                z-index: 1000;
                background: rgba(3, 7, 18, 0.7);
                backdrop-filter: blur(24px);
                border-bottom: 1px solid rgba(255, 255, 255, 0.05);
                height: 80px;
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
                position: relative;
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
            .nav-links { display: flex; gap: 32px; }
            .nav-link {
                color: #94a3b8;
                text-decoration: none;
                font-size: 14px;
                font-weight: 500;
                transition: all 0.3s ease;
                position: relative;
            }
            .nav-link::after {
                content: '';
                position: absolute;
                bottom: -4px;
                left: 0;
                width: 0;
                height: 2px;
                background: #6366f1;
                transition: width 0.3s ease;
            }
            .nav-link:hover { color: #fff; }
            .nav-link:hover::after { width: 100%; }
            .auth-group { display: flex; gap: 16px; align-items: center; }
            .btn-outline {
                color: #f8fafc;
                text-decoration: none;
                font-size: 14px;
                font-weight: 500;
                padding: 10px 22px;
                border-radius: 12px;
                border: 1px solid rgba(255, 255, 255, 0.1);
                transition: all 0.3s ease;
            }
            .btn-outline:hover { background: rgba(255, 255, 255, 0.05); border-color: #6366f1; }
            .btn-primary-sm {
                background: linear-gradient(135deg, #6366f1, #a855f7);
                color: white;
                text-decoration: none;
                font-size: 14px;
                font-weight: 600;
                padding: 10px 24px;
                border-radius: 12px;
                transition: all 0.3s ease;
            }
            .btn-primary-sm:hover { box-shadow: 0 8px 20px rgba(99, 102, 241, 0.4); transform: translateY(-2px); }
            
            /* Mobile Toggle */
            .mobile-toggle {
                display: none;
                flex-direction: column;
                gap: 6px;
                background: none;
                border: none;
                cursor: pointer;
                padding: 4px;
                z-index: 1001;
            }
            .mobile-toggle span {
                width: 24px;
                height: 2px;
                background: #fff;
                transition: all 0.3s ease;
            }

            /* Mobile Menu */
            .mobile-menu {
                position: fixed;
                top: 0;
                right: -100%;
                width: 100%;
                height: 100vh;
                background: rgba(3, 7, 18, 0.98);
                backdrop-filter: blur(20px);
                transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 999;
            }
            .mobile-menu.active { right: 0; }
            .mobile-nav {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 32px;
                width: 100%;
            }
            .mobile-link {
                color: #fff;
                text-decoration: none;
                font-size: 24px;
                font-weight: 600;
                transition: color 0.3s ease;
            }
            .mobile-link:hover { color: #6366f1; }
            .mobile-auth { display: flex; flex-direction: column; gap: 16px; width: 200px; text-align: center; margin-top: 20px; }

            @media (max-width: 992px) {
                .nav-links, .auth-group { display: none; }
                .mobile-toggle { display: flex; }
            }

            /* Animation when active */
            .mobile-toggle.active span:nth-child(1) { transform: translateY(8px) rotate(45deg); }
            .mobile-toggle.active span:nth-child(2) { opacity: 0; }
            .mobile-toggle.active span:nth-child(3) { transform: translateY(-8px) rotate(-45deg); }
        </style>
        `;

        // Toggle logic
        const toggle = this.querySelector('#mobileToggle');
        const menu = this.querySelector('#mobileMenu');
        toggle.addEventListener('click', () => {
            toggle.classList.toggle('active');
            menu.classList.toggle('active');
            document.body.style.overflow = menu.classList.contains('active') ? 'hidden' : '';
        });

        // Run auth check
        setTimeout(() => {
            fetch('/api/authcheck')
                .then(r => r.ok ? r.json() : { authenticated: false })
                .then(data => {
                    if (data.authenticated) {
                        const nav = this.querySelector('#mainNav');
                        const mobileNav = this.querySelector('.mobile-nav');
                        const dashboardLink = `<a href="${rootPath}dashboard.html" class="nav-link" style="color:#6366f1">Dashboard</a>`;
                        const mobileDashboardLink = `<a href="${rootPath}dashboard.html" class="mobile-link" style="color:#6366f1">Dashboard</a>`;
                        
                        if (nav) nav.innerHTML += dashboardLink;
                        if (mobileNav) mobileNav.insertAdjacentHTML('afterbegin', mobileDashboardLink);

                        const btns = this.querySelector('#authButtons');
                        const mobileAuth = this.querySelector('#mobileAuth');
                        const uploadBtn = `<a href="${rootPath}upload.html" class="btn-primary-sm">Upload File</a>`;
                        
                        if (btns) btns.innerHTML = uploadBtn;
                        if (mobileAuth) mobileAuth.innerHTML = uploadBtn;
                    }
                })
                .catch(() => {});
        }, 100);
    }
}

customElements.define('global-navbar', GlobalNavbar);
