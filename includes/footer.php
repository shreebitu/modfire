<?php
if (!isset($base_url)) {
    require_once 'init.php';
}
?>
<footer class="bg-white border-t border-slate-100 pt-16 pb-8 mt-20">
    <div class="max-w-[1400px] mx-auto px-6 md:px-10">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-16">
            <!-- Brand Column -->
            <div class="space-y-6">
                <a href="<?php echo $base_url; ?>" class="flex items-center gap-0">
                    <div class="w-12 h-12 flex items-center justify-center overflow-hidden">
                        <img src="<?php echo $base_url; ?>assets/images/logo.png" class="w-full h-full object-contain mix-blend-multiply" alt="Logo">
                    </div>
                    <div class="-ml-1">
                        <h1 class="text-2xl font-black leading-tight flex items-center">
                            <span class="text-indigo-950">MOD</span>
                            <span class="text-orange-600">FIRE</span>
                        </h1>
                    </div>
                </a>
                <p class="text-slate-500 text-sm leading-relaxed font-medium">
                    The ultimate destination for premium APKs, Windows software, and verified digital tools. Experience high-speed, secure downloads every time.
                </p>
                <div class="flex items-center gap-4">
                    <a href="#" class="w-10 h-10 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 hover:bg-indigo-600 hover:text-white transition-all shadow-sm">
                        <span class="material-symbols-outlined text-lg">public</span>
                    </a>
                    <a href="#" class="w-10 h-10 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 hover:bg-blue-500 hover:text-white transition-all shadow-sm">
                        <span class="material-symbols-outlined text-lg">share</span>
                    </a>
                    <a href="#" class="w-10 h-10 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 hover:bg-red-500 hover:text-white transition-all shadow-sm">
                        <span class="material-symbols-outlined text-lg">mail</span>
                    </a>
                </div>
            </div>

            <!-- Quick Links -->
            <div>
                <h4 class="text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] mb-8">Navigation</h4>
                <ul class="space-y-4">
                    <li><a href="<?php echo $base_url; ?>index.php" class="text-sm font-bold text-slate-600 hover:text-indigo-600 transition-colors flex items-center gap-2"><span class="material-symbols-outlined text-[18px]">explore</span> Discovery</a></li>
                    <li><a href="<?php echo $base_url; ?>category/apk" class="text-sm font-bold text-slate-600 hover:text-indigo-600 transition-colors flex items-center gap-2"><span class="material-symbols-outlined text-[18px]">android</span> Android Apps</a></li>
                    <li><a href="<?php echo $base_url; ?>category/windows" class="text-sm font-bold text-slate-600 hover:text-indigo-600 transition-colors flex items-center gap-2"><span class="material-symbols-outlined text-[18px]">desktop_windows</span> Windows PC</a></li>
                </ul>
            </div>

            <!-- Information -->
            <div>
                <h4 class="text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] mb-8">Information</h4>
                <ul class="space-y-4">
                    <li><a href="<?php echo $base_url; ?>about.php" class="text-sm font-bold text-slate-600 hover:text-indigo-600 transition-colors flex items-center gap-2"><span class="material-symbols-outlined text-[18px]">info</span> About Us</a></li>
                    <li><a href="<?php echo $base_url; ?>contact.php" class="text-sm font-bold text-slate-600 hover:text-indigo-600 transition-colors flex items-center gap-2"><span class="material-symbols-outlined text-[18px]">contact_support</span> Contact Support</a></li>
                    <li><a href="<?php echo $base_url; ?>privacy.php" class="text-sm font-bold text-slate-600 hover:text-indigo-600 transition-colors flex items-center gap-2"><span class="material-symbols-outlined text-[18px]">policy</span> Privacy Policy</a></li>
                </ul>
            </div>

            <!-- Legal & DMCA -->
            <div>
                <h4 class="text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] mb-8">Legal Compliance</h4>
                <ul class="space-y-4">
                    <li><a href="<?php echo $base_url; ?>dmca.php" class="text-sm font-bold text-slate-600 hover:text-red-600 transition-colors flex items-center gap-2"><span class="material-symbols-outlined text-[18px]">gavel</span> DMCA Notice</a></li>
                    <li><a href="<?php echo $base_url; ?>dmca.php#report" class="text-sm font-bold text-slate-600 hover:text-red-600 transition-colors flex items-center gap-2"><span class="material-symbols-outlined text-[18px]">flag</span> Report Infringement</a></li>
                </ul>
                <div class="mt-8 p-4 bg-slate-50 rounded-2xl border border-slate-100">
                    <p class="text-[10px] text-slate-500 font-bold leading-relaxed">
                        We comply with 17 U.S.C. § 512 and the DMCA. All trademarks belong to their respective owners.
                    </p>
                </div>
            </div>
        </div>

        <!-- Bottom Copyright -->
        <div class="pt-8 border-t border-slate-50 flex flex-col md:flex-row justify-between items-center gap-6">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">
                &copy; <?php echo date('Y'); ?> <span class="text-indigo-950">MOD</span><span class="text-orange-600">FIRE</span> NETWORK. ALL RIGHTS RESERVED.
            </p>
            <div class="flex items-center gap-8">
                <span class="text-[10px] font-black text-slate-300 uppercase tracking-tighter">SECURE SSL ENCRYPTED</span>
                <span class="text-[10px] font-black text-slate-300 uppercase tracking-tighter">DMCA PROTECTED</span>
            </div>
        </div>
    </div>
</footer>
