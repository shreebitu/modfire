const fs = require('fs');
const path = require('path');

const files = ['index.html', 'about.html', 'explore.html', 'upload.html', 'contact.html', 'privacy.html'];

const fontLink = `    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">\n    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>`;

files.forEach(file => {
    let content = fs.readFileSync(file, 'utf8');

    // 1. Add Google Fonts
    if (!content.includes('fonts.googleapis.com/css2?family=Outfit')) {
        content = content.replace('<script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>', fontLink);
    }
    
    // 2. Add font-family to style
    if (!content.includes("font-family: 'Outfit'")) {
        content = content.replace('<style>', "<style>\n        body { font-family: 'Outfit', sans-serif; }");
    }

    // 3. Upgrade Background (Add subtle gradient logic or keep deep blue)
    content = content.replace(/body class="bg-gray-950 text-gray-200"/g, 'body class="bg-[#0B0F19] bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-[#111827] via-[#0B0F19] to-black text-gray-200 antialiased selection:bg-indigo-500/30"');
    
    // 4. Upgrade Header to Glassmorphism
    content = content.replace(/bg-gray-950\/80 border-b border-gray-800/g, 'backdrop-blur-xl bg-[#0B0F19]/60 border-b border-white/5 shadow-sm');
    content = content.replace(/bg-gray-950\/80 border-gray-800/g, 'backdrop-blur-xl bg-[#0B0F19]/60 border-white/5 shadow-sm');
    
    // 5. Upgrade Logo
    content = content.replace(/text-xl font-bold tracking-wide text-white hover:text-indigo-400/g, 'text-2xl font-extrabold tracking-wider text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-purple-400 hover:opacity-80 drop-shadow-md');
    
    // 6. Upgrade Nav Links
    content = content.replace(/hover:text-white transition/g, 'hover:text-white hover:text-indigo-400 transition-colors duration-300');
    
    // 7. Upgrade Primary Buttons (Indigo)
    content = content.replace(/bg-indigo-600 hover:bg-indigo-500/g, 'bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 shadow-lg shadow-indigo-500/25 border border-white/10');
    
    // 8. Upgrade Cards to Glassmorphism
    content = content.replace(/bg-gray-900 border border-gray-800/g, 'backdrop-blur-md bg-white/[0.02] border border-white/5 shadow-2xl shadow-black/50');
    content = content.replace(/bg-gray-900 border-gray-800/g, 'backdrop-blur-md bg-white/[0.02] border-white/5 shadow-2xl shadow-black/50');
    
    // 9. Fix remaining gray-800 borders to low opacity white
    content = content.replace(/border-gray-800/g, 'border-white/5');

    // 10. Improve text readability
    content = content.replace(/text-gray-400/g, 'text-gray-400/90');
    
    // 11. Add a background glow element inside the body (before HEADER)
    if (!content.includes('pointer-events-none fixed inset-0 z-0')) {
        content = content.replace('<!-- HEADER -->', '<!-- BACKGROUND GLOW -->\n  <div class="pointer-events-none fixed inset-0 z-0 flex justify-center">\n    <div class="w-[800px] h-[500px] bg-indigo-600/10 rounded-full blur-[120px] -translate-y-1/2"></div>\n  </div>\n\n  <!-- HEADER -->');
    }

    fs.writeFileSync(file, content, 'utf8');
    console.log(`Updated ${file}`);
});
