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
        <!-- MOBILE TOP HEADER (Hidden on Desktop) -->
        <header class="mobile-only-header main-header lg:hidden">
            <div class="nav-container">
                <a href="${rootPath}index.html" class="logo">
                    <img src="${rootPath}assets/images/logo.png" alt="Logo" class="nav-icon" onerror="this.style.display='none'">
                    <span>SHREEBITU</span>
                </a>
                
                <button class="mobile-toggle" id="mobileToggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </header>

        <!-- DESKTOP SIDEBAR (Hidden on Mobile) -->
        <aside class="desktop-sidebar hidden lg:flex fixed left-0 top-0 bottom-0 w-[280px] bg-[#111113] border-r border-white/5 shadow-2xl flex-col z-[1000] overflow-y-auto no-scrollbar font-sans text-gray-200">
            <!-- Mac Dots -->
            <div class="flex gap-2 mb-8 px-8 pt-8">
                <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                <div class="w-3 h-3 bg-green-500 rounded-full"></div>
            </div>

            <!-- Logo -->
            <a href="${rootPath}index.html" class="flex items-center gap-3 mb-10 px-8 hover:scale-105 transition-transform" style="text-decoration:none">
                <img src="${rootPath}assets/images/logo.png" alt="Logo" class="w-8 h-8 object-contain filter drop-shadow-[0_0_10px_rgba(99,102,241,0.5)]" onerror="this.style.display='none'">
                <span class="font-black text-[18px] tracking-[0.1em] bg-clip-text text-transparent bg-gradient-to-br from-indigo-400 to-purple-500" style="-webkit-background-clip: text;-webkit-text-fill-color: transparent;">SHREEBITU</span>
            </a>

            <!-- Main Menu -->
            <div class="mb-3 px-8">
                <h4 class="text-[10px] font-bold text-gray-500/80 tracking-[0.15em] uppercase">Main Menu</h4>
            </div>
            <nav class="space-y-1 mb-8 px-5" id="mainNav">
                <a href="${rootPath}index.html" class="flex items-center gap-4 text-gray-400 hover:text-white px-4 py-3 rounded-[20px] transition group hover:bg-white/5" style="text-decoration:none">
                    <svg class="w-5 h-5 opacity-70 group-hover:opacity-100 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    <span class="text-sm font-medium">Home</span>
                </a>
                <a href="${rootPath}explore.html" class="flex items-center gap-4 text-gray-400 hover:text-white px-4 py-3 rounded-[20px] transition group hover:bg-white/5" style="text-decoration:none">
                    <svg class="w-5 h-5 opacity-70 group-hover:opacity-100 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <span class="text-sm font-medium">Explore</span>
                </a>
                <a href="${rootPath}about.html" class="flex items-center gap-4 text-gray-400 hover:text-white px-4 py-3 rounded-[20px] transition group hover:bg-white/5" style="text-decoration:none">
                    <svg class="w-5 h-5 opacity-70 group-hover:opacity-100 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="text-sm font-medium">About</span>
                </a>
                <a href="${rootPath}contact.html" class="flex items-center gap-4 text-gray-400 hover:text-white px-4 py-3 rounded-[20px] transition group hover:bg-white/5" style="text-decoration:none">
                    <svg class="w-5 h-5 opacity-70 group-hover:opacity-100 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    <span class="text-sm font-medium">Contact</span>
                </a>
            </nav>

        </aside>

        <!-- MOBILE MENU OVERLAY -->
        <div class="mobile-menu" id="mobileMenu">
            <button class="mobile-menu-close" id="mobileMenuClose" aria-label="Close menu">&times;</button>
            <nav class="mobile-nav">
                <a href="${rootPath}index.html" class="mobile-link">Home</a>
                <a href="${rootPath}explore.html" class="mobile-link">Explore</a>
                <a href="${rootPath}about.html" class="mobile-link">About</a>
                <a href="${rootPath}contact.html" class="mobile-link">Contact</a>

            </nav>
        </div>
        <div class="mobile-overlay" id="mobileOverlay"></div>

        <style>
            /* Ensure the desktop sidebar pushes content over! */
            @media (min-width: 993px) {
                body {
                    padding-left: 280px !important;
                }
                .mobile-only-header {
                    display: none !important;
                }
                .lg\\:hidden { display: none !important; }
                .lg\\:flex { display: flex !important; }
            }

            @media (max-width: 992px) {
                .desktop-sidebar {
                    display: none !important;
                }
            }

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
                width: 100%;
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

            .no-scrollbar::-webkit-scrollbar { display: none; }
            .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
            
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
                display: flex;
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

            /* Mobile Menu */
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
                display: flex;
                flex-direction: column;
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
            }
            .mobile-menu-close:hover { background: rgba(99,102,241,0.3); }
            .mobile-nav {
                display: flex;
                flex-direction: column;
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

        if (toggle) {
            toggle.addEventListener('click', () => {
                menu.classList.contains('active') ? closeMenu() : openMenu();
            });
        }

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
    }
}

customElements.define('global-navbar', GlobalNavbar);
