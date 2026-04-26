/**
 * SHREEBITU MASTER MAINTENANCE TOOL (Complete Website Fix)
 */

const fs = require('fs');
const path = require('path');

function getFiles(dir, filesList = []) {
    const files = fs.readdirSync(dir);
    for (const file of files) {
        const name = dir + '/' + file;
        if (fs.statSync(name).isDirectory() && !name.match(/node_modules|\.git|scripts|assets|\.json/)) {
            getFiles(name, filesList);
        } else if (name.endsWith('.html')) {
            filesList.push(name);
        }
    }
    return filesList;
}

function runGlobalMaintenance() {
    console.log("--- Starting Global Maintenance ---");
    const allHtmls = getFiles('.');

    for (const file of allHtmls) {
        let content = fs.readFileSync(file, 'utf8');
        let original = content;

        content = polishContent(content, file);

        if (content !== original) {
            fs.writeFileSync(file, content, 'utf8');
            console.log(`[FIXED] ${file}`);
        }
    }
    console.log("--- Maintenance Complete ---");
}

function polishContent(content, file) {
    const normalizedPath = path.normalize(file).replace(/\\/g, '/');
    const depth = normalizedPath.split('/').filter(p => p !== '.' && p !== '').length - 1;
    const rp = depth === 0 ? '' : '../'.repeat(depth);

    content = content.replace(/â€/g, '—');
    content = content.replace(/â€"/g, '"');
    content = content.replace(/â€œ/g, '"');
    content = content.replace(/â€'/g, "'");
    content = content.replace(/â€™/g, "'");
    content = content.replace(/â€˜/g, "'");
    content = content.replace(/â€¢/g, '•');

    if (!content.includes('ca-pub-7668010130101569')) {
        content = content.replace('</head>', `    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-7668010130101569" crossorigin="anonymous"></script>\n</head>`);
    }

    // Fix header to match homepage style
    const oldHeader = /<header class="h-\[72px\] flex items-center justify-between px-4 lg:px-10 shrink-0 sticky top-0 bg-\[#f8f9fa\]\/90 backdrop-blur z-30 border-b border-transparent">[\s\S]*?<\/header>/;
    const newHeader = `<header class="h-[72px] flex items-center justify-between px-4 lg:px-10 shrink-0 sticky top-0 bg-white z-30 border-b border-gray-100 hidden md:block">
        
        <div class="flex items-center gap-2">
            <button id="mobileMenuBtn" class="md:hidden text-gray-500 hover:text-[#0066cc] focus:outline-none p-1.5 shrink-0 bg-gray-50 rounded-lg">
                <svg class="w-[22px] h-[22px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <a href="${rp}index.html" class="md:hidden font-bold tracking-tight text-[16px] text-[#0066cc]">ShreeBitu</a>
            
            <div id="desktopSearchWrap" class="hidden md:flex items-center relative">
                <svg class="w-4 h-4 text-gray-400 absolute left-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input type="text" id="searchInput" placeholder="Search..." class="bg-gray-50 text-[13px] text-gray-800 rounded-lg pl-9 pr-3 py-2 w-[140px] md:w-[180px] lg:w-[220px] outline-none focus:ring-2 focus:ring-[#0066cc]/30 focus:bg-white placeholder-gray-500 transition-all text-sm" />
            </div>
        </div>
        
        <div class="flex items-center gap-2 md:gap-3">
            <button id="searchToggleBtn" class="md:hidden text-gray-500 hover:text-[#0066cc] p-2 shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </button>
            
            <div id="searchContainer" class="hidden absolute top-[72px] left-0 right-0 z-[200] bg-white border-b border-gray-100 p-3">
                <div class="relative">
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" id="mobileSearchInput" placeholder="Search files, apps..." class="w-full bg-gray-50 text-[14px] text-gray-800 rounded-lg pl-9 pr-4 py-2.5 outline-none focus:ring-2 focus:ring-[#0066cc]/30 placeholder-gray-500" />
                </div>
                <div id="mobileSearchResults" class="mt-2 max-h-[60vh] overflow-y-auto"></div>
            </div>
            
            <div class="hidden lg:flex items-center gap-2">
                <a href="${rp}login.html" class="text-[13px] font-medium text-gray-600 hover:text-[#0066cc]">Log In</a>
                <a href="${rp}signup.html" class="bg-[#0066cc] text-white px-3 py-1.5 rounded-lg text-[12px] font-medium hover:bg-[#0055aa]">Sign Up</a>
            </div>
            
            <button id="searchToggleBtnDesktop" class="hidden md:block lg:hidden text-gray-500 hover:text-[#0066cc] p-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </button>
        </div>
    </header>`;

    content = content.replace(oldHeader, newHeader);

    // Also add hidden md:block to header if not present
    if (!content.includes('class="hidden md:block')) {
        content = content.replace(/<header class="h-\[72px\]/g, '<header class="hidden md:block h-[72px]');
    }

    content = content.replace(
        /<a href="([^"]+)" class="hidden md:block text-\[13px\] font-semibold text-gray-600 hover:text-gray-900 transition flex-shrink-0 px-1">Log In<\/a>/g,
        `<a href="$1" class="hidden lg:block text-[13px] font-semibold text-gray-600 hover:text-gray-900 transition flex-shrink-0 px-1">Log In</a>`
    );

    content = content.replace(
        /<a href="([^"]+)" class="hidden md:block bg-\[#0066cc\] text-white px-3 md:px-4 py-1\.5 md:py-2 rounded-\[8px\] text-\[12px\] md:text-\[13px\] font-semibold shadow-sm hover:bg-\[#005bb5\] transition">Sign Up<\/a>/g,
        `<a href="$1" class="hidden lg:block bg-[#0066cc] text-white px-3 md:px-4 py-1.5 md:py-2 rounded-[8px] text-[12px] md:text-[13px] font-semibold shadow-sm hover:bg-[#005bb5] transition">Sign Up</a>`
    );

    content = content.replace(
        /<a href="([^"]+)" class="hidden md:block text-\[12px\] md:text-\[13px\] font-semibold text-gray-600 hover:text-gray-900 transition flex-shrink-0 px-1">Log In<\/a>/g,
        `<a href="$1" class="hidden lg:block text-[12px] md:text-[13px] font-semibold text-gray-600 hover:text-gray-900 transition flex-shrink-0 px-1">Log In</a>`
    );

    content = content.replace(
        /<a href="([^"]+)" class="hidden md:block text-\[12px\] md:text-\[13px\] font-semibold text-gray-600 hover:text-gray-900 transition flex-shrink-0">Log In<\/a>/g,
        `<a href="$1" class="hidden lg:block text-[12px] md:text-[13px] font-semibold text-gray-600 hover:text-gray-900 transition flex-shrink-0">Log In</a>`
    );

    content = content.replace(
        /<a href="([^"]+)" class="hidden md:block bg-\[#0066cc\] text-white px-3 md:px-4 py-1\.5 md:py-2 rounded-\[8px\] text-\[12px\] md:text-\[13px\] font-semibold shadow-sm hover:bg-\[#005bb5\] transition flex-shrink-0">Sign Up<\/a>/g,
        `<a href="$1" class="hidden lg:block bg-[#0066cc] text-white px-3 md:px-4 py-1.5 md:py-2 rounded-[8px] text-[12px] md:text-[13px] font-semibold shadow-sm hover:bg-[#005bb5] transition flex-shrink-0">Sign Up</a>`
    );

    // Fix more Log In/Sign Up variations
    content = content.replace(
        /<a href="([^"]+)" class="text-\[12px\] md:text-\[13px\] font-semibold text-gray-600 hover:text-gray-900 transition flex-shrink-0 px-1">Log In<\/a>/g,
        `<a href="$1" class="hidden md:block text-[12px] md:text-[13px] font-semibold text-gray-600 hover:text-gray-900 transition flex-shrink-0 px-1">Log In</a>`
    );

    content = content.replace(
        /<a href="([^"]+)" class="text-\[12px\] md:text-\[13px\] font-semibold text-gray-600 hover:text-gray-900 transition flex-shrink-0">Log In<\/a>/g,
        `<a href="$1" class="hidden md:block text-[12px] md:text-[13px] font-semibold text-gray-600 hover:text-gray-900 transition flex-shrink-0">Log In</a>`
    );

    content = content.replace(
        /<a href="([^"]+)" class="bg-\[#0066cc\] text-white px-3 md:px-4 py-1\.5 md:py-2 rounded-\[8px\] text-\[12px\] md:text-\[13px\] font-semibold shadow-sm hover:bg-\[#005bb5\] transition flex-shrink-0">Sign Up<\/a>/g,
        `<a href="$1" class="hidden md:block bg-[#0066cc] text-white px-3 md:px-4 py-1.5 md:py-2 rounded-[8px] text-[12px] md:text-[13px] font-semibold shadow-sm hover:bg-[#005bb5] transition flex-shrink-0">Sign Up</a>`
    );

    const newMobileNav = `</main>

<script src="${rp}assets/js/dynamicLoader.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const searchToggle = document.getElementById('searchToggleBtn');
    const searchContainer = document.getElementById('searchContainer');
    const mobileSearchInput = document.getElementById('mobileSearchInput');
    const mobileSearchResults = document.getElementById('mobileSearchResults');
    const loader = window.DynamicLoader;
    
    if (searchToggle && searchContainer) {
        searchToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            searchContainer.classList.toggle('hidden');
            if (!searchContainer.classList.contains('hidden') && mobileSearchInput) {
                mobileSearchInput.focus();
            }
        });
        
        document.addEventListener('click', function(e) {
            if (!searchContainer.contains(e.target) && e.target !== searchToggle) {
                searchContainer.classList.add('hidden');
            }
        });
        
        if (mobileSearchInput && loader) {
            mobileSearchInput.addEventListener('input', function(e) {
                const query = e.target.value.toLowerCase();
                if (query.length > 0) {
                    loader.fetchAll().then(items => {
                        const filtered = items.filter(item => 
                            item.title.toLowerCase().includes(query) ||
                            (item.category && item.category.toLowerCase().includes(query))
                        );
                        mobileSearchResults.innerHTML = '';
                        filtered.slice(0, 10).forEach(item => {
                            const card = loader.createCard(item);
                            card.classList.add('p-2', 'border-b', 'border-gray-100');
                            mobileSearchResults.appendChild(card);
                        });
                        if (filtered.length === 0) {
                            mobileSearchResults.innerHTML = '<p class="text-gray-500 text-center p-4">No results found</p>';
                        }
                    });
                } else {
                    mobileSearchResults.innerHTML = '';
                }
            });
        }
    }
    
    const searchToggleDesktop = document.getElementById('searchToggleBtnDesktop');
    if (searchToggleDesktop) {
        searchToggleDesktop.addEventListener('click', function(e) {
            e.stopPropagation();
            const input = document.getElementById('searchInput');
            if (input) {
                if (input.classList.contains('hidden')) {
                    input.classList.remove('hidden');
                    input.focus();
                } else {
                    input.classList.add('hidden');
                }
            }
        });
    }
    
    const desktopSearchInput = document.getElementById('searchInput');
    if (desktopSearchInput && loader) {
        desktopSearchInput.addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase();
            const gridId = desktopSearchInput.closest('header').nextElementSibling?.querySelector('#dynamicFileGrid, #fileGrid');
            if (gridId) {
                const cards = gridId.querySelectorAll('.searchable-card');
                cards.forEach(card => {
                    const text = card.textContent.toLowerCase();
                    card.style.display = text.includes(query) ? '' : 'none';
                });
            }
        });
    }
});
</script>

<!-- Mobile Bottom Nav -->
<div class="md:hidden fixed bottom-0 left-0 right-0 z-[100] bg-white border-t border-gray-100 px-4 py-2 shadow-[0_-5px_20px_rgba(0,0,0,0.05)]">
    <nav class="flex justify-around items-center">
        <a href="${rp}index.html" class="flex flex-col items-center gap-1 text-blue-600 p-2">
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
            <span class="text-[10px] font-bold">Home</span>
        </a>
        <a href="${rp}explore.html" class="flex flex-col items-center gap-1 text-gray-500 p-2">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <span class="text-[10px] font-bold">Explore</span>
        </a>
        <a href="${rp}upload.html" class="flex items-center justify-center w-12 h-12 bg-blue-600 text-white rounded-full shadow-lg -translate-y-3 border-4 border-white">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"/></svg>
        </a>
        <a href="${rp}login.html" class="flex flex-col items-center gap-1 text-gray-500 p-2">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            <span class="text-[10px] font-bold">Account</span>
        </a>
        <button id="mobileMenuBtn" class="flex flex-col items-center gap-1 text-gray-500 p-2">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
            <span class="text-[10px] font-bold">Menu</span>
        </button>
    </nav>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const btn = document.getElementById('mobileMenuBtn');
    const sidebar = document.querySelector('aside');
    if (btn && sidebar) {
        let overlay = document.getElementById('sidebarOverlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'sidebarOverlay';
            overlay.className = 'fixed inset-0 bg-black/60 z-[110] hidden backdrop-blur-md transition-opacity';
            document.body.appendChild(overlay);
        }
        const toggle = (show) => {
            if (show) {
                sidebar.classList.remove('hidden');
                sidebar.classList.add('fixed', 'inset-y-0', 'left-0', 'z-[120]', 'w-72', 'shadow-2xl');
                overlay.classList.remove('hidden');
            } else {
                sidebar.classList.add('hidden');
                sidebar.classList.remove('fixed', 'inset-y-0', 'left-0', 'z-[120]', 'w-72', 'shadow-2xl');
                overlay.classList.add('hidden');
            }
        };
        btn.addEventListener('click', () => toggle(true));
        overlay.addEventListener('click', () => toggle(false));
    }
});
</script>

</body></html>`;

    // Replace from </main> to </html>
    content = content.replace(/<\/main>[\s\S]*<\/html>/g, newMobileNav);

    // Add loader initialization for category pages
    if (normalizedPath.includes('/index.html') && depth > 0) {
        // This is a category page like /linux/index.html
        let category = '';
        if (normalizedPath.includes('/linux/')) category = 'linux';
        else if (normalizedPath.includes('/apk/')) category = 'apk';
        else if (normalizedPath.includes('/windows/')) category = 'windows';
        else if (normalizedPath.includes('/pdf/')) category = 'pdf';
        else if (normalizedPath.includes('/zip/')) category = 'zip';
        else if (normalizedPath.includes('/presentation/')) category = 'ppt';
        else if (normalizedPath.includes('/opensource/')) category = 'opensource';

        if (category) {
            const loaderInit = `<script src="${rp}assets/js/dynamicLoader.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const loader = window.DynamicLoader;
    if (loader) {
        loader.fetchCategory('${category}').then(items => {
            loader.render(items, 'dynamicFileGrid');
            const indicator = document.getElementById('loadingIndicator');
            if (indicator) indicator.style.display = 'none';
        });
        loader.setupSearch('searchInput', 'dynamicFileGrid');
    }
});
</script>`;

            content = content.replace(/<script src="\.\.\/assets\/js\/dynamicLoader\.js"><\/script>/g, loaderInit);
        }
    }

    return content;
}

runGlobalMaintenance();