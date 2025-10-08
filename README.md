# 🏍️ Bike Rental System

A complete **Bike Rental System** built with **PHP, MySQL, and Tailwind CSS** running on **XAMPP**.

## 📋 Features

### User Features

- 🔐 User Registration & Login System
- 🔍 Browse and Search Bikes
- 🏍️ View Detailed Bike Information
- 📅 Request Bike Bookings with Date Selection
- ⭐ Rate and Review Bikes
- 📱 Fully Responsive Design

### Admin Features

- 📊 Admin Dashboard with Statistics
- ➕ Add New Bikes
- ✏️ Edit Existing Bikes
- 🗑️ Delete Bikes
- 📈 View All Bookings and Reviews

## 🛠️ Technology Stack

- **Backend:** PHP 7.4+
- **Database:** MySQL
- **Frontend:** Tailwind CSS
- **Icons:** Font Awesome 6
- **Server:** XAMPP (Apache + MySQL)

## 📁 Project Structure

```
bikerental/
├── assets/
│   ├── css/
│   ├── images/
│   └── js/
├── components/
│   ├── header.php
│   └── footer.php
├── pages/
│   ├── login.php
│   ├── register.php
│   ├── logout.php
│   ├── explore.php
│   ├── bike_details.php
│   ├── admin_dashboard.php
│   ├── add_bike.php
│   └── edit_bike.php
├── config.php
├── db_connect.php
├── db_setup.sql
├── index.php
├── prd.md
└── README.md
```

## 🚀 Installation Guide

### Prerequisites

- XAMPP installed on your system
- Web browser (Chrome, Firefox, Edge, etc.)

### Step 1: Install XAMPP

1. Download XAMPP from [https://www.apachefriends.org](https://www.apachefriends.org)
2. Install XAMPP in `C:\xampp` (Windows) or `/opt/lampp` (Linux)
3. Start Apache and MySQL from XAMPP Control Panel

### Step 2: Set Up the Project

1. Copy the `bikerental` folder to `C:\xampp\htdocs\`
2. Your project path should be: `C:\xampp\htdocs\bikerental\`

### Step 3: Set Up the Database

1. Open your browser and go to [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
2. Click on "Import" tab
3. Click "Choose File" and select `db_setup.sql` from the project folder
4. Click "Go" to execute the SQL script
5. The database `bikerental` will be created with all tables and sample data

**OR manually:**

1. Create a new database named `bikerental`
2. Copy all SQL from `db_setup.sql`
3. Paste in SQL tab and execute

### Step 4: Configure Database Connection

Edit `db_connect.php` if needed (default settings work with XAMPP):

```php
$host = 'localhost';
$dbname = 'bikerental';
$username = 'root';
$password = ''; // Empty for XAMPP default
```

### Step 5: Access the Website

Open your browser and visit:

- **Homepage:** [http://localhost/bikerental](http://localhost/bikerental)
- **Admin Login:** [http://localhost/bikerental/pages/login.php](http://localhost/bikerental/pages/login.php)

## 🔑 Default Login Credentials

### Admin Account

- **Email:** admin@bikerental.com
- **Password:** admin123

### Test User Account

- **Email:** user@bikerental.com
- **Password:** user123

## 📱 Pages Overview

### Public Pages

- **Home (index.php)** - Hero section with featured bikes
- **Explore Bikes (explore.php)** - Browse all bikes with search and filter
- **Bike Details (bike_details.php)** - Detailed view with booking and reviews
- **Login (login.php)** - User authentication
- **Register (register.php)** - New user registration

### Admin Pages (Requires Admin Login)

- **Admin Dashboard (admin_dashboard.php)** - Overview with statistics
- **Add Bike (add_bike.php)** - Add new bikes to inventory
- **Edit Bike (edit_bike.php)** - Modify existing bike details

## 🗃️ Database Tables

1. **users** - User accounts (admin and regular users)
2. **categories** - Bike categories (Rally, Scooters, etc.)
3. **bikes** - Bike inventory with details
4. **bookings** - Rental booking requests
5. **reviews** - User ratings and reviews

## 🎨 Design Features

- Modern, premium UI design
- Gradient hero sections
- Card-based layouts
- Responsive grid system
- Smooth hover effects
- Interactive forms
- Rating stars display
- Status badges

## 🔧 Configuration

### Base URL

Update in `config.php` if your project path is different:

```php
define('BASE_URL', 'http://localhost/bikerental');
```

### Session Management

Sessions are automatically handled in `config.php` with helper functions:

- `isLoggedIn()` - Check if user is authenticated
- `isAdmin()` - Check if user has admin privileges
- `requireLogin()` - Protect pages requiring authentication
- `requireAdmin()` - Protect admin-only pages

## 🐛 Troubleshooting

### Database Connection Error

- Ensure MySQL is running in XAMPP
- Check database credentials in `db_connect.php`
- Verify database name is `bikerental`

### Page Not Found (404)

- Check BASE_URL in `config.php`
- Ensure Apache is running in XAMPP
- Verify project is in `htdocs/bikerental` folder

### Images Not Loading

- Check image URLs in the database
- Ensure image_url column contains valid URLs
- Test image URLs directly in browser

### Login Not Working

- Clear browser cookies and cache
- Check if database has user records
- Verify password is hashed correctly

## 📝 Adding New Features

### Adding a New Bike Category

```sql
INSERT INTO categories (name, description) VALUES
('Sport Bikes', 'High-speed racing bikes');
```

### Adding a New Admin

```sql
INSERT INTO users (name, email, password, is_admin) VALUES
('New Admin', 'newadmin@example.com', '$2y$10$hash...', 1);
```

### Updating Bike Ratings

Ratings are automatically updated when users submit reviews. To manually recalculate:

```sql
UPDATE bikes SET rating = (
    SELECT AVG(rating) FROM reviews WHERE bike_id = bikes.id
) WHERE id = [bike_id];
```

## 🔒 Security Features

- Password hashing using `password_hash()`
- SQL injection protection using PDO prepared statements
- XSS prevention using `htmlspecialchars()`
- Session-based authentication
- Role-based access control (Admin/User)
- CSRF protection recommended for production

## 📄 License

This project is created for educational purposes.

## 👨‍💻 Development

### Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache 2.4 or higher

### Development Tips

- Use prepared statements for all database queries
- Sanitize all user inputs
- Test on different screen sizes for responsiveness
- Keep database credentials secure
- Regular database backups recommended

## 📞 Support

For issues or questions:

1. Check the troubleshooting section
2. Review the PRD.md for requirements
3. Verify all installation steps are complete

## 🎯 Future Enhancements

- Email notifications for bookings
- Payment gateway integration
- Advanced search filters
- User booking history
- Admin booking management
- Multi-image support for bikes
- Real-time availability checking
- Mobile app version

---

**Enjoy your Bike Rental System! 🏍️**
