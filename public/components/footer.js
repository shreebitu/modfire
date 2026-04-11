class GlobalFooter extends HTMLElement {
    constructor() {
        super();
    }

    connectedCallback() {
        const rootPath = window.location.protocol === 'file:' ? (window.location.pathname.split('/').length - 2 > 0 ? '../' : './') : '/';

        this.innerHTML = `
        <footer class="main-footer">
            <div class="footer-container">
                <div class="footer-brand">
                    <div class="footer-logo">SB</div>
                    <div class="brand-name">SHREEBITU</div>
                </div>
                <div class="footer-links">
                    <a href="${rootPath}index.html">Home</a>
                    <a href="${rootPath}upload.html">Upload</a>
                    <a href="${rootPath}explore.html">Explore</a>
                    <a href="${rootPath}about.html">About</a>
                    <a href="${rootPath}contact.html">Contact</a>
                    <a href="${rootPath}privacy.html">Privacy</a>
                </div>
                <div class="footer-divider"></div>
                <div class="footer-info">
                    <p class="copyright">© 2026 SHREEBITU — Premium Cloud Storage Solutions</p>
                    <p class="tagline">Next-Gen Sharing • Community Powered • Professional Experience</p>
                </div>
            </div>
        </footer>

        <style>
            .main-footer {
                padding: 80px 0 40px;
                margin-top: 100px;
                border-top: 1px solid rgba(255, 255, 255, 0.05);
                background: linear-gradient(to bottom, transparent, rgba(3, 7, 18, 0.8));
            }
            .footer-container {
                max-width: 1200px;
                margin: 0 auto;
                padding: 0 24px;
                display: flex;
                flex-direction: column;
                align-items: center;
                text-align: center;
            }
            .footer-brand {
                display: flex;
                align-items: center;
                gap: 12px;
                margin-bottom: 40px;
            }
            .footer-logo {
                width: 32px;
                height: 32px;
                background: linear-gradient(135deg, #6366f1, #a855f7);
                border-radius: 8px;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-weight: 900;
                font-size: 10px;
            }
            .brand-name {
                font-size: 18px;
                font-weight: 800;
                letter-spacing: 2px;
                color: #fff;
            }
            .footer-links {
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
                gap: 32px;
                margin-bottom: 40px;
            }
            .footer-links a {
                color: #94a3b8;
                text-decoration: none;
                font-size: 14px;
                font-weight: 500;
                transition: color 0.3s ease;
            }
            .footer-links a:hover {
                color: #6366f1;
            }
            .footer-divider {
                width: 60px;
                height: 2px;
                background: linear-gradient(to right, #6366f1, #a855f7);
                margin-bottom: 40px;
                border-radius: 2px;
                opacity: 0.3;
            }
            .copyright {
                color: #f8fafc;
                font-size: 14px;
                font-weight: 600;
                margin-bottom: 12px;
            }
            .tagline {
                color: #64748b;
                font-size: 12px;
                font-weight: 400;
                letter-spacing: 0.05em;
            }
        </style>
        `;
    }
}

customElements.define('global-footer', GlobalFooter);
