/**
 * =============================================
 *   APP REGISTRY — ShreeBitu.in
 * =============================================
 *  Naya app add karna ho toh sirf neeche
 *  APP_LIST array mein ek object add karo.
 *  Automatically homepage pe card show ho jaayega.
 *
 *  ⚠️  IMPORTANT: Sirf woh apps yahan add karo
 *  jo GitHub repo mein NAHI hain. Jo apps GitHub
 *  pe hain wo automatically GitHub API se load
 *  ho jaate hain — unhe yahan daalne se duplicate
 *  ban jaata hai.
 *
 *  GitHub repo mein hain (mat daalo):
 *    ✗ vphonegaga.html  — GitHub se auto-load hota hai
 *
 *  Yahan daalo (GitHub pe nahi hain):
 *    ✓ twoyi.html
 * =============================================
 */

const APP_LIST = [
    {
        name: "VPhoneGaGa",
        subtitle: "Virtual Android Machine",
        description: "Download VPhoneGaGa APK — the best virtual Android machine. Create an isolated environment, run multiple accounts, and get Root access easily.",
        icon: "apk/vimg/vphonegaga_logo.webp",
        pageUrl: "apk/vphonegaga.html",
        filename: "vphonegaga",
        size: "390 MB",
        type: "app",
        badge: "📱",
        color: "indigo",
        updated: "Feb 2026"
    },
    {
        name: "Twoyi",
        subtitle: "Android Container · Open Source",
        description: "Twoyi is a lightweight Android container that runs a nearly complete Android system as a normal app. No root required. Supports Xposed, LSPosed & Magisk modules.",
        icon: "apk/vimg/twoyi.png",
        pageUrl: "apk/twoyi.html",
        filename: "twoyi",
        size: "~45 MB",
        type: "app",
        badge: "📱",
        color: "cyan",
        updated: "Mar 2022"
    },
    {
        name: "HAPI",
        subtitle: "Vibe Coding Anywhere · AI Terminal",
        description: "Control Claude Code, Codex, Gemini & OpenCode from your phone. Run AI agents on your machine and manage them remotely with E2E encryption.",
        icon: "apk/vimg/hapi.png",
        pageUrl: "apk/hapi.html",
        filename: "hapi",
        size: "CLI Tool",
        type: "app",
        badge: "⌨️",
        color: "purple",
        updated: "Apr 2026"
    },
    {
        name: "Gene Transfer Methods",
        subtitle: "Biology Presentation · Free PPT",
        description: "Comprehensive PowerPoint presentation on Gene Transfer Methods — Agrobacterium, Biolistics, Electroporation, Microinjection, Viral Vectors & more. Free download.",
        icon: "",
        pageUrl: "apk/gene-transfer-ppt.html",
        filename: "gene-transfer-ppt",
        size: "~36 KB",
        type: "document",
        badge: "📊",
        color: "orange",
        updated: "Apr 2026"
    }
    // =============================================
    // NAYA APP ADD KARNA HO TOH YAHAN COPY KARO:
    // {
    //     name: "App Ka Naam",
    //     subtitle: "Chhota description",
    //     description: "Lamba description jo card pe dikhega.",
    //     icon: "apk/vimg/your_logo.png",   // ya webp
    //     pageUrl: "apk/yourapp.html",
    //     filename: "yourapp",              // HTML file ka naam (extension ke bina)
    //     size: "XX MB",
    //     type: "app",                      // app / document / image / media
    //     badge: "📱",
    //     color: "indigo",                  // indigo / cyan / purple / emerald / amber
    //     updated: "Apr 2026"
    // },
    // =============================================
];

/**
 * Color classes map karo Tailwind classes se
 */
const COLOR_MAP = {
    indigo: {
        bg: "bg-indigo-600/20",
        btn: "from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 shadow-indigo-500/25"
    },
    cyan: {
        bg: "bg-cyan-600/20",
        btn: "from-cyan-600 to-blue-600 hover:from-cyan-500 hover:to-blue-500 shadow-cyan-500/25"
    },
    purple: {
        bg: "bg-purple-600/20",
        btn: "from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 shadow-purple-500/25"
    },
    emerald: {
        bg: "bg-emerald-600/20",
        btn: "from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 shadow-emerald-500/25"
    },
    amber: {
        bg: "bg-amber-600/20",
        btn: "from-amber-600 to-orange-600 hover:from-amber-500 hover:to-orange-500 shadow-amber-500/25"
    },
    orange: {
        bg: "bg-orange-600/20",
        btn: "from-orange-600 to-red-600 hover:from-orange-500 hover:to-red-500 shadow-orange-500/25"
    }
};

/**
 * Registry se cards render karo — index.html ke dynamicFileGrid mein inject karta hai
 */
function renderAppRegistryCards() {
    const grid = document.getElementById('dynamicFileGrid');
    if (!grid) return;

    // Reliable duplicate check: existing cards ke href links dekho
    // (Title-based check unreliable hai kyunki GitHub async update karta hai)
    const existingHrefs = Array.from(grid.querySelectorAll('a[href]'))
        .map(a => a.getAttribute('href').toLowerCase());

    APP_LIST.forEach(app => {
        // filename se check karo (e.g. "twoyi" from "apk/twoyi.html")
        const appFilename = (app.filename || app.pageUrl.split('/').pop().replace('.html', '')).toLowerCase();
        const isDuplicate = existingHrefs.some(href => href.includes(appFilename));
        if (isDuplicate) return;

        const colors = COLOR_MAP[app.color] || COLOR_MAP.indigo;

        const card = document.createElement('div');
        card.className = "card-hover backdrop-blur-md bg-white/[0.02] border border-white/5 shadow-2xl shadow-black/50 rounded-2xl p-6 searchable-card";
        card.setAttribute('data-type', app.type);
        card.setAttribute('data-registry-app', appFilename);

        // Logo ya emoji icon
        const iconHtml = app.icon
            ? `<img src="${app.icon}" alt="${app.name} logo" class="w-10 h-10 object-contain rounded-lg" onerror="this.parentElement.innerHTML='${app.badge}'">`
            : app.badge;

        card.innerHTML = `
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 ${colors.bg} rounded-xl flex items-center justify-center text-2xl flex-shrink-0 overflow-hidden card-icon">
                    ${iconHtml}
                </div>
                <div class="overflow-hidden">
                    <h3 class="text-white font-semibold truncate card-title" title="${app.name}">${app.name}</h3>
                <p class="text-gray-500 text-xs mt-0.5">${app.type === 'document' ? 'PPT' : 'APK'} • ${app.size}</p>
                </div>
            </div>
            <p class="text-gray-400/90 text-sm mb-4 line-clamp-2 card-desc">${app.description}</p>
            <div class="flex items-center justify-between">
                <span class="text-xs text-gray-500 card-date">Updated ${app.updated}</span>
                <a href="${app.pageUrl}" 
                   class="bg-gradient-to-r ${colors.btn} shadow-lg border border-white/10 px-4 py-2 rounded-lg text-xs font-medium transition hover:scale-105 active:scale-95 text-white">
                   ${app.type === 'document' ? 'Download' : 'View App'}
                </a>
            </div>
        `;

        grid.appendChild(card);
    });
}

// DOM load hone ke baad TURANT run karo (GitHub API se pehle)
// Isse GitHub API cards aane par registry cards already honge aur duplicates skip ho jaayenge
document.addEventListener('DOMContentLoaded', () => {
    renderAppRegistryCards();
});
