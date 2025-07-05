# Cambodia Jobs & News Platform - Development Roadmap

## Project Overview

A comprehensive web application for Cambodia that combines local news content with job postings, specifically targeting English teachers and expat workers. The platform uses a credit-based system for job postings and shares revenue with content writers based on article views.

## Tech Stack

- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Backend**: PHP 8+
- **Database**: MySQL 8.0+
- **Server**: Apache/Nginx

## Development Phases

### **PHASE 1: Foundation - User Authentication & Basic User Management**

**Priority**: Build First
**Timeline**: Weeks 1-2

#### Core Features:

- User registration system with user type selection (writer, school, teacher, admin)
- 6-digit email verification code system (console log during development)
- Login/logout functionality with session management
- Password reset via 6-digit email verification code
- Basic user profiles with type-specific fields
- User type differentiation and permissions
- Simple role-based dashboard routing

#### Database Tables Required:

```sql
-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    user_type ENUM('writer', 'school', 'teacher', 'admin') NOT NULL,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    profile_data JSON,
    email_verified BOOLEAN DEFAULT FALSE,
    verification_code VARCHAR(6),
    verification_expires DATETIME,
    reset_code VARCHAR(6),
    reset_expires DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- User sessions table
CREATE TABLE user_sessions (
    id VARCHAR(128) PRIMARY KEY,
    user_id INT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

#### Pages to Build:

- `/register.php` - Registration form with user type selection
- `/verify-email.php` - 6-digit verification code form
- `/login.php` - Login form
- `/logout.php` - Logout handler
- `/forgot-password.php` - Password reset request (sends 6-digit code)
- `/reset-password.php` - Password reset form with code verification
- `/dashboard/` - Role-based dashboard routing
- `/profile.php` - Basic profile management
- `/resend-verification.php` - Resend verification code option

#### Backend Functions Required:

- `register_user($email, $password, $user_type, $profile_data)`
- `generate_verification_code()` - Creates 6-digit random code
- `send_verification_email($user_id, $code)` - Logs to console during development
- `verify_email_code($user_id, $code)`
- `authenticate_user($email, $password)` - Only allows verified users to login
- `request_password_reset($email)` - Generates and sends 6-digit reset code
- `verify_reset_code($email, $code)`
- `reset_password($email, $code, $new_password)`
- `resend_verification_code($user_id)`
- `check_user_permissions($user_id, $required_role)`
- `is_logged_in()`
- `get_current_user()`
- `cleanup_expired_codes()` - Remove expired verification/reset codes

#### Security Features:

- Password hashing with PHP password_hash()
- CSRF protection for all forms
- Input sanitization and validation
- Session security (httponly, secure flags)
- Rate limiting for login attempts and code generation
- 6-digit verification codes expire after 15 minutes
- Maximum 3 verification code attempts before requiring new code generation
- Development mode: Log verification codes to console instead of sending emails

---

### **PHASE 2: Core Content System**

**Priority**: Second
**Timeline**: Weeks 3-4

#### Core Features:

- Writers can submit articles with rich text editor
- Basic admin approval workflow (draft → submitted → published/rejected)
- Article publishing and public display
- Simple categorization system
- Basic view tracking for articles
- Public article browsing and reading

#### Database Tables Required:

```sql
-- Articles table
CREATE TABLE articles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE,
    content TEXT NOT NULL,
    excerpt TEXT,
    status ENUM('draft', 'submitted', 'published', 'rejected') DEFAULT 'draft',
    category VARCHAR(100),
    featured_image VARCHAR(255),
    meta_description TEXT,
    views INT DEFAULT 0,
    published_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Article views tracking
CREATE TABLE article_views (
    id INT PRIMARY KEY AUTO_INCREMENT,
    article_id INT NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE
);

-- Article categories
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### Pages to Build:

- `/articles/` - Public article listing
- `/articles/[slug]` - Individual article view
- `/writer/submit-article.php` - Article submission form
- `/writer/my-articles.php` - Writer's article management
- `/admin/articles.php` - Admin article moderation
- `/category/[slug]` - Category-based article listing

#### Backend Functions Required:

- `submit_article($user_id, $title, $content, $category)`
- `publish_article($article_id)`
- `reject_article($article_id, $reason)`
- `track_article_view($article_id, $ip_address)`
- `get_articles($status, $category, $limit, $offset)`
- `generate_article_slug($title)`

---

### **PHASE 3: Basic Job Board**

**Priority**: Third
**Timeline**: Weeks 5-6

#### Core Features:

- Schools can post basic job listings (free initially)
- Job browsing and search for teachers
- Basic application system (email contact)
- Job categories and location filtering
- Job expiration system

#### Database Tables Required:

```sql
-- Jobs table
CREATE TABLE jobs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    employer_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    job_type ENUM('teaching', 'hospitality', 'tourism', 'it', 'ngo', 'other') DEFAULT 'teaching',
    employment_type ENUM('full_time', 'part_time', 'contract', 'temporary'),
    salary_min INT,
    salary_max INT,
    currency VARCHAR(3) DEFAULT 'USD',
    location VARCHAR(100),
    requirements TEXT,
    benefits TEXT,
    contact_email VARCHAR(255),
    contact_phone VARCHAR(20),
    application_deadline DATE,
    status ENUM('active', 'expired', 'filled', 'paused') DEFAULT 'active',
    views INT DEFAULT 0,
    applications_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employer_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Job applications
CREATE TABLE job_applications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    job_id INT NOT NULL,
    applicant_id INT NOT NULL,
    cover_letter TEXT,
    resume_file VARCHAR(255),
    status ENUM('pending', 'reviewed', 'accepted', 'rejected') DEFAULT 'pending',
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    FOREIGN KEY (applicant_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_application (job_id, applicant_id)
);
```

#### Pages to Build:

- `/jobs/` - Public job listings
- `/jobs/[id]` - Individual job view and application
- `/school/post-job.php` - Job posting form
- `/school/my-jobs.php` - School's job management
- `/teacher/my-applications.php` - Teacher's application tracking
- `/jobs/search.php` - Advanced job search

#### Backend Functions Required:

- `post_job($employer_id, $job_data)`
- `apply_to_job($job_id, $applicant_id, $cover_letter)`
- `search_jobs($filters, $limit, $offset)`
- `expire_old_jobs()`
- `track_job_view($job_id, $ip_address)`
- `get_applications_for_job($job_id)`

---

### **PHASE 4: Monetization - Credit System & Payments**

**Priority**: Fourth
**Timeline**: Weeks 7-8

#### Core Features:

- Credit purchasing system with packages (10/$60, 15/$90, 20/$120)
- Credit-based job posting (Silver/10, Gold/15, Platinum/20 credits)
- Payment integration (PayPal/Stripe)
- Credit transaction tracking
- Job tier system with different visibility/features

#### Database Tables Required:

```sql
-- Credit packages
CREATE TABLE credit_packages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    credits INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'USD',
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Credit transactions
CREATE TABLE credit_transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    amount INT NOT NULL,
    transaction_type ENUM('purchase', 'job_post', 'resume_view', 'refund', 'admin_adjustment'),
    description VARCHAR(255),
    reference_id VARCHAR(255),
    payment_method VARCHAR(50),
    payment_status ENUM('pending', 'completed', 'failed', 'refunded'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Payment records
CREATE TABLE payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'USD',
    payment_method VARCHAR(50),
    payment_provider VARCHAR(50),
    provider_transaction_id VARCHAR(255),
    status ENUM('pending', 'completed', 'failed', 'refunded'),
    metadata JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Update jobs table to include tier
ALTER TABLE jobs ADD COLUMN tier ENUM('silver', 'gold', 'platinum') DEFAULT 'silver';
ALTER TABLE jobs ADD COLUMN featured BOOLEAN DEFAULT FALSE;
ALTER TABLE jobs ADD COLUMN credits_cost INT DEFAULT 10;
```

#### Pages to Build:

- `/credits/purchase.php` - Credit purchase page
- `/credits/history.php` - Credit transaction history
- `/payment/process.php` - Payment processing
- `/payment/success.php` - Payment success page
- `/payment/cancel.php` - Payment cancellation page
- `/school/post-job.php` - Updated with credit system

#### Backend Functions Required:

- `purchase_credits($user_id, $package_id, $payment_data)`
- `deduct_credits($user_id, $amount, $description)`
- `get_user_credits($user_id)`
- `process_payment($payment_data)`
- `create_job_with_credits($employer_id, $job_data, $tier)`
- `refund_credits($transaction_id, $reason)`

---

### **PHASE 5: Advanced Features - Resume Database System**

**Priority**: Fifth
**Timeline**: Weeks 9-10

#### Core Features:

- Teacher resume uploads with file management
- Resume search and filtering for schools
- Credit-based resume viewing system
- Resume management dashboard for teachers
- Privacy controls for resume visibility

---

### **PHASE 6: Revenue Sharing - Writer Analytics & Payment System**

**Priority**: Sixth
**Timeline**: Weeks 11-12

#### Core Features:

- Detailed article view tracking and analytics
- Revenue calculation based on writer performance
- Monthly payment processing for writers
- Writer analytics dashboard with earnings tracking
- Revenue distribution algorithm (50% of job posting revenue)

---

### **PHASE 7: Admin Tools & Analytics**

**Priority**: Seventh
**Timeline**: Weeks 13-14

#### Core Features:

- Comprehensive admin dashboard
- Site-wide analytics and reporting
- Content moderation tools
- User management and roles
- Financial reporting and revenue tracking

---

### **PHASE 8: Advertising System**

**Priority**: Eighth
**Timeline**: Weeks 15-16

#### Core Features:

- Ad placement zones throughout the site
- Ad management dashboard for advertisers
- Ad revenue tracking and reporting
- CPM-based pricing system
- Ad performance analytics

---

## Development Guidelines

### **Code Organization:**

- Use MVC pattern for clean separation
- Create reusable components for common UI elements
- Implement proper error handling and logging
- Follow PSR standards for PHP code

### **Security Requirements:**

- Input sanitization and validation on all forms
- CSRF protection on all state-changing operations
- SQL injection prevention using prepared statements
- XSS prevention with proper output escaping
- Rate limiting on sensitive operations

### **Performance Considerations:**

- Database indexing on frequently queried columns
- Image optimization for uploads
- Caching for frequently accessed data
- Lazy loading for large datasets
- Mobile-first responsive design

### **Testing Strategy:**

- Unit tests for critical business logic
- Integration tests for payment processing
- User acceptance testing for each phase
- Security testing before production deployment

### **Deployment Notes:**

- Use environment variables for sensitive configuration
- Implement proper backup strategies
- Set up monitoring and error tracking
- Use SSL certificates for secure connections
- Configure proper server security headers

## Current Phase: PHASE 1

**Next Steps**: Implement user authentication and basic user management system as outlined above.

**Success Criteria for Phase 1:**

- Users can register with different account types
- Login/logout works securely
- Password reset functionality works
- Basic dashboards show different content based on user type
- All forms have proper validation and security

Once Phase 1 is complete and tested, proceed to Phase 2 (Content System).
