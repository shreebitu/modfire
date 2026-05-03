# ShreeBitu Project Database Structure

This document outlines the database schema for the **ShreeBitu Playstore** platform.

## Database Information
- **Database Name**: `shreebitu_playstore`
- **Host**: `localhost`
- **User**: `root`
- **Password**: `(empty)`

---

## Table Details

### 1. `users`
Stores user and administrator accounts.
| Column | Type | Description |
| :--- | :--- | :--- |
| `id` | INT (PK) | Unique User ID |
| `username` | VARCHAR(50) | Unique username |
| `email` | VARCHAR(100) | Unique email address |
| `password` | VARCHAR(255) | Hashed password |
| `role` | ENUM | 'user' or 'admin' |
| `created_at` | TIMESTAMP | Registration date |

### 2. `apps`
Contains all listed applications/software.
| Column | Type | Description |
| :--- | :--- | :--- |
| `id` | INT (PK) | Unique App ID |
| `name` | VARCHAR(100) | App Name |
| `description` | TEXT | App description (HTML supported) |
| `logo` | VARCHAR(255) | Path to logo image |
| `apk_link` | VARCHAR(255) | Direct download link |
| `status` | ENUM | 'pending', 'approved', 'rejected' |
| `category` | VARCHAR(50) | App category (e.g., Tools, Games) |
| `user_id` | INT (FK) | ID of the user who uploaded it |
| `file_size` | VARCHAR(50) | Size of the file |
| `os_compatible` | VARCHAR(100)| Compatible OS versions |
| `developer` | VARCHAR(100) | Name of the developer |
| `created_at` | TIMESTAMP | Upload date |

### 3. `categories`
Manages website categories.
| Column | Type | Description |
| :--- | :--- | :--- |
| `id` | INT (PK) | Unique Category ID |
| `name` | VARCHAR(50) | Category name |
| `icon` | VARCHAR(50) | Material icon name |

### 4. `settings`
Global website configuration toggles.
| Column | Type | Description |
| :--- | :--- | :--- |
| `id` | INT (PK) | Setting ID |
| `setting_key` | VARCHAR(50) | Unique key (e.g., 'maintenance_mode') |
| `setting_value` | TEXT | Value of the setting |

### 5. `activity_logs`
Tracks admin and user actions for security.
| Column | Type | Description |
| :--- | :--- | :--- |
| `id` | INT (PK) | Log ID |
| `user_id` | INT | User who performed action |
| `action` | VARCHAR(100) | Type of action |
| `details` | TEXT | Description of change |
| `ip_address` | VARCHAR(45) | User's IP address |
| `created_at` | TIMESTAMP | Time of action |

### 6. `reviews`
Stores user ratings and comments for apps.
| Column | Type | Description |
| :--- | :--- | :--- |
| `id` | INT (PK) | Review ID |
| `user_id` | INT (FK) | User who wrote the review |
| `app_id` | INT (FK) | App being reviewed |
| `rating` | INT | Star rating (1-5) |
| `comment` | TEXT | Review text |
| `created_at` | TIMESTAMP | Review date |

---

## How it was initialized (Process)

The database was set up following these steps:

1.  **Creation**: The database `shreebitu_playstore` was created using MySQL CLI:
    ```sql
    CREATE DATABASE shreebitu_playstore;
    ```
2.  **Schema Import**: Initial tables were imported from `database.sql`.
3.  **Expansion**: Additional columns (`file_size`, `developer`, etc.) and management tables (`settings`, `activity_logs`) were added to support the Super Admin panel and rich app details.
4.  **Seeding**: Sample data for 10 high-quality apps was injected using the `seed_db.php` script to populate the frontend.
5.  **Config Sync**: `db.php` was updated to point to the correct database name to resolve the connection failure.

---

> [!TIP]
> Keep the `database.sql` file updated with any future schema changes (like new tables or columns) to ensure easy deployment in other environments.
