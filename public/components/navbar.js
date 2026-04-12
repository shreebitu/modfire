class GlobalNavbar extends HTMLElement {
    constructor() {
        super();
    }

    connectedCallback() {
        const subfolders = ['windows', 'apk', 'linux', 'presentation', 'zip', 'pdf', 'opensource', 'apk'];
        const isSubfolder = subfolders.some(folder => window.location.pathname.toLowerCase().includes('/' + folder + '/') || window.location.pathname.toLowerCase().includes('\\' + folder + '\\'));
        const depth = isSubfolder ? 1 : 0;
        const rootPath = depth > 0 ? '../'.repeat(depth) : './';

        this.innerHTML = `
        <header class="main-header">
            <div class="nav-container">
                <a href="${rootPath}index.html" class="logo">
                    <img src="${rootPath}assets/images/logo.png" alt="Logo" class="nav-icon" onerror="this.style.display='none'">
                    <span>SHREEBITU</span>
                </a>
                
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
                <button class="mobile-menu-close" id="mobileMenuClose" aria-label="Close menu">&times;</button>
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
            <div class="mobile-overlay" id="mobileOverlay"></div>
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
                max-width: 1400px;
                width: 100%;
                margin: 0 auto;
                padding: 0 24px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                position: relative;
            }
            .logo {
                display: flex;
                align-items: center;
                gap: 12px;
                text-decoration: none;
                transition: transform 0.3s ease;
                z-index: 1002;
            }
            .logo:hover {
                transform: scale(1.02);
            }
            .nav-icon {
                width: 42px;
                height: 42px;
                object-fit: contain;
                filter: drop-shadow(0 0 15px rgba(99, 102, 241, 0.4));
                border-radius: 10px;
            }
            .logo span {
                font-size: 22px;
                font-weight: 900;
                letter-spacing: 2px;
                background: linear-gradient(135deg, #6366f1, #a855f7);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                display: inline-block;
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

            /* Mobile Overlay */
            .mobile-overlay {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 998;
                backdrop-filter: blur(2px);
                opacity: 0;
                transition: opacity 0.3s ease;
            }
            .mobile-overlay.active {
                display: block;
                opacity: 1;
            }

            /* Mobile Menu — slides in from LEFT using transform (no layout glitch) */
            .mobile-menu {
                position: fixed;
                top: 0;
                left: 0;
                width: min(300px, 85vw);
                height: 100vh;
                height: 100dvh;
                background: rgba(3, 7, 18, 0.98);
                backdrop-filter: blur(24px);
                border-right: 1px solid rgba(255, 255, 255, 0.08);
                transform: translateX(-100%);
                transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1);
                will-change: transform;
                display: flex;
                flex-direction: column;
                align-items: flex-start;
                justify-content: flex-start;
                padding: 100px 32px 40px;
                z-index: 999;
                overflow-y: auto;
            }
            .mobile-menu.active { transform: translateX(0); }
            .mobile-menu-close {
                position: absolute;
                top: 20px;
                right: 20px;
                background: rgba(255,255,255,0.08);
                border: 1px solid rgba(255,255,255,0.1);
                color: #fff;
                font-size: 22px;
                width: 40px;
                height: 40px;
                border-radius: 50%;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: background 0.2s ease;
                line-height: 1;
            }
            .mobile-menu-close:hover { background: rgba(99,102,241,0.3); }
            .mobile-nav {
                display: flex;
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
                width: 100%;
            }
            .mobile-link {
                color: #cbd5e1;
                text-decoration: none;
                font-size: 18px;
                font-weight: 600;
                padding: 12px 0;
                width: 100%;
                border-bottom: 1px solid rgba(255,255,255,0.05);
                transition: color 0.2s ease, padding-left 0.2s ease;
            }
            .mobile-link:hover { color: #6366f1; padding-left: 8px; }
            .mobile-auth {
                display: flex;
                flex-direction: column;
                gap: 12px;
                width: 100%;
                margin-top: 24px;
                padding-top: 16px;
            }
            .mobile-auth .btn-outline,
            .mobile-auth .btn-primary-sm {
                width: 100%;
                text-align: center;
                display: block;
            }

            @media (max-width: 992px) {
                .nav-links, .auth-group { display: none; }
                .mobile-toggle { display: flex; }
            }

            /* Hamburger → X animation */
            .mobile-toggle.active span:nth-child(1) { transform: translateY(8px) rotate(45deg); }
            .mobile-toggle.active span:nth-child(2) { opacity: 0; transform: scaleX(0); }
            .mobile-toggle.active span:nth-child(3) { transform: translateY(-8px) rotate(-45deg); }
        </style>
        `;

        // Toggle logic
        const toggle = this.querySelector('#mobileToggle');
        const menu = this.querySelector('#mobileMenu');
        const overlay = this.querySelector('#mobileOverlay');
        const closeBtn = this.querySelector('#mobileMenuClose');

        const openMenu = () => {
            toggle.classList.add('active');
            menu.classList.add('active');
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        };

        const closeMenu = () => {
            toggle.classList.remove('active');
            menu.classList.remove('active');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        };

        toggle.addEventListener('click', () => {
            menu.classList.contains('active') ? closeMenu() : openMenu();
        });

        if (closeBtn) closeBtn.addEventListener('click', closeMenu);
        if (overlay) overlay.addEventListener('click', closeMenu);

        // Close on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeMenu();
        });

        // Close menu on nav link click (mobile)
        this.querySelectorAll('.mobile-link').forEach(link => {
            link.addEventListener('click', closeMenu);
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
