<?php
require_once 'config.php';
require_once 'db.php';

// Check if admin
// Temporary bypass for CLI execution
// if (!isAdmin()) {
//     die("Unauthorized access. Please log in as admin.");
// }

try {
    // 1. Delete all existing apps
    $pdo->exec("DELETE FROM apps");
    $pdo->exec("ALTER TABLE apps AUTO_INCREMENT = 1");
    echo "Existing apps cleared.<br>";

    // 2. Sample Data for 10 High Quality Demo Apps
    $apps = [
        [
            'name' => 'WhatsApp Messenger',
            'description' => '<p>WhatsApp from Meta is a FREE messaging and video calling app. It’s used by over 2B people in more than 180 countries. It’s simple, reliable, and private, so you can easily keep in touch with your friends and family.</p><ul><li>Private messaging across the world</li><li>Simple and secure connections, right away</li><li>High quality voice and video calls</li><li>Group chats to keep you in touch</li></ul>',
            'logo' => 'uploads/whatsapp.png',
            'apk_link' => 'https://www.whatsapp.com/download',
            'category' => 'Social',
            'user_id' => 1,
            'file_size' => '35.2 MB',
            'os_compatible' => 'Android 5.0+, iOS 12.0+',
            'language' => 'Multilingual',
            'license_type' => 'Free',
            'developer' => 'Meta Platforms Inc.',
            'status' => 'approved'
        ],
        [
            'name' => 'Adobe Lightroom Mobile',
            'description' => '<p>Adobe Photoshop Lightroom is a free, powerful photo & video editor and camera app that empowers you to capture and edit stunning images.</p><p>Lightroom offers easy-to-use photo & video editing tools like sliders to retouch your images, photo filters, and transformative presets to quickly apply unique adjustments that bring your photos to life wherever you are – all in one app.</p>',
            'logo' => 'uploads/lightroom.png',
            'apk_link' => 'https://www.adobe.com/products/photoshop-lightroom.html',
            'category' => 'Productivity',
            'user_id' => 1,
            'file_size' => '98.5 MB',
            'os_compatible' => 'Android 8.0+',
            'language' => 'English, Spanish, French',
            'license_type' => 'Freemium',
            'developer' => 'Adobe Inc.',
            'status' => 'approved'
        ],
        [
            'name' => 'Grand Theft Auto V',
            'description' => '<p>Grand Theft Auto V for PC offers players the option to explore the award-winning world of Los Santos and Blaine County in resolutions of up to 4k and beyond, as well as the chance to experience the game running at 60 frames per second.</p><p>The game offers players a huge range of PC-specific customization options, including over 25 separate configurable settings for texture quality, shaders, tessellation, anti-aliasing and more.</p>',
            'logo' => 'uploads/gtav.png',
            'apk_link' => 'https://www.rockstargames.com/gta-v',
            'category' => 'Games',
            'user_id' => 1,
            'file_size' => '105 GB',
            'os_compatible' => 'Windows 10/11',
            'language' => 'English, Hindi, many more',
            'license_type' => 'Paid',
            'developer' => 'Rockstar North',
            'status' => 'approved'
        ],
        [
            'name' => 'CCleaner Professional',
            'description' => '<p>CCleaner is the number-one tool for cleaning your PC. It protects your privacy and makes your computer faster and more secure!</p><ul><li>Automatic Cleaning</li><li>Privacy Protection</li><li>Complete PC Health Check</li><li>Software Updater</li></ul>',
            'logo' => 'uploads/ccleaner.png',
            'apk_link' => 'https://www.ccleaner.com/',
            'category' => 'Tools',
            'user_id' => 1,
            'file_size' => '28.1 MB',
            'os_compatible' => 'Windows, Android, Mac',
            'language' => 'Multilingual',
            'license_type' => 'Premium',
            'developer' => 'Piriform',
            'status' => 'approved'
        ],
        [
            'name' => 'Spotify: Music and Podcasts',
            'description' => '<p>With Spotify, you can play millions of songs and podcasts for free. Listen to the songs and podcasts you love and find music from all over the world.</p><ul><li>Discover new music, albums, playlists and podcasts</li><li>Search for your favorite song, artist, or podcast</li><li>Enjoy music playlists and a unique daily mix made just for you</li></ul>',
            'logo' => 'uploads/spotify.png',
            'apk_link' => 'https://www.spotify.com/',
            'category' => 'Social',
            'user_id' => 1,
            'file_size' => '25.6 MB',
            'os_compatible' => 'Android, iOS, Windows',
            'language' => 'Global',
            'license_type' => 'Freemium',
            'developer' => 'Spotify AB',
            'status' => 'approved'
        ],
        [
            'name' => 'Minecraft',
            'description' => '<p>Explore infinite worlds and build everything from the simplest of homes to the grandest of castles. Play in creative mode with unlimited resources or mine deep into the world in survival mode, crafting weapons and armor to fend off dangerous mobs.</p>',
            'logo' => 'uploads/minecraft.png',
            'apk_link' => 'https://www.minecraft.net/',
            'category' => 'Games',
            'user_id' => 1,
            'file_size' => '450 MB',
            'os_compatible' => 'All Platforms',
            'language' => 'Multilingual',
            'license_type' => 'Paid',
            'developer' => 'Mojang Studios',
            'status' => 'approved'
        ],
        [
            'name' => 'Microsoft Office 365',
            'description' => '<p>The Microsoft 365 app is home to all your favorite productivity apps and content. Now with new ways to help you find, create, share and save your content, all in one place.</p><p>Includes Word, Excel, PowerPoint, and Outlook.</p>',
            'logo' => 'assets/images/logo.png',
            'apk_link' => 'https://www.office.com/',
            'category' => 'Productivity',
            'user_id' => 1,
            'file_size' => '2.1 GB',
            'os_compatible' => 'Windows, Mac, Mobile',
            'language' => 'Global',
            'license_type' => 'Subscription',
            'developer' => 'Microsoft Corporation',
            'status' => 'approved'
        ],
        [
            'name' => 'NordVPN Premium',
            'description' => '<p>Experience the ultimate online privacy and security with NordVPN. Protect your data and stay anonymous online with our advanced encryption and global network of servers.</p><ul><li>Ultra-fast servers</li><li>No-logs policy</li><li>Connect up to 6 devices</li></ul>',
            'logo' => 'assets/images/arch_logo.png',
            'apk_link' => 'https://nordvpn.com/',
            'category' => 'Tools',
            'user_id' => 1,
            'file_size' => '15.4 MB',
            'os_compatible' => 'Windows, Android, iOS',
            'language' => 'English',
            'license_type' => 'Paid',
            'developer' => 'Nord Security',
            'status' => 'approved'
        ],
        [
            'name' => 'Instagram Pro',
            'description' => '<p>Bringing you closer to the people and things you love. Connect with friends, share what you’re up to, or see what\'s new from others all over the world.</p>',
            'logo' => 'assets/images/ubuntu_logo.png',
            'apk_link' => 'https://www.instagram.com/',
            'category' => 'Social',
            'user_id' => 1,
            'file_size' => '42.1 MB',
            'os_compatible' => 'Android, iOS',
            'language' => 'Global',
            'license_type' => 'Free',
            'developer' => 'Instagram (Meta)',
            'status' => 'approved'
        ],
        [
            'name' => 'FL Studio Mobile',
            'description' => '<p>Create and save complete multi-track music projects on your Phone, Tablet or Laptop (Android, iOS or Windows). Record, sequence, edit, mix and render complete songs.</p>',
            'logo' => 'assets/images/vphonegaga_logo.webp',
            'apk_link' => 'https://www.image-line.com/fl-studio-mobile/',
            'category' => 'Productivity',
            'user_id' => 1,
            'file_size' => '220 MB',
            'os_compatible' => 'Android, iOS, Windows',
            'language' => 'English',
            'license_type' => 'Paid',
            'developer' => 'Image-Line',
            'status' => 'approved'
        ]
    ];

    $stmt = $pdo->prepare("INSERT INTO apps (name, description, logo, apk_link, category, user_id, file_size, os_compatible, language, license_type, developer, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    foreach ($apps as $app) {
        $stmt->execute([
            $app['name'],
            $app['description'],
            $app['logo'],
            $app['apk_link'],
            $app['category'],
            $app['user_id'],
            $app['file_size'],
            $app['os_compatible'],
            $app['language'],
            $app['license_type'],
            $app['developer'],
            $app['status']
        ]);
        echo "Inserted: " . $app['name'] . "<br>";
    }

    echo "<b>All 10 high-quality demo apps have been seeded successfully!</b>";

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
