# 🔐 Laravel Sanctum REST API - Production Ready

## 📋 Table of Contents
- [Overview](#overview)
- [Security Features](#security-features)
- [Installation & Setup](#installation--setup)
- [API Endpoints](#api-endpoints)
- [Authentication](#authentication)
- [Error Handling](#error-handling)
- [Rate Limiting](#rate-limiting)
- [Production Deployment](#production-deployment)

---

## 🎯 Overview

This is a **production-ready Laravel REST API** built with:
- **Framework**: Laravel 11.x
- **Authentication**: Laravel Sanctum (Bearer tokens)
- **Authorization**: Spatie Laravel Permission (role-based)
- **Database**: MySQL with Eloquent ORM
- **Security**: HTTPS enforcement, rate limiting, CORS, input validation

---

## 🛡️ Security Features

### ✅ Implemented Security Measures

1. **HTTPS Enforcement**
   - All API requests must use HTTPS in production
   - Configure web server (Nginx/Apache) to redirect HTTP to HTTPS

2. **Authentication & Authorization**
   - Laravel Sanctum with Bearer tokens
   - Token rotation on login (old tokens revoked)
   - Token revocation on logout
   - Role-based access control (admin/user)

3. **Rate Limiting**
   - Login: 5 attempts per minute
   - API endpoints: 60 requests per minute
   - IP-based throttling

4. **Input Validation**
   - Strict validation using Form Requests
   - Protection against mass assignment
   - SQL injection prevention (Eloquent ORM)
   - XSS protection (automatic escaping)

5. **CORS Configuration**
   - Only trusted origins allowed
   - Authorization headers enabled
   - Credentials support

6. **Logging & Monitoring**
   - Failed login attempts logged
   - Unauthorized access attempts logged
   - Admin CRUD actions logged
   - Activity stored in `activity_logs` table

7. **Password Security**
   - Minimum 8 characters
   - Mixed case required
   - Numbers and symbols required
   - Compromised password check
   - Bcrypt hashing

8. **Account Security**
   - Inactive user blocking
   - Soft deletion of users
   - Token revocation on password change
   - Admin self-protection (can't delete/deactivate own account)

---

## 📦 Installation & Setup

### 1. Clone & Install Dependencies

```bash
# Clone the repository
git clone <repository-url>
cd laravelAI

# Install PHP dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 2. Configure Environment

Edit `.env` file:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

# CORS - Add your mobile app domain
FRONTEND_URL=https://yourmobileapp.com
SANCTUM_STATEFUL_DOMAINS=yourmobileapp.com

# Cache & Session (use Redis in production)
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

### 3. Run Migrations & Seed

```bash
# Run migrations
php artisan migrate

# Seed database with initial roles and users
php artisan db:seed
```

**Default Credentials:**
- **Admin**: admin@example.com / Admin@123456
- **User**: user@example.com / User@123456

### 4. Configure Web Server (Nginx)

**HTTPS Enforcement** - Add to your Nginx config:

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

---

## 🚀 API Endpoints

### Base URL
```
https://api.yourdomain.com/api/v1
```

---

## 🔑 Authentication

### POST /api/v1/login

Login and receive authentication token.

**Rate Limit**: 5 requests per minute

**Request:**
```bash
curl -X POST https://api.yourdomain.com/api/v1/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "Admin@123456"
  }'
```

**Response (200 OK):**
```json
{
  "message": "Login successful",
  "user": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@example.com",
    "is_active": true,
    "roles": ["admin"],
    "email_verified_at": "2026-01-07T06:50:32.000000Z",
    "created_at": "2026-01-07T06:50:32.000000Z",
    "updated_at": "2026-01-07T06:50:32.000000Z"
  },
  "token": "1|AbCdEfGhIjKlMnOpQrStUvWxYz1234567890",
  "token_type": "Bearer"
}
```

**Error Responses:**

- **401 Unauthorized** - Invalid credentials
```json
{
  "message": "The provided credentials are incorrect.",
  "errors": {
    "email": ["The provided credentials are incorrect."]
  }
}
```

- **403 Forbidden** - Account inactive
```json
{
  "message": "Your account has been deactivated. Please contact support."
}
```

- **429 Too Many Requests** - Rate limit exceeded
```json
{
  "message": "Too many login attempts. Please try again in 60 seconds."
}
```

---

### POST /api/v1/logout

Logout and revoke current token.

**Authentication**: Required

**Request:**
```bash
curl -X POST https://api.yourdomain.com/api/v1/logout \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Response (200 OK):**
```json
{
  "message": "Logged out successfully"
}
```

---

## 👤 User Profile

### GET /api/v1/profile

Get authenticated user's profile.

**Authentication**: Required  
**Authorization**: All authenticated users

**Request:**
```bash
curl -X GET https://api.yourdomain.com/api/v1/profile \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Response (200 OK):**
```json
{
  "user": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@example.com",
    "is_active": true,
    "roles": ["admin"],
    "email_verified_at": "2026-01-07T06:50:32.000000Z",
    "created_at": "2026-01-07T06:50:32.000000Z",
    "updated_at": "2026-01-07T06:50:32.000000Z"
  }
}
```

---

## 👥 User Management (Admin Only)

### GET /api/v1/users

List all users with pagination.

**Authentication**: Required  
**Authorization**: Admin only

**Query Parameters:**
- `per_page` (optional): Items per page (default: 15, max: 100)

**Request:**
```bash
curl -X GET "https://api.yourdomain.com/api/v1/users?per_page=20" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN"
```

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Admin User",
      "email": "admin@example.com",
      "is_active": true,
      "roles": ["admin"],
      "email_verified_at": "2026-01-07T06:50:32.000000Z",
      "created_at": "2026-01-07T06:50:32.000000Z",
      "updated_at": "2026-01-07T06:50:32.000000Z"
    }
  ],
  "meta": {
    "total": 2,
    "per_page": 20,
    "current_page": 1,
    "last_page": 1
  }
}
```

---

### POST /api/v1/users

Create a new user.

**Authentication**: Required  
**Authorization**: Admin only

**Request:**
```bash
curl -X POST https://api.yourdomain.com/api/v1/users \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "SecurePass@123",
    "password_confirmation": "SecurePass@123",
    "role": "user",
    "is_active": true
  }'
```

**Request Body:**
- `name` (required): User's full name (letters and spaces only)
- `email` (required): Valid email address (must be unique)
- `password` (required): Min 8 chars, mixed case, numbers, symbols
- `password_confirmation` (required): Must match password
- `role` (required): Either "admin" or "user"
- `is_active` (optional): Boolean (default: true)

**Response (201 Created):**
```json
{
  "message": "User created successfully",
  "user": {
    "id": 3,
    "name": "John Doe",
    "email": "john@example.com",
    "is_active": true,
    "roles": ["user"],
    "email_verified_at": null,
    "created_at": "2026-01-07T07:00:00.000000Z",
    "updated_at": "2026-01-07T07:00:00.000000Z"
  }
}
```

**Validation Errors (422 Unprocessable Entity):**
```json
{
  "message": "The email has already been taken.",
  "errors": {
    "email": ["The email has already been taken."],
    "password": ["The password must contain at least one symbol."]
  }
}
```

---

### PUT /api/v1/users/{id}

Update an existing user.

**Authentication**: Required  
**Authorization**: Admin only

**Request:**
```bash
curl -X PUT https://api.yourdomain.com/api/v1/users/3 \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN" \
  -d '{
    "name": "John Smith",
    "email": "johnsmith@example.com",
    "role": "admin",
    "is_active": true
  }'
```

**Request Body (all fields optional):**
- `name`: User's full name
- `email`: Valid email address
- `password`: New password (requires password_confirmation)
- `password_confirmation`: Must match password
- `role`: Either "admin" or "user"
- `is_active`: Boolean

**Response (200 OK):**
```json
{
  "message": "User updated successfully",
  "user": {
    "id": 3,
    "name": "John Smith",
    "email": "johnsmith@example.com",
    "is_active": true,
    "roles": ["admin"],
    "email_verified_at": null,
    "created_at": "2026-01-07T07:00:00.000000Z",
    "updated_at": "2026-01-07T07:05:00.000000Z"
  }
}
```

**Notes:**
- Admin cannot deactivate their own account
- Password change revokes all user tokens
- Activity is logged

---

### DELETE /api/v1/users/{id}

Delete a user (soft delete).

**Authentication**: Required  
**Authorization**: Admin only

**Request:**
```bash
curl -X DELETE https://api.yourdomain.com/api/v1/users/3 \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN"
```

**Response (200 OK):**
```json
{
  "message": "User deleted successfully"
}
```

**Error (404 Not Found):**
```json
{
  "message": "User not found"
}
```

**Error (422 Unprocessable Entity):**
```json
{
  "message": "You cannot delete your own account"
}
```

**Notes:**
- Admin cannot delete their own account
- User tokens are revoked on deletion
- Soft delete (user can be restored from database)
- Activity is logged

---

## ⚠️ Error Handling

All API errors follow a consistent JSON format:

### Standard Error Response
```json
{
  "message": "Error description",
  "errors": {
    "field_name": ["Validation error message"]
  }
}
```

### HTTP Status Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 201 | Created |
| 401 | Unauthenticated |
| 403 | Forbidden / Unauthorized |
| 404 | Not Found |
| 422 | Validation Error |
| 429 | Too Many Requests |
| 500 | Server Error |

---

## 🚦 Rate Limiting

### Login Endpoint
- **Limit**: 5 requests per minute per IP
- **Lockout**: 60 seconds after limit exceeded
- **Header**: `X-RateLimit-Limit`, `X-RateLimit-Remaining`

### API Endpoints
- **Limit**: 60 requests per minute per user/IP
- **Header**: `X-RateLimit-Limit`, `X-RateLimit-Remaining`

**Rate Limit Response (429):**
```json
{
  "message": "Too many requests. Please try again in 30 seconds."
}
```

---

## 🚀 Production Deployment

### Security Checklist

- [ ] Set `APP_ENV=production` and `APP_DEBUG=false`
- [ ] Use strong `APP_KEY` (generated with `php artisan key:generate`)
- [ ] Configure HTTPS on web server (Nginx/Apache)
- [ ] Set up SSL certificate (Let's Encrypt recommended)
- [ ] Configure CORS with trusted origins only
- [ ] Use Redis for cache and sessions
- [ ] Set up database backups
- [ ] Configure log rotation
- [ ] Set up monitoring (Laravel Telescope, Sentry, etc.)
- [ ] Enable queue workers for background jobs
- [ ] Restrict database access to localhost
- [ ] Change default admin credentials
- [ ] Set up firewall rules (allow 80, 443, 22 only)
- [ ] Disable directory listing in web server
- [ ] Set proper file permissions (755 for dirs, 644 for files)

### Performance Optimization

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

### Monitor Activity Logs

```sql
-- Check failed login attempts
SELECT * FROM activity_logs 
WHERE action = 'login_failed' 
ORDER BY created_at DESC 
LIMIT 50;

-- Check unauthorized access attempts
SELECT * FROM activity_logs 
WHERE action = 'unauthorized_access' 
ORDER BY created_at DESC 
LIMIT 50;

-- Check admin actions
SELECT * FROM activity_logs 
WHERE action IN ('user_created', 'user_updated', 'user_deleted') 
ORDER BY created_at DESC 
LIMIT 50;
```

---

## 📝 License

This API is built with Laravel, which is open-sourced software licensed under the MIT license.

---

## 🆘 Support

For issues or questions, please contact your development team or refer to:
- [Laravel Documentation](https://laravel.com/docs)
- [Laravel Sanctum Documentation](https://laravel.com/docs/sanctum)
- [Spatie Permission Documentation](https://spatie.be/docs/laravel-permission)
