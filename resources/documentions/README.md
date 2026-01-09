# 🔐 Production-Ready Laravel Sanctum REST API

[![Laravel](https://img.shields.io/badge/Laravel-11.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

A **production-ready REST API** built with Laravel Sanctum, featuring comprehensive authentication, role-based authorization, security best practices, and extensive logging.

---

## 🎯 Features

### ✅ Authentication & Authorization
- **Laravel Sanctum** for API token authentication
- **Bearer token** authentication for mobile apps
- **Token rotation** on login (old tokens automatically revoked)
- **Role-based authorization** (Admin/User roles)
- Inactive user blocking

### ✅ Security
- **HTTPS enforcement** (production-ready)
- **Rate limiting** (5 login attempts/min, 60 API requests/min)
- **CORS configuration** with trusted origins only
- **Strict input validation** with Form Requests
- **Strong password requirements** (mixed case, numbers, symbols)
- **Mass assignment protection**
- **SQL injection prevention** (Eloquent ORM)
- **Activity logging** (failed logins, unauthorized access, admin actions)

### ✅ User Management (Admin Only)
- Create users with role assignment
- Update user information
- Delete users (soft delete)
- List users with pagination
- Admin self-protection (cannot delete/deactivate own account)

### ✅ API Features
- RESTful JSON API with versioning (`/api/v1`)
- Consistent error responses
- Proper HTTP status codes
- API Resources for response transformation
- Pagination support

---

## 📋 Requirements

- PHP 8.2 or higher
- Composer
- MySQL 8.0 or higher
- Redis (recommended for production)
- Nginx or Apache with SSL certificate

---

## 🚀 Quick Start

### 1. Installation

```bash
# Clone repository
git clone <repository-url>
cd laravelAI

# Install dependencies
composer install

# Setup environment
cp .env.example .env
php artisan key:generate

# Configure database in .env
# DB_DATABASE=your_database
# DB_USERNAME=your_username
# DB_PASSWORD=your_password

# Run migrations and seed
php artisan migrate:fresh --seed
```

### 2. Default Credentials

After seeding, use these credentials:

**Admin Account:**
- Email: `admin@example.com`
- Password: `Admin@123456`

**Regular User:**
- Email: `user@example.com`
- Password: `User@123456`

⚠️ **Change these in production!**

### 3. Start Development Server

```bash
php artisan serve
```

API available at: `http://localhost:8000/api/v1`

---

## 📚 API Documentation

Complete API documentation is available in [API_DOCUMENTATION.md](API_DOCUMENTATION.md)

### Quick Reference

#### Authentication
- `POST /api/v1/login` - Login and get token
- `POST /api/v1/logout` - Logout and revoke token

#### User Profile
- `GET /api/v1/profile` - Get authenticated user profile

#### User Management (Admin Only)
- `GET /api/v1/users` - List all users
- `POST /api/v1/users` - Create new user
- `PUT /api/v1/users/{id}` - Update user
- `DELETE /api/v1/users/{id}` - Delete user

---

## 🧪 Testing with Postman

Import the provided Postman collection:

1. Open Postman
2. Click **Import**
3. Select `postman_collection.json`
4. Update `base_url` variable to your API URL
5. Login to automatically set the Bearer token

---

## 🔒 Security Implementation Details

### 1. HTTPS Enforcement

**Web Server Configuration (Nginx):**

```nginx
server {
    listen 80;
    server_name api.yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name api.yourdomain.com;
    
    ssl_certificate /path/to/cert.pem;
    ssl_certificate_key /path/to/key.pem;
    
    # Laravel public directory
    root /var/www/html/laravelAI/public;
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 2. Rate Limiting

Configured in `AppServiceProvider`:
- **Login**: 5 attempts per minute per IP
- **API**: 60 requests per minute per user/IP

### 3. CORS Configuration

Edit `config/cors.php` to set allowed origins:

```php
'allowed_origins' => [
    env('FRONTEND_URL', 'https://yourmobileapp.com'),
],
```

### 4. Activity Logging

All security events are logged in `activity_logs` table:
- Failed login attempts
- Unauthorized access attempts
- Admin CRUD operations

**Query Activity Logs:**

```sql
-- Failed logins
SELECT * FROM activity_logs WHERE action = 'login_failed' ORDER BY created_at DESC;

-- Unauthorized access
SELECT * FROM activity_logs WHERE action = 'unauthorized_access' ORDER BY created_at DESC;

-- Admin actions
SELECT * FROM activity_logs WHERE action IN ('user_created', 'user_updated', 'user_deleted');
```

---

## 🏗️ Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   └── Api/V1/
│   │       ├── AuthController.php      # Login/Logout
│   │       ├── UserController.php      # Admin CRUD
│   │       └── ProfileController.php   # User profile
│   ├── Middleware/
│   │   ├── RoleMiddleware.php          # Role-based auth
│   │   └── EnsureUserIsActive.php      # Active user check
│   ├── Requests/
│   │   ├── LoginRequest.php            # Login validation
│   │   ├── StoreUserRequest.php        # Create user validation
│   │   └── UpdateUserRequest.php       # Update user validation
│   └── Resources/
│       ├── UserResource.php            # User JSON response
│       └── UserCollection.php          # User list response
├── Models/
│   ├── User.php                        # User model with Sanctum
│   └── ActivityLog.php                 # Activity logging
database/
├── migrations/
│   ├── create_users_table.php
│   ├── create_permission_tables.php
│   ├── create_personal_access_tokens_table.php
│   └── create_activity_logs_table.php
└── seeders/
    └── DatabaseSeeder.php              # Admin & roles seeder
routes/
└── api.php                             # API routes with middleware
```

---

## 🚀 Production Deployment

### Pre-Deployment Checklist

- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Generate strong `APP_KEY`
- [ ] Configure production database
- [ ] Set up SSL certificate (HTTPS)
- [ ] Configure CORS with trusted origins
- [ ] Change default admin credentials
- [ ] Set up Redis for cache/sessions
- [ ] Configure log rotation
- [ ] Set up database backups
- [ ] Enable firewall (ports 80, 443, 22)
- [ ] Set proper file permissions

### Optimization Commands

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev
```

### File Permissions

```bash
# Set ownership
chown -R www-data:www-data /var/www/html/laravelAI

# Set permissions
find /var/www/html/laravelAI -type f -exec chmod 644 {} \;
find /var/www/html/laravelAI -type d -exec chmod 755 {} \;

# Storage and cache need write permissions
chmod -R 775 storage bootstrap/cache
```

---

## 📊 Database Schema

### Users Table
- `id` - Primary key
- `name` - User's full name
- `email` - Unique email address
- `password` - Hashed password
- `is_active` - Account status
- `email_verified_at` - Email verification timestamp
- `created_at`, `updated_at` - Timestamps
- `deleted_at` - Soft delete timestamp

### Roles & Permissions
Managed by Spatie Laravel Permission package:
- `roles` table
- `permissions` table
- `model_has_roles` pivot table

### Activity Logs
- `id` - Primary key
- `user_id` - Foreign key to users
- `action` - Action performed
- `ip_address` - Request IP
- `user_agent` - Request user agent
- `description` - Action description
- `metadata` - Additional JSON data
- `created_at`, `updated_at` - Timestamps

---

## 🔧 Environment Variables

### Required Production Variables

```env
# Application
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:YOUR_GENERATED_KEY
APP_URL=https://api.yourdomain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_secure_password

# CORS
FRONTEND_URL=https://yourmobileapp.com
SANCTUM_STATEFUL_DOMAINS=yourmobileapp.com

# Cache & Session (Production)
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Logging
LOG_CHANNEL=stack
LOG_STACK=daily
LOG_LEVEL=info
```

---

## 🧪 Testing

### Manual Testing

1. **Test Authentication:**
```bash
# Login
curl -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"Admin@123456"}'

# Use returned token for subsequent requests
```

2. **Test Rate Limiting:**
```bash
# Try login 6 times rapidly to trigger rate limit
for i in {1..6}; do
  curl -X POST http://localhost:8000/api/v1/login \
    -H "Content-Type: application/json" \
    -d '{"email":"test@test.com","password":"wrong"}';
done
```

3. **Test Authorization:**
```bash
# Login as regular user and try to access admin endpoint
curl -X GET http://localhost:8000/api/v1/users \
  -H "Authorization: Bearer USER_TOKEN"
# Should return 403 Forbidden
```

---

## 📝 License

This project is licensed under the MIT License.

---

## 🆘 Support & Contributing

For issues, questions, or contributions:

1. Check existing documentation
2. Review [API_DOCUMENTATION.md](API_DOCUMENTATION.md)
3. Check Laravel documentation: https://laravel.com/docs
4. Sanctum documentation: https://laravel.com/docs/sanctum

---

## 🙏 Acknowledgments

Built with:
- [Laravel](https://laravel.com) - PHP Framework
- [Laravel Sanctum](https://laravel.com/docs/sanctum) - API Authentication
- [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission) - Role Management

---

**🔥 Production-Ready | 🔒 Secure | 📱 Mobile-Optimized**
