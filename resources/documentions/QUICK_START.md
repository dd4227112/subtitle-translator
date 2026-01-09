# 🚀 QUICK START GUIDE

## Get the API Running in 5 Minutes

This guide will get your Laravel Sanctum API up and running quickly.

---

## ⚡ Prerequisites

Make sure you have:
- PHP 8.2+ installed
- Composer installed
- MySQL running
- Git (if cloning from repository)

---

## 📝 Step 1: Install & Configure (2 minutes)

```bash
# Navigate to project directory
cd /var/www/html/laravelAI

# Install dependencies (if not already done)
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Edit .env and set your database credentials
nano .env
```

**Configure these in .env:**
```env
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password
```

---

## 🗄️ Step 2: Setup Database (1 minute)

```bash
# Create database (MySQL)
mysql -u root -p -e "CREATE DATABASE your_database_name;"

# Run migrations and seed data
php artisan migrate:fresh --seed
```

**You'll see:**
```
✅ Database seeded successfully!

Default Credentials:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Admin:
  Email: admin@example.com
  Password: Admin@123456

Regular User:
  Email: user@example.com
  Password: User@123456
```

---

## 🎯 Step 3: Start Server (30 seconds)

```bash
# Start Laravel development server
php artisan serve
```

**Server starts at:** `http://localhost:8000`

---

## 🧪 Step 4: Test API (1 minute)

### Test Login (Get Token)

```bash
curl -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "Admin@123456"
  }'
```

**Copy the token from the response!**

### Test Profile (Use Token)

```bash
# Replace YOUR_TOKEN with the token from login response
curl -X GET http://localhost:8000/api/v1/profile \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Test Admin Endpoint (List Users)

```bash
curl -X GET http://localhost:8000/api/v1/users \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## 🎨 Alternative: Use Postman (30 seconds)

1. Open Postman
2. Click **Import**
3. Select `postman_collection.json` from project root
4. Set `base_url` to `http://localhost:8000/api/v1`
5. Send the **Login** request
6. Token is auto-saved, test other endpoints!

---

## ✅ Verify Everything Works

### Check Routes
```bash
php artisan route:list --path=api
```

**You should see:**
```
POST   api/v1/login
POST   api/v1/logout
GET    api/v1/profile
GET    api/v1/users
POST   api/v1/users
PUT    api/v1/users/{user}
DELETE api/v1/users/{user}
```

### Check Database
```bash
mysql -u root -p your_database_name -e "SELECT email, name FROM users;"
```

**You should see:**
```
+---------------------+---------------+
| email               | name          |
+---------------------+---------------+
| admin@example.com   | Admin User    |
| user@example.com    | Regular User  |
+---------------------+---------------+
```

---

## 🎯 API Endpoints Summary

### Public (No Auth Required)
| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/v1/login` | POST | Login and get token |

### Authenticated (All Users)
| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/v1/logout` | POST | Logout and revoke token |
| `/api/v1/profile` | GET | Get your profile |

### Admin Only
| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/v1/users` | GET | List all users |
| `/api/v1/users` | POST | Create user |
| `/api/v1/users/{id}` | PUT | Update user |
| `/api/v1/users/{id}` | DELETE | Delete user |

---

## 🔑 Test Credentials

### Admin Account
```
Email: admin@example.com
Password: Admin@123456
```

### Regular User Account
```
Email: user@example.com
Password: User@123456
```

⚠️ **Important:** Change these passwords in production!

---

## 🐛 Troubleshooting

### Problem: "Connection refused" when testing API
**Solution:** Make sure Laravel server is running
```bash
php artisan serve
```

### Problem: Database connection error
**Solution:** Check .env database credentials
```bash
# Test database connection
mysql -u your_username -p
# Then type your password
```

### Problem: "Class not found" errors
**Solution:** Regenerate autoload files
```bash
composer dump-autoload
```

### Problem: Migration errors
**Solution:** Drop and recreate database
```bash
php artisan migrate:fresh --seed
```

### Problem: Permission denied errors
**Solution:** Fix storage permissions
```bash
chmod -R 775 storage bootstrap/cache
```

---

## 📚 Next Steps

1. **Read Full Documentation:** [API_DOCUMENTATION.md](API_DOCUMENTATION.md)
2. **Check Security:** [SECURITY_CHECKLIST.md](SECURITY_CHECKLIST.md)
3. **Review Implementation:** [IMPLEMENTATION_SUMMARY.md](IMPLEMENTATION_SUMMARY.md)
4. **Production Setup:** [README.md](README.md#production-deployment)

---

## 💡 Common Use Cases

### Create a New User (Admin)
```bash
curl -X POST http://localhost:8000/api/v1/users \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "SecurePass@123",
    "password_confirmation": "SecurePass@123",
    "role": "user"
  }'
```

### Update User
```bash
curl -X PUT http://localhost:8000/api/v1/users/3 \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN" \
  -d '{
    "name": "John Smith",
    "is_active": false
  }'
```

### Delete User
```bash
curl -X DELETE http://localhost:8000/api/v1/users/3 \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN"
```

### Logout
```bash
curl -X POST http://localhost:8000/api/v1/logout \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## 🎉 You're All Set!

Your Laravel Sanctum API is now running and ready for development.

**What's Included:**
- ✅ Secure authentication with token rotation
- ✅ Role-based authorization (Admin/User)
- ✅ Rate limiting (5 login attempts/min, 60 API requests/min)
- ✅ Activity logging (failed logins, unauthorized access)
- ✅ Input validation with security rules
- ✅ Complete API documentation
- ✅ Postman collection for testing

**Need Help?**
- Check [API_DOCUMENTATION.md](API_DOCUMENTATION.md) for detailed API reference
- Review [SECURITY_CHECKLIST.md](SECURITY_CHECKLIST.md) before production
- Read [README.md](README.md) for comprehensive overview

---

**Happy Coding! 🚀**
