class GlobalFooter extends HTMLElement {
    constructor() {
        super();
    }

    connectedCallback() {
        const rootPath = window.location.protocol === 'file:' ? (window.location.pathname.split('/').length - 2 > 0 ? '../' : './') : '/';

        this.innerHTML = `
        <footer class="main-footer">
            <div class="footer-container">
                <div class="footer-links">
                    <a href="${rootPath}index.html">Home</a>
                    <a href="${rootPath}upload.html">Upload</a>
                    <a href="${rootPath}explore.html">Explore</a>
                    <a href="${rootPath}about.html">About</a>
                    <a href="${rootPath}contact.html">Contact</a>
                    <a href="${rootPath}privacy.html">Privacy</a>
                </div>
                <div class="footer-info">
                    <p>© 2026 SHREEBITU — Premium Cloud Solutions</p>
                    <p class="tagline">Next-Gen Storage, Community Sharing, Unlimited Potential.</p>
                </div>
            </div>
        </footer>

        <style>
            .main-footer {
                padding: 60px 0;
                margin-top: 80px;
                border-top: 1px solid rgba(255, 255, 255, 0.05);
                background: rgba(3, 7, 18, 0.4);
            }
            .footer-container {
                max-width: 1200px;
                margin: 0 auto;
                padding: 0 24px;
                text-align: center;
            }
            .footer-links {
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
                gap: 24px;
                margin-bottom: 30px;
            }
            .footer-links a {
                color: #94a3b8;
                text-decoration: none;
                font-size: 14px;
                transition: color 0.3s ease;
            }
            .footer-links a:hover {
                color: #6366f1;
            }
            .footer-info p {
                color: #64748b;
                font-size: 14px;
                margin-bottom: 8px;
            }
            .tagline {
                font-size: 12px !important;
                opacity: 0.6;
            }
        </style>
        `;
    }
}

customElements.define('global-footer', GlobalFooter);
