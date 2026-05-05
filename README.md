# 🔥 MODFIRE — Premium App Marketplace

[![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=flat-square&logo=mysql&logoColor=white)](https://mysql.com)
[![TailwindCSS](https://img.shields.io/badge/TailwindCSS-CDN-06B6D4?style=flat-square&logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![License](https://img.shields.io/badge/License-Proprietary-red?style=flat-square)]()

> A full-featured, self-hosted digital marketplace for distributing APKs, PDFs, presentations, and Windows software — with admin moderation, user accounts, reviews, SEO-optimized URLs, and a premium glassmorphic UI.

---

## 📋 Table of Contents

- [Features](#-features)
- [Tech Stack](#-tech-stack)
- [Folder Structure](#-folder-structure)
- [Installation](#-installation)
- [Database Setup](#-database-setup)
- [Configuration](#-configuration)
- [Usage Guide](#-usage-guide)
- [Admin Panel](#-admin-panel)
- [API Endpoints](#-api-endpoints)
- [Security](#-security)
- [SEO](#-seo)
- [Contributing](#-contributing)

---

## ✨ Features

### Public Features
- 🏠 **Homepage** — Hero banner, featured sections (Top/Popular/Recent), category-based sections, search, pagination
- 📦 **App Detail Pages** — Rich descriptions, screenshot carousel (Swiper.js), specs sidebar, older versions, related apps
- 🔍 **Search & Filter** — Real-time search by app name, filter by category
- ⭐ **Reviews & Ratings** — 5-star rating system with Google Play Store-style breakdown chart
- 📥 **Download Tracking** — Every download is logged with IP, user agent, and timestamp
- 🗂️ **Categories** — Dynamic category system with Material icon support
- 📄 **SEO-Optimized** — Clean slugs, canonical URLs, Open Graph tags, dynamic XML sitemap
- 📱 **Fully Responsive** — Premium mobile experience with slide-out sidebar drawer

### User Features
- 🔐 **Authentication** — Secure login/register with bcrypt password hashing
- 📤 **App Submission** — Upload apps with logo, file upload (APK/PDF/PPT), or external link
- ✏️ **Edit Submissions** — Modify your own submitted apps
- ❤️ **Favorites** — Save/unsave apps with AJAX toggle (instant, no page reload)
- 👤 **Profile Management** — Update username, email, profile picture
- 📊 **My Apps Dashboard** — View all your submissions with status badges

### Admin Features
- 📊 **Command Center Dashboard** — Stats, activity stream, system health, content mix charts
- ✅ **App Moderation** — Approve/reject submissions with auto-slug generation and user notifications
- 👥 **User Management** — Ban, shadow-ban, role changes, user deletion
- 📝 **Review Moderation** — Delete inappropriate reviews
- 🚩 **Report System** — View and resolve user-filed reports
- ⚙️ **Site Settings** — Maintenance mode, registration toggle, upload toggle, file size limits, allowed extensions
- 🔒 **Security Center** — IP blocking, session management, activity logs
- 💾 **Database Backup** — Export database from admin panel
- 📋 **Activity Logs** — Full audit trail with user, action, IP, and timestamp

### Technical Features
- 🛡️ **CSRF Protection** — Token-based form validation on all POST requests
- 🔑 **Session Token Validation** — Server-side session integrity checks
- 🚫 **IP Blocking** — Block abusive IPs from the admin panel
- 📈 **Rate Limiting** — 5 submissions/hour, 5 reports/hour per user
- 👻 **Shadow Banning** — Users see their own content but it's hidden from others
- 🗺️ **Dynamic Sitemap** — Auto-generated XML sitemap with all pages, categories, and apps

---

## 🛠️ Tech Stack

| Layer | Technology |
|-------|-----------|
| **Backend** | PHP 8.0+ (vanilla, no framework) |
| **Database** | MySQL 8.0+ / MariaDB 10.5+ |
| **Frontend** | HTML5, Vanilla JavaScript |
| **Styling** | TailwindCSS (CDN), Custom CSS |
| **Icons** | Google Material Symbols (Outlined) |
| **Fonts** | Inter (Google Fonts) |
| **Carousel** | Swiper.js 11 (app screenshots) |
| **Server** | Apache with mod_rewrite (XAMPP / cPanel) |

---

## 📁 Folder Structure

```
shreebitu/
├── includes/          # Core: bootstrap, helpers, shared UI components
├── auth/              # Authentication: login, register, logout
├── user/              # User area: submit apps, favorites, profile
├── admin/             # Admin panel: dashboard, moderation, settings
│   └── components/    # Admin UI components (header, sidebar)
├── assets/
│   ├── css/           # Stylesheets
│   ├── js/            # Client-side scripts
│   └── images/        # Static images and logos
├── uploads/           # User-uploaded files (logos, profile pics, app files)
│   ├── profile_pics/  # User avatars
│   └── files/         # Uploaded APK/PDF/PPT files
├── config.php         # Global configuration
├── db.php             # Database connection (PDO)
├── database.sql       # Full database schema + seed data
├── index.php          # Homepage
├── post.php           # App detail page (slug-based)
├── category.php       # Category listing
├── download.php       # Download handler
├── sitemap.php        # Dynamic XML sitemap
├── .htaccess          # URL rewriting rules
└── robots.txt         # Search engine directives
```

---

## 🚀 Installation

### Prerequisites
- PHP 8.0 or higher
- MySQL 8.0+ or MariaDB 10.5+
- Apache with `mod_rewrite` enabled
- XAMPP (local) or cPanel (production)

### Step-by-Step Setup

#### 1. Clone the Repository
```bash
git clone https://github.com/your-username/shreebitu.git
cd shreebitu
```

#### 2. Place in Web Server Root
- **XAMPP**: Copy to `C:\xampp\htdocs\shreebitu\`
- **cPanel**: Upload to `public_html/shreebitu/` (or root if using a subdomain)

#### 3. Create the Database
```sql
CREATE DATABASE shreebitu_playstore CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
```

#### 4. Import the Schema
```bash
mysql -u root -p shreebitu_playstore < database.sql
```
Or import `database.sql` via phpMyAdmin.

#### 5. Configure Database Connection
Edit `db.php`:
```php
$host = '127.0.0.1';       // Use 'localhost' on cPanel
$dbname = 'shreebitu_playstore';
$user = 'root';             // Your MySQL username
$pass = '';                 // Your MySQL password
```

#### 6. Configure Base URL
Edit `config.php`:
```php
$base_url = '/shreebitu/';  // Change to '/' if deployed at domain root
```

#### 7. Set File Permissions
```bash
chmod 755 uploads/
chmod 755 uploads/profile_pics/
mkdir -p uploads/files && chmod 755 uploads/files/
```

#### 8. Enable mod_rewrite (if not already)
Ensure Apache has `mod_rewrite` enabled and `AllowOverride All` is set for the directory.

#### 9. Access the Application
```
http://localhost/shreebitu/
```

---

## 🗄️ Database Setup

The `database.sql` file creates **11 tables**:

| # | Table | Purpose |
|---|-------|---------|
| 1 | `users` | User accounts (admin + regular) |
| 2 | `categories` | App categories (Apps, Games, Windows, etc.) |
| 3 | `apps` | Core table — all submitted applications |
| 4 | `settings` | Key-value site settings |
| 5 | `downloads_log` | Download tracking per app |
| 6 | `reports` | User-filed content reports |
| 7 | `favorites` | User-app favorite relationships |
| 8 | `notifications` | System notifications to users |
| 9 | `activity_logs` | Full audit trail |
| 10 | `blocked_ips` | IP block list |
| 11 | `reviews` | User reviews with 1-5 star ratings |

### Default Seed Data
- **8 categories**: Apps, Notes, Presentations, Windows, Tools, Games, Social, Productivity
- **Admin account**: `admin@shreebitu.in` / `admin123`

> ⚠️ **Change the default admin password immediately after first login!**

---

## ⚙️ Configuration

### `config.php` — Key Settings
```php
$base_url = '/shreebitu/';     // Base path of the application
define('DEBUG_MODE', true);     // Set to false in production!
```

### Admin Panel Settings (`/admin/settings.php`)
| Setting | Description |
|---------|-------------|
| `maintenance_mode` | Enable/disable maintenance page for non-admins |
| `registration_enabled` | Allow/block new user registrations |
| `upload_enabled` | Allow/block new app submissions |
| `max_file_size` | Maximum upload size in MB (default: 100) |
| `allowed_extensions` | Comma-separated allowed file types (default: apk,pdf,ppt) |
| `approval_system` | If enabled, new apps require admin approval |
| `external_links_allowed` | Allow/block external download links |

---

## 📖 Usage Guide

### For Users
1. **Register** an account at `/auth/register.php`
2. **Login** at `/auth/login.php`
3. **Browse** apps on the homepage or by category
4. **Download** any approved app by clicking "Download Now"
5. **Submit** your own app at `/user/submit.php`
6. **Review** apps with a 1-5 star rating and comment
7. **Favorite** apps by clicking the heart icon
8. **Report** problematic content via the "Report Issue" link

### For Admins
1. Login with admin credentials
2. Access the **Admin Panel** via the dropdown menu or `/admin/dashboard.php`
3. Review and approve pending submissions at `/admin/apps.php`
4. Manage users, reviews, and reports from the sidebar

---

## 🔐 Admin Panel

Access: `/admin/dashboard.php` (requires admin role)

| Page | URL | Function |
|------|-----|----------|
| Dashboard | `/admin/dashboard.php` | System overview, stats, activity feed |
| Apps | `/admin/apps.php` | Approve, reject, edit, delete apps |
| Categories | `/admin/categories.php` | Add, edit, delete categories |
| Users | `/admin/users.php` | Ban, shadow-ban, change roles |
| Reviews | `/admin/reviews.php` | Moderate user reviews |
| Reports | `/admin/reports.php` | View and resolve content reports |
| Settings | `/admin/settings.php` | Site-wide configuration |
| Security | `/admin/security.php` | IP blocking, session management |
| Logs | `/admin/logs.php` | Activity audit trail |
| Backup | `/admin/backup.php` | Database export |

---

## 🔌 API Endpoints

| Endpoint | Method | Auth | Description |
|----------|--------|------|-------------|
| `user/favorites_action.php?app_id={id}` | GET | Login | Toggle favorite (returns JSON) |
| `user/toggle_favorite.php` | POST | Login + CSRF | Toggle favorite with CSRF (returns JSON) |
| `sitemap.xml` | GET | Public | Dynamic XML sitemap |
| `download.php?id={id}` | GET | Public | Download handler with tracking |

---

## 🛡️ Security

### Built-in Protections
- ✅ CSRF token validation on all POST forms
- ✅ bcrypt password hashing (`PASSWORD_DEFAULT`)
- ✅ PDO prepared statements (SQL injection prevention)
- ✅ Input sanitization via `htmlspecialchars()` + `strip_tags()`
- ✅ Session regeneration on login
- ✅ Server-side session token validation
- ✅ IP blocking system
- ✅ Rate limiting on submissions and reports
- ✅ File upload extension whitelist
- ✅ Shadow banning system
- ✅ Full activity logging with IP + user agent

### Production Checklist
- [ ] Set `DEBUG_MODE` to `false` in `config.php`
- [ ] Change default admin password
- [ ] Use HTTPS (SSL certificate)
- [ ] Set secure cookie flags in `php.ini`
- [ ] Restrict `/admin/`, `/includes/`, `/auth/` directories
- [ ] Add MIME type verification for file uploads
- [ ] Install HTMLPurifier for safe HTML rendering in descriptions
- [ ] Move `db.php` credentials to environment variables

---

## 🔍 SEO

- **Clean URLs**: `/category/apk`, `/whatsapp-download` via `.htaccess`
- **Dynamic Sitemap**: Auto-generated at `/sitemap.xml`
- **Open Graph**: Facebook/Twitter meta tags on all pages
- **Canonical URLs**: Prevents duplicate content issues
- **Robots.txt**: Blocks admin, auth, and includes directories
- **Custom SEO Fields**: Per-app meta title, description, and keywords (admin editor)

---

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/amazing-feature`
3. Commit changes: `git commit -m 'Add amazing feature'`
4. Push to branch: `git push origin feature/amazing-feature`
5. Open a Pull Request

---

## 📄 License

This project is proprietary software. All rights reserved.

---

<p align="center">
  <strong>MODFIRE</strong> — Built with ❤️ by ShreeBitu
</p>
