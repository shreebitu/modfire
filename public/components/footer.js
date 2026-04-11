class GlobalFooter extends HTMLElement {
    constructor() {
        super();
    }

    connectedCallback() {
        const rootPath = window.location.protocol === 'file:' ? (window.location.pathname.split('/').length - 2 > 0 ? '../' : './') : '/';

        this.innerHTML = `
        <footer class="border-t border-white/5 py-10 text-center text-gray-500 mt-auto">
            <div class="max-w-6xl mx-auto px-6">
                <div class="flex flex-wrap justify-center gap-6 text-sm mb-6">
                    <a href="${rootPath}index.html" class="hover:text-indigo-400 transition-colors duration-300">Home</a>
                    <a href="${rootPath}upload.html" class="hover:text-indigo-400 transition-colors duration-300">Upload</a>
                    <a href="${rootPath}explore.html" class="hover:text-indigo-400 transition-colors duration-300">Explore</a>
                    <a href="${rootPath}about.html" class="hover:text-indigo-400 transition-colors duration-300">About</a>
                    <a href="${rootPath}contact.html" class="hover:text-indigo-400 transition-colors duration-300">Contact</a>
                    <a href="${rootPath}privacy.html" class="hover:text-indigo-400 transition-colors duration-300">Privacy Policy</a>
                </div>
                <p>© 2026 ShreeBitu.in — All rights reserved.</p>
                <p class="mt-2 text-sm">Built with passion and continuous learning.</p>
            </div>
        </footer>
        `;
    }
}

customElements.define('global-footer', GlobalFooter);
