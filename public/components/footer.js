class GlobalFooter extends HTMLElement {
    constructor() {
        super();
    }

    connectedCallback() {
        const subfolders = ['windows', 'apk', 'linux', 'presentation', 'zip', 'pdf', 'opensource', 'apk'];
        const isSubfolder = subfolders.some(folder => window.location.pathname.toLowerCase().includes('/' + folder + '/') || window.location.pathname.toLowerCase().includes('\\' + folder + '\\'));
        const depth = isSubfolder ? 1 : 0;
        const rootPath = depth > 0 ? '../'.repeat(depth) : './';

        this.innerHTML = `
        <footer class="main-footer">
            <div class="footer-container">
                <div class="footer-links">
                    <a href="${rootPath}index.html">Home</a>
                    <a href="${rootPath}explore.html">Explore</a>
                    <a href="${rootPath}about.html">About</a>
                    <a href="${rootPath}contact.html">Contact</a>
                    <a href="${rootPath}privacy.html">Privacy</a>
                </div>
                <div class="footer-divider"></div>
                <div class="footer-info">
                    <p class="copyright">&copy; 2026 SHREEBITU</p>
                    <p class="tagline">Next-Gen Sharing &bull; Community Powered &bull; Professional Experience</p>
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
                max-width: 1400px;
                margin: 0 auto;
                padding: 0 24px;
                display: flex;
                flex-direction: column;
                align-items: center;
                text-align: center;
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
            .main-footer *, .main-footer *:before, .main-footer *:after {
                box-sizing: border-box;
            }
            @media (max-width: 768px) {
                .footer-links { 
                    display: flex;
                    flex-direction: row;
                    flex-wrap: nowrap;
                    overflow-x: auto;
                    max-width: 100%;
                    gap: 20px;
                    padding: 4px 24px 12px;
                    justify-content: flex-start;
                    scrollbar-width: none; 
                    -ms-overflow-style: none;
                    -webkit-overflow-scrolling: touch;
                }
                .footer-links::-webkit-scrollbar {
                    display: none;
                }
                .main-footer { margin-top: 40px; padding: 40px 0; overflow-x: hidden; }
                .copyright, .tagline { padding: 0 24px; text-align: center; }
            }
        </style>
        `;
    }
}

customElements.define('global-footer', GlobalFooter);
