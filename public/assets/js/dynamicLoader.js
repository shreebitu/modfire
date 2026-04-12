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
        let badgeColor = "rgba(99, 102, 241, 0.15)";
        let badgeText = "#6366f1";
        if (item.category === 'apk')       { fallbackIcon = "📱"; badgeColor = "rgba(34,197,94,0.15)"; badgeText = "#22c55e"; }
        else if (item.category === 'windows') { fallbackIcon = "🪟"; badgeColor = "rgba(59,130,246,0.15)"; badgeText = "#3b82f6"; }
        else if (item.category === 'linux')   { fallbackIcon = "🐧"; badgeColor = "rgba(251,191,36,0.15)"; badgeText = "#fbbf24"; }
        else if (item.category === 'ppt')     { fallbackIcon = "📊"; badgeColor = "rgba(168,85,247,0.15)"; badgeText = "#a855f7"; }
        else if (item.category === 'zip')     { fallbackIcon = "📦"; badgeColor = "rgba(234,179,8,0.15)"; badgeText = "#eab308"; }
        else if (item.category === 'pdf')     { fallbackIcon = "📄"; badgeColor = "rgba(239,68,68,0.15)"; badgeText = "#ef4444"; }
        else if (item.category === 'opensource') { fallbackIcon = "🐙"; badgeColor = "rgba(148,163,184,0.15)"; badgeText = "#94a3b8"; }

        // Logo Logic: Use image if provided, else emoji fallback
        const logoHtml = item.image && item.image !== "" ? 
            `<img src="${basePath + item.image}" alt="${item.title}" class="card-logo" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
             <div class="card-fallback-icon" style="display:none; font-size:28px; align-items:center; justify-content:center; width:100%; height:100%;">${fallbackIcon}</div>` : 
            `<div style="font-size:28px; display:flex; align-items:center; justify-content:center; width:100%; height:100%;">${fallbackIcon}</div>`;

        // Handle link depth
        const absoluteLink = item.link.startsWith('http') ? item.link : basePath + item.link;

        // Format date nicely
        let displayDate = item.date || '';
        if (displayDate) {
            try {
                displayDate = new Date(displayDate).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            } catch(e) {}
        }

        card.innerHTML = `
            <div class="fc-header">
                <div class="fc-logo-wrap">${logoHtml}</div>
                <div class="fc-title-block">
                    <span class="fc-badge" style="background:${badgeColor}; color:${badgeText};">${item.category.toUpperCase()}</span>
                    <h3 class="fc-title" title="${item.title}">${item.title}</h3>
                </div>
            </div>
            <p class="fc-desc">${item.description}</p>
            <div class="fc-meta">
                <span class="fc-meta-pill">📦 ${item.size || 'N/A'}</span>
                <span class="fc-meta-pill">🗓 ${displayDate || 'Latest'}</span>
            </div>
            <a href="${absoluteLink}" class="fc-btn">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Download Now
            </a>
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
