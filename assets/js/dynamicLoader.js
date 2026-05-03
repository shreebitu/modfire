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
        card.className = "group block bg-white p-2 rounded-2xl border border-gray-100 hover:border-blue-200 hover:bg-gray-50 transition-all duration-200 searchable-card";
        card.dataset.category = item.category;

        // Map categories to beautiful icons and colors (Fallback)
        let iconCode = '📁';
        let iconBg = 'bg-gray-100'; let iconTxt = 'text-gray-600';
        
        const cat = item.category.toLowerCase();
        if(cat === 'apk' || cat === 'app') { iconCode = '📱'; iconBg = 'bg-green-100'; iconTxt = 'text-green-600'; }
        else if(cat === 'linux') { iconCode = '🐧'; iconBg = 'bg-orange-100'; iconTxt = 'text-orange-600'; }
        else if(cat === 'windows') { iconCode = '🪟'; iconBg = 'bg-blue-100'; iconTxt = 'text-blue-600'; }
        else if(cat === 'pdf') { iconCode = '📄'; iconBg = 'bg-red-100'; iconTxt = 'text-red-600'; }
        else if(cat === 'zip' || cat === 'archives') { iconCode = '📦'; iconBg = 'bg-yellow-100'; iconTxt = 'text-yellow-700'; }
        else if(cat === 'ppt' || cat === 'presentation') { iconCode = '📊'; iconBg = 'bg-purple-100'; iconTxt = 'text-purple-600'; }
        else if(cat === 'opensource' || cat === 'github') { iconCode = '🐙'; iconBg = 'bg-slate-100'; iconTxt = 'text-slate-800'; }
        
        // Use image if available, else fallback to iconCode
        let iconContent = `<span class="${iconTxt} text-3xl font-bold">${iconCode}</span>`;
        if (item.image) {
            const imagePath = item.image.startsWith('http') ? item.image : basePath + item.image;
            iconContent = `<img src="${imagePath}" alt="${item.title}" class="w-full h-full object-cover transition-transform duration-300">`;
        }

        let subtext = item.category.toUpperCase();
        if (item.size) subtext = item.size;
        else if (item.stars) subtext = `★ ${item.stars}`;

        card.innerHTML = `
            <div class="relative aspect-square w-full rounded-[22%] overflow-hidden mb-2 bg-gray-50 flex items-center justify-center border border-gray-200 group-hover:border-blue-400/50 group-hover:shadow-md transition-all duration-300">
                ${iconContent}
            </div>
            <div class="px-0.5">
                <h3 class="font-semibold text-[13px] text-gray-900 leading-[1.2] mb-0.5 overflow-hidden group-hover:text-blue-600 transition-colors" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; min-height: 2.4em;">
                    ${item.title}
                </h3>
                <div class="flex items-center gap-1.5 opacity-70">
                    <p class="text-[10px] text-gray-500 font-medium truncate">${subtext}</p>
                </div>
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
