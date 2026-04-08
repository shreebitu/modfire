/**
 * Auto-Sync Engine
 * Fetches real-time data from Play Store with 3-Day Caching
 */

const CACHE_KEY = "vphonegaga_playstore_data";
const CACHE_TIME_KEY = "vphonegaga_playstore_sync_time";
const CACHE_EXPIRY_MS = 3 * 24 * 60 * 60 * 1000; // 3 Days in milliseconds

// Exact URL requested by user
const playStoreUrl = `https://play.google.com/store/apps/details?id=com.yoyo.snake.rush&referrer=utm_source%3Dvphoneos_website`;

async function initAutoSync() {
    console.log("🚀 Initializing Auto-Sync from Play Store URL...");

    // Check Cache First
    const lastSync = localStorage.getItem(CACHE_TIME_KEY);
    if (lastSync && (Date.now() - parseInt(lastSync) < CACHE_EXPIRY_MS)) {
        const cachedData = localStorage.getItem(CACHE_KEY);
        if (cachedData) {
            console.log("⚡ Using Cached Play Store Data (Valid for 3 days).");
            applyDataToUI(JSON.parse(cachedData));
            return;
        }
    }

    // Cache missing or expired, fetch new data
    fetchPlayStoreData();
}

async function fetchPlayStoreData() {
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

            const extractedData = parsePlayStoreHtml(html);
            if (extractedData) {
                console.log("✅ Play Store Sync complete! Saving to cache...");
                
                // Save to localStorage
                localStorage.setItem(CACHE_KEY, JSON.stringify(extractedData));
                localStorage.setItem(CACHE_TIME_KEY, Date.now().toString());
                
                // Apply immediately
                applyDataToUI(extractedData);
                return;
            }
        } catch (e) { 
            console.warn("Proxy failed, trying next..."); 
        }
    }
    
    // If all proxies fail, apply fallbacks
    applyFallbackStats();
}

function parsePlayStoreHtml(htmlContent) {
    try {
        const parser = new DOMParser();
        const doc = parser.parseFromString(htmlContent, "text/html");
        let data = { rating: null, reviews: null, android: null };

        // EXTRACT RATING & REVIEWS
        // Method 1: JSON-LD Structured Data
        doc.querySelectorAll('script[type="application/ld+json"]').forEach(s => {
            try {
                const j = JSON.parse(s.textContent);
                const items = Array.isArray(j) ? j : [j];
                items.forEach(item => {
                    if (item.aggregateRating && !data.rating) {
                        data.rating = parseFloat(item.aggregateRating.ratingValue).toFixed(1);
                        data.reviews = parseInt(item.aggregateRating.ratingCount).toLocaleString();
                    }
                });
            } catch (e) { }
        });

        // Method 2: Regex HTML Scrape as Fallback
        if (!data.rating) {
            // Google Play obfuscates classes, so we rely on Aria-labels
            const ratingAriaMatch = htmlContent.match(/aria-label="Rated ([\d\.]+) stars out of five"/i);
            const reviewsAriaMatch = htmlContent.match(/([\d\.,]+)\s*reviews/i) || htmlContent.match(/([\d\.,]+)\s*ratings/i);
            
            // Backup direct regex
            const nakedRatingMatch = htmlContent.match(/<div class="[^"]*">([\d\.]+)<i class="star/);

            if (ratingAriaMatch) data.rating = parseFloat(ratingAriaMatch[1]).toFixed(1);
            else if (nakedRatingMatch) data.rating = parseFloat(nakedRatingMatch[1]).toFixed(1);
            
            if (reviewsAriaMatch) data.reviews = reviewsAriaMatch[1];
        }

        // EXTRACT ANDROID VERSION
        const bodyText = doc.body.innerText || doc.body.textContent;
        const androidMatch = bodyText.match(/Requires Android[\s\n]*([\d\.]+) and up/i) || bodyText.match(/Android\s*(\d+\.?\d*)\s*and\s*up/i) || bodyText.match(/Android\s*(\d+\.?\d*)\+/i);
        if (androidMatch) {
            data.android = `${androidMatch[1]}+`;
        }

        // Return data only if we successfully grabbed something
        if (data.rating || data.android) {
            return data;
        }

        return null;
    } catch (e) { return null; }
}

function applyDataToUI(data) {
    const ratingEl = document.getElementById('appRating');
    const reviewsEl = document.getElementById('appReviews');
    const androidEl = document.getElementById('appAndroid');

    if (ratingEl && data.rating) ratingEl.textContent = data.rating;
    if (reviewsEl && data.reviews) reviewsEl.textContent = data.reviews;
    if (androidEl && data.android) androidEl.textContent = data.android;
}

function applyFallbackStats() {
    console.warn("Using fallback Play Store data...");
    const ratingEl = document.getElementById('appRating');
    const reviewsEl = document.getElementById('appReviews');
    const androidEl = document.getElementById('appAndroid');

    // Only apply if they are still the '...' placeholder
    if(ratingEl && ratingEl.textContent.includes('...')) ratingEl.textContent = '4.6';
    if(reviewsEl && reviewsEl.textContent.includes('...')) reviewsEl.textContent = '12,500';
    if(androidEl && androidEl.textContent.includes('...')) androidEl.textContent = '7.0+';
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
