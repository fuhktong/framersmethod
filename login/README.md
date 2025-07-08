# Login System Setup

## 🔐 Simple ENV-based Authentication

This login system uses environment variables for credentials - no database required!

## Setup Instructions

### 1. Add to your `.env` file:
```env
# Admin Login Credentials
ADMIN_USERNAME=admin
ADMIN_PASSWORD=your_secure_password_here
```

### 2. File Structure
```
framersmethod/
├── login/
│   ├── auth.php          # Authentication handler
│   ├── auth_check.php    # Protection for pages
│   ├── login.php         # Login form
│   └── logout.php        # Logout handler
└── emailservice/
    ├── index.php         # Protected email service pages
    ├── campaigns.php
    └── ... (all protected)
```

### 3. How it Works
- **login.php** - Beautiful login form with validation
- **auth.php** - Validates credentials against ENV variables
- **auth_check.php** - Protects pages (include at top of protected files)
- **logout.php** - Clears session and redirects

### 4. Security Features
- ✅ **Rate Limiting** - 5 failed attempts = 15 minute lockout
- ✅ **Session Security** - Session regeneration, CSRF tokens
- ✅ **Timeout Protection** - 24 hour session, 2 hour activity timeout
- ✅ **Secure Logout** - Complete session cleanup
- ✅ **XSS Protection** - All output properly escaped

### 5. Usage
```php
<?php
// Protect pages in emailservice with this at the top:
require_once '../login/auth_check.php';
$currentUser = getCurrentUser();
?>

// Or for root-level pages:
require_once 'login/auth_check.php';
```

### 6. Navigation Integration
All email service pages now include:
- Welcome message with username
- Logout button
- Automatic redirect to login if not authenticated

## Default Credentials
- **Username:** `admin` 
- **Password:** Set in your `.env` file

**⚠️ Important:** Change the default password in your `.env` file immediately!

## Access URLs
- **Standard Login:** `/login/login.php`
- **Hidden Login:** Double-click "New Mexico" on `/team` page
- **Quick Admin:** `/admin.php` (redirects to email service)
- **Email Service Dashboard:** `/emailservice/index.php` (redirects to login if not authenticated)

## Session Management
- **Session Duration:** 24 hours
- **Activity Timeout:** 2 hours of inactivity
- **Rate Limiting:** 5 failed attempts per IP address
- **Lockout Duration:** 15 minutes

The system automatically handles session validation and redirects on all protected pages.

## 🕵️ Hidden Login Feature

**Trigger:** Double-click on "New Mexico" text in the team page (`/team`)

**Features:**
- ✅ **Completely Invisible** - Zero traces in HTML source code
- ✅ **Precise Targeting** - Only works when clicking exactly on "New Mexico" text
- ✅ **Secure Overlay** - Full-screen login modal with backdrop blur
- ✅ **Same Security** - Uses identical authentication as standard login
- ✅ **Smooth UX** - Animations, error handling, ESC key to close
- ✅ **Mobile Friendly** - Works on all devices

**How it Works:**
1. Visit the team page (`/team`)
2. Double-click on "New Mexico" in Dustin Taylor's bio
3. Hidden login overlay appears
4. Enter credentials and login
5. Automatically redirected to email service dashboard

**Security Notes:**
- **Zero HTML traces** - completely undetectable even in inspect element
- **Precise detection** - only triggers on exact "New Mexico" text clicks
- Uses same rate limiting as standard login
- Same session management and timeouts
- No additional security risks - just hidden access point
- Falls back to standard login for all other authentication needs

This provides a secret admin entrance that's memorable for you but completely invisible to anyone inspecting the code!