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
        const card = document.createElement('div');
        card.className = "glass-panel file-card searchable-card";
        card.dataset.category = item.category;

        // Determine fallback icon based on category
        let fallbackIcon = "📄";
        if (item.category === 'apk') fallbackIcon = "📱";
        else if (item.category === 'windows') fallbackIcon = "🪟";
        else if (item.category === 'linux') fallbackIcon = "🐧";
        else if (item.category === 'ppt') fallbackIcon = "📊";
        else if (item.category === 'zip') fallbackIcon = "📦";
        else if (item.category === 'pdf') fallbackIcon = "📄";
        else if (item.category === 'opensource') fallbackIcon = "🐙";

        // Logo Logic: Use image if provided, else emoji fallback
        const logoHtml = item.image && item.image !== "" ? 
            `<img src="${basePath + item.image}" alt="${item.title}" class="card-logo" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
             <div class="category-icon fallback-icon" style="display:none;">${fallbackIcon}</div>` : 
            `<div class="category-icon">${fallbackIcon}</div>`;

        // Handle link depth
        const absoluteLink = item.link.startsWith('http') ? item.link : basePath + item.link;

        card.innerHTML = `
            <div class="file-info">
                <div class="logo-wrapper">
                    ${logoHtml}
                </div>
                <div style="overflow: hidden; flex: 1;">
                    <div class="file-badge">${item.category.toUpperCase()}</div>
                    <h3 class="card-title" style="margin-top: 8px; font-size: 1.1rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="${item.title}">${item.title}</h3>
                </div>
            </div>
            
            <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 20px; line-clamp: 2; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; height: 40px;">
                ${item.description}
            </p>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <div class="file-size-badge">
                    <span>📦 ${item.size || 'N/A'}</span>
                </div>
                <div class="file-size-badge">
                    <span>📅 ${item.date || 'Update'}</span>
                </div>
            </div>

            <a href="${absoluteLink}" class="btn-primary" style="width: 100%; justify-content: center; font-size: 14px; text-decoration: none;">Download Now</a>
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
