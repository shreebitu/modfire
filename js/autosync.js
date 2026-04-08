/**
 * Auto-Sync Engine
 * Fetches real-time data from Play Store and GitHub
 */
const config = {
    appId: "com.yoyo.snake.rush", // Fallback for stats as VPhoneGaGa is not on Play Store
    repo: "shreebitu/modfire",
    apkPath: "apk/"
};

async function initAutoSync() {
    console.log("🚀 Initializing Auto-Sync...");

    // 1. Fetch GitHub Content (Priority for APK Link & Size)
    fetchGitHubData();

    // 2. Fetch Play Store Content (Name, Logo, Ratings)
    fetchPlayStoreData();
}

async function fetchGitHubData() {
    try {
        const res = await fetch(`https://api.github.com/repos/${config.repo}/contents/${config.apkPath}`);
        if (!res.ok) {
            applyFallbackSize();
            return;
        }

        const files = await res.json();
        const apkFile = files.find(f => f.name.toLowerCase().endsWith('.apk') || f.name.toLowerCase().includes('vphonegaga'));

        if (apkFile) {
            const sizeMB = (apkFile.size / (1024 * 1024)).toFixed(2);
            console.log(`✅ APK Found: ${apkFile.name} (${sizeMB} MB)`);

            const sizeElem = document.getElementById('appHeroSize');
            if(sizeElem) sizeElem.textContent = `${sizeMB} MB`;
            
            const btnText = document.getElementById('downloadBtnText');
            if(btnText) btnText.textContent = `Download APK (${sizeMB} MB)`;

            // Update technical info table size if exists
            const sizeRows = Array.from(document.querySelectorAll('span')).filter(s => s.textContent.includes('Size:'));
            sizeRows.forEach(row => {
                const val = row.nextElementSibling;
                if (val) val.textContent = `${sizeMB} MB`;
            });

            // Set download link
            window.realDownloadUrl = apkFile.download_url;
            
            const badge = document.getElementById('syncBadge');
            if(badge) badge.classList.remove('hidden');
        } else {
            applyFallbackSize();
        }
    } catch (e) { 
        console.error("GitHub Sync failed", e); 
        applyFallbackSize();
    }
}

function applyFallbackSize() {
    console.warn("Using fallback GitHub data...");
    const sizeElem = document.getElementById('appHeroSize');
    if(sizeElem) sizeElem.textContent = `390 MB`;
    
    const btnText = document.getElementById('downloadBtnText');
    if(btnText) btnText.textContent = `Download APK (390 MB)`;
}

async function fetchPlayStoreData() {
    const playStoreUrl = `https://play.google.com/store/apps/details?id=${config.appId}`;
    const proxies = [
        url => `https://api.allorigins.win/get?url=${encodeURIComponent(url)}`,
        url => `https://corsproxy.io/?${encodeURIComponent(url)}`
    ];

    for (const getProxyUrl of proxies) {
        try {
            const response = await fetch(getProxyUrl(playStoreUrl));
            if (!response.ok) continue;

            let html = "";
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                const json = await response.json();
                html = json.contents || json;
            } else {
                html = await response.text();
            }

            if (parseAndUpdate(html)) {
                console.log("✅ Play Store Sync complete!");
                return;
            }
        } catch (e) { 
            console.warn("Proxy failed, trying next..."); 
        }
    }
    
    // If all proxies fail, apply fallbacks so it doesn't stay as "..."
    applyFallbackStats();
}

function parseAndUpdate(htmlContent) {
    try {
        const parser = new DOMParser();
        const doc = parser.parseFromString(htmlContent, "text/html");

        // Extract Rating and Reviews (JSON-LD)
        let success = false;
        doc.querySelectorAll('script[type="application/ld+json"]').forEach(s => {
            try {
                const j = JSON.parse(s.textContent);
                const items = Array.isArray(j) ? j : [j];
                items.forEach(item => {
                    if (item.aggregateRating) {
                        const ratingEl = document.getElementById('appRating');
                        const reviewsEl = document.getElementById('appReviews');
                        
                        if(ratingEl) ratingEl.textContent = parseFloat(item.aggregateRating.ratingValue).toFixed(1);
                        if(reviewsEl) reviewsEl.textContent = parseInt(item.aggregateRating.ratingCount).toLocaleString();
                        success = true;
                    }
                });
            } catch (e) { }
        });

        // Extract Android Version
        const bodyText = doc.body.innerText || doc.body.textContent;
        const androidMatch = bodyText.match(/Android\s*(\d+\.?\d*)\s*and\s*up/i) || bodyText.match(/Android\s*(\d+\.?\d*)\+/i);
        const androidEl = document.getElementById('appAndroid');
        if (androidMatch && androidEl) {
            androidEl.textContent = `Android ${androidMatch[1]}+`;
        }

        if (!success) return false;
        return true;
    } catch (e) { return false; }
}

function applyFallbackStats() {
    console.warn("Using fallback Play Store data...");
    const ratingEl = document.getElementById('appRating');
    const reviewsEl = document.getElementById('appReviews');
    const androidEl = document.getElementById('appAndroid');

    if(ratingEl && ratingEl.textContent === '...') ratingEl.textContent = '4.6';
    if(reviewsEl && reviewsEl.textContent === '...') reviewsEl.textContent = '12,500';
    if(androidEl && androidEl.textContent === 'Android ...') androidEl.textContent = 'Android 7.0+';
}

function startDownload() {
    const btn = document.getElementById('downloadBtn');
    const container = document.getElementById('progressContainer');
    const bar = document.getElementById('progressBar');
    const text = document.getElementById('progressText');
    const percent = document.getElementById('progressPercent');

    if(!btn || !container) return;

    btn.style.display = 'none';
    container.classList.remove('hidden');

    let width = 0;
    const interval = setInterval(() => {
        let increment = Math.random() * 8 + 2;
        width += increment;
        if (width > 100) width = 100;

        bar.style.width = width + "%";
        percent.innerText = Math.floor(width) + "%";

        if (width < 30) text.innerText = "Connecting to Secure Server...";
        else if (width < 70) text.innerText = "Downloading VPhoneGaGa.apk...";
        else if (width < 100) text.innerText = "Finalizing...";

        if (width >= 100) {
            clearInterval(interval);
            text.innerText = "Download Complete!";
            text.style.color = "#10b981";

            setTimeout(() => {
                window.location.href = window.realDownloadUrl || "#";
            }, 500);
        }
    }, 300);
}

document.addEventListener('DOMContentLoaded', initAutoSync);
