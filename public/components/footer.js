class GlobalFooter extends HTMLElement {
    constructor() {
        super();
    }

    connectedCallback() {
        const rootPath = window.location.protocol === 'file:' ? (window.location.pathname.split('/').length - 2 > 0 ? '../' : './') : '/';

        this.innerHTML = `
        <footer class="main-footer">
            <div class="footer-container">
                <a href="${rootPath}index.html" class="footer-brand">SHREEBITU</a>
                
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
                padding: 40px 0 30px;
                margin-top: 60px;
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
                font-size: 18px;
                font-weight: 800;
                letter-spacing: 2px;
                color: #fff;
                text-decoration: none;
                margin-bottom: 24px;
                background: linear-gradient(135deg, #6366f1, #a855f7);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
            }
            .footer-links {
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
                gap: 20px;
                margin-bottom: 24px;
            }
            .footer-links a {
                color: #94a3b8;
                text-decoration: none;
                font-size: 13px;
                font-weight: 500;
                transition: color 0.3s ease;
            }
            .footer-links a:hover {
                color: #6366f1;
            }
            .footer-divider {
                width: 40px;
                height: 2px;
                background: linear-gradient(to right, #6366f1, #a855f7);
                margin-bottom: 24px;
                border-radius: 2px;
                opacity: 0.2;
            }
            .copyright {
                color: #f8fafc;
                font-size: 13px;
                font-weight: 600;
                margin-bottom: 8px;
            }
            .tagline {
                color: #64748b;
                font-size: 11px;
                font-weight: 400;
                letter-spacing: 0.05em;
            }
            @media (max-width: 768px) {
                .footer-links { gap: 16px; flex-direction: column; }
                .main-footer { margin-top: 40px; padding: 30px 0; }
            }
        </style>
        `;
    }
}

customElements.define('global-footer', GlobalFooter);
