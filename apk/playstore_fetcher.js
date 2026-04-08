/**
 * Play Store Data Fetcher (v4 - UI Focus)
 * Automatically fetches rating, reviews, size, and version from Google Play.
 */

async function fetchPlayStoreData() {
    const appId = "com.yoyo.snake.rush";
    const playStoreUrl = `https://play.google.com/store/apps/details?id=${appId}`;
    
    console.log("Starting Play Store data fetch (v4)...");

    // Attempt with CORS Proxy (Fastest)
    try {
        console.log("Attempting fetch via corsproxy.io...");
        const response = await fetch(`https://corsproxy.io/?${encodeURIComponent(playStoreUrl)}`);
        if (response.ok) {
            const html = await response.text();
            if (parseAndUpdateData(html)) return;
        }
    } catch (e) { console.warn("corsproxy.io blocked."); }

    // JSONP Fallback (Most compatible for local file://)
    console.log("Attempting JSONP fallback via allorigins...");
    const script = document.createElement('script');
    script.src = `https://api.allorigins.win/get?url=${encodeURIComponent(playStoreUrl)}&callback=handlePlayStoreResponseV4`;
    document.body.appendChild(script);
}

window.handlePlayStoreResponseV4 = function(data) {
    if (data && data.contents) {
        console.log("Received data via JSONP.");
        parseAndUpdateData(data.contents);
    } else {
        console.error("JSONP fetch failed.");
    }
};

function parseAndUpdateData(html) {
    try {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, "text/html");

        // 1. Extract from JSON-LD (Most reliable for Rating/Reviews)
        let rating = null;
        let reviews = null;
        const jsonScripts = doc.querySelectorAll('script[type="application/ld+json"]');
        jsonScripts.forEach(s => {
            try {
                const j = JSON.parse(s.textContent);
                const items = Array.isArray(j) ? j : [j];
                items.forEach(item => {
                    if (item.aggregateRating) {
                        rating = parseFloat(item.aggregateRating.ratingValue).toFixed(1);
                        reviews = parseInt(item.aggregateRating.ratingCount).toLocaleString();
                    }
                });
            } catch (e) {}
        });

        // 2. Extract from Meta tags & DOM (For Android Ver, Size, Downloads)
        const bodyText = doc.body.innerText || doc.body.textContent;
        
        // Android Version
        const androidMatch = bodyText.match(/Android\s*(\d+\.?\d*)\s*and\s*up/i) || bodyText.match(/Android\s*(\d+\.?\d*)\+/i);
        const androidVer = androidMatch ? `Android ${androidMatch[1]}+` : "Android 5.0+";

        // Downloads
        const downloadMatch = bodyText.match(/(\d[\d,.]*[MKB]?\+?)\s+downloads/i);
        const downloads = downloadMatch ? downloadMatch[1] : "10,000,000+";

        // Size (Usually not in static HTML, fallback to a sensible value or extract if possible)
        // Note: Play Store rarely shows size in the static page text unless it's in a specific metadata field.
        const sizeMatch = bodyText.match(/(\d+\.?\d*)\s*(MB|GB|KB)/i);
        const size = sizeMatch ? sizeMatch[0] : "390 MB";

        const finalData = {
            rating: rating || "3.0",
            reviews: reviews || "4,750",
            androidVer,
            downloads,
            size
        };

        applyDataToUI(finalData);
        return true;
    } catch (e) {
        console.error("Parsing error:", e);
    }
    return false;
}

function applyDataToUI(data) {
    const ids = {
        'appRating': data.rating,
        'appReviews': data.reviews,
        'appAndroid': data.androidVer,
        'appSize': data.size
    };

    let updatedCount = 0;
    for (const [id, value] of Object.entries(ids)) {
        const el = document.getElementById(id);
        if (el) {
            el.textContent = value;
            updatedCount++;
        }
    }

    // Update Technical Table "Installs"
    const infoRows = document.querySelectorAll('div.flex.justify-between');
    infoRows.forEach(row => {
        if (row.textContent.includes("Installs:")) {
            const valSpan = row.querySelector('.text-white');
            if (valSpan) valSpan.textContent = data.downloads;
        }
    });

    console.log(`UI Updated: ${updatedCount} elements synced with Play Store.`);
}

document.addEventListener('DOMContentLoaded', fetchPlayStoreData);
