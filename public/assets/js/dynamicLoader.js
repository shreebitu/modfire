/**
 * ============================================================
 *   DYNAMIC LOADER — ShreeBitu.in
 * ============================================================
 * This script handles fetching JSON data from the /data/ folder
 * and rendering premium cards across the platform.
 */

const DynamicLoader = {
    // Determine the base path based on location (depth handling)
    getBasePath() {
        const path = window.location.pathname;
        const depth = (path.split('/').length - (path.endsWith('/') ? 2 : 1));
        
        // If it's a subfolder (depth > 1 for /subdir/page.html, or depth > 0 depending on host)
        // Simplified detection: if we are in a subfolder like /apk/, we need ../
        const isSubfolder = window.location.pathname.split('/').filter(p => p).length > 1;
        
        // However, a more robust way for static sites:
        if (window.location.pathname.includes('/apk/') || 
            window.location.pathname.includes('/windows/') || 
            window.location.pathname.includes('/linux/') || 
            window.location.pathname.includes('/presentation/') || 
            window.location.pathname.includes('/zip/') || 
            window.location.pathname.includes('/pdf/') || 
            window.location.pathname.includes('/opensource/')) {
            return '../';
        }
        return '';
    },

    // Categories and their respective JSON sources
    categories: {
        apk: 'data/apk.json',
        windows: 'data/windows.json',
        linux: 'data/linux.json',
        ppt: 'data/ppt.json',
        zip: 'data/zip.json',
        pdf: 'data/pdf.json',
        opensource: 'data/opensource.json'
    },

    /**
     * Fetch items from a single category
     */
    async fetchCategory(category) {
        try {
            const basePath = this.getBasePath();
            const response = await fetch(basePath + this.categories[category]);
            if (!response.ok) return [];
            const data = await response.json();
            return data.map(item => ({ ...item, category }));
        } catch (error) {
            console.error(`Error loading category: ${category}`, error);
            return [];
        }
    },

    /**
     * Fetch all items from all categories
     */
    async fetchAll() {
        const promises = Object.keys(this.categories).map(cat => this.fetchCategory(cat));
        const results = await Promise.all(promises);
        return results.flat();
    },

    /**
     * Create a premium HTML card for an item
     */
    createCard(item) {
        const basePath = this.getBasePath();
        const card = document.createElement('a');
        
        // Handle link depth
        const absoluteLink = item.link && item.link.startsWith('http') ? item.link : (item.link ? basePath + item.link : '#');
        
        card.href = absoluteLink;
        card.className = "app-card glow-hover block bg-white p-5 rounded-[20px] border border-gray-100 cursor-pointer shadow-sm relative group searchable-card";
        card.dataset.category = item.category;

        // Map categories to beautiful icons and colors
        let iconCode = '📁';
        let iconBg = 'bg-gray-50'; let iconTxt = 'text-gray-600'; let borderCol = 'border-gray-100';
        
        const cat = item.category.toLowerCase();
        if(cat === 'apk' || cat === 'app') { iconCode = '📱'; iconBg = 'bg-green-50'; iconTxt = 'text-green-500'; borderCol = 'border-green-100'; }
        else if(cat === 'linux') { iconCode = '🐧'; iconBg = 'bg-orange-50'; iconTxt = 'text-orange-500'; borderCol = 'border-orange-100'; }
        else if(cat === 'windows') { iconCode = '🪟'; iconBg = 'bg-blue-50'; iconTxt = 'text-blue-500'; borderCol = 'border-blue-100'; }
        else if(cat === 'pdf') { iconCode = '📄'; iconBg = 'bg-red-50'; iconTxt = 'text-red-500'; borderCol = 'border-red-100'; }
        else if(cat === 'zip' || cat === 'archives') { iconCode = '📦'; iconBg = 'bg-yellow-50'; iconTxt = 'text-yellow-600'; borderCol = 'border-yellow-100'; }
        else if(cat === 'ppt' || cat === 'presentation') { iconCode = '📊'; iconBg = 'bg-purple-50'; iconTxt = 'text-purple-500'; borderCol = 'border-purple-100'; }
        else if(cat === 'opensource' || cat === 'github') { iconCode = '🐙'; iconBg = 'bg-slate-50'; iconTxt = 'text-slate-800'; borderCol = 'border-slate-100'; }
        
        let sizeDisplay = item.size ? item.size : (item.stars ? `⭐ ${item.stars}` : 'View details');

        card.innerHTML = `
            <div class="flex justify-between items-start mb-4">
                <div class="w-12 h-12 rounded-xl ${iconBg} flex items-center justify-center ${iconTxt} text-2xl shadow-sm border ${borderCol} transition-colors">
                    ${iconCode}
                </div>
                <div class="w-7 h-7 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 group-hover:text-[#0066cc] group-hover:bg-[#0066cc]/10 transition z-10">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                </div>
            </div>
            <h3 class="font-[600] text-[15px] text-gray-900 leading-tight truncate">${item.title}</h3>
            <p class="text-[12px] text-gray-500 mb-4 mt-0.5 capitalize">${item.category}</p>
            <div class="flex items-center justify-between text-[12px] font-medium mt-auto">
                <span class="text-gray-400 flex items-center gap-1">${sizeDisplay}</span>
                <span class="text-[#0066cc] font-semibold opacity-0 translate-x-[-10px] group-hover:opacity-100 group-hover:translate-x-0 transition-all duration-300">Get it</span>
            </div>
        `;
        return card;
    },

    /**
     * Render items to a target container
     */
    render(items, containerId) {
        const container = document.getElementById(containerId);
        if (!container) return;

        container.innerHTML = '';
        if (items.length === 0) {
            container.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 40px; color: var(--text-secondary);">No items found in this section.</div>';
            return;
        }

        items.forEach(item => {
            container.appendChild(this.createCard(item));
        });
    },

    /**
     * Global Search Implementation
     */
    setupSearch(inputId, containerId) {
        const input = document.getElementById(inputId);
        if (!input) return;

        input.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase();
            const cards = document.querySelectorAll(`#${containerId} .searchable-card`);
            
            cards.forEach(card => {
                const text = card.textContent.toLowerCase();
                card.style.display = text.includes(query) ? '' : 'none';
            });
        });
    }
};

// Export for global use
window.DynamicLoader = DynamicLoader;
