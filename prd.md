We are building a complete **Bike Rental System Website** using **PHP, MySQL, and Tailwind CSS**, running locally via **XAMPP**.

---

### 🏍️ Project Overview

The project is a **bike rental system** that allows users to browse, rent, and manage bikes.
It supports **two user types**:

- **Normal User**
- **Admin User**

User roles are identified using an `is_admin` field in the `users` table (`0` = user, `1` = admin).

---

### 🌐 Website Pages & Features

#### 1. Home Page

- A **modern, premium hero section** with a large banner and CTA buttons.
- Below the hero, show highlights like featured bikes, categories, and promotional sections.
- **Responsive design** using Tailwind CSS following modern UI principles.

#### 2. Header and Footer

- The **header and footer** should be **reusable components**, placed in a `/components` folder.
- Header should:

  - Show **"Explore Bikes"** before login.
  - After login:

    - Show **"Explore Bikes"** for normal users.
    - Show **"Explore Bikes"** and **"Manage Bikes"** for admin users.

  - Include **Login / Register** buttons before login, and a **Logout** option after login.

- Footer should match the overall site theme, minimal and clean.

#### 3. Hero Section Behavior

- The **Hero section** should dynamically adapt based on login state:

  - **Before login:** Show a button like “Explore Bikes”.
  - **After login (user):** Show “Explore Bikes”.
  - **After login (admin):** Show both “Explore Bikes” and “Manage Bikes”.

#### 4. Bike Listing

- Display bikes in a responsive **card grid**.
- Each bike card includes:

  - Image (URL stored in the database)
  - Name, category, price per hour/day, and rating.

- Allow filtering by categories such as:

  - Rally Bikes
  - Normal Bikes
  - Scooters
  - (etc.)

#### 5. Bike Details Page

When a user clicks a bike:

- Open a detailed page similar to an Amazon product view.
- Include:

  - Multiple images
  - Description, price, and technical specifications
  - Availability (via date/time selection)
  - “Request Bike” button for booking
  - **Ratings and Reviews** section (users can submit reviews)

- Bike images should be stored as **online image URLs** (type `VARCHAR` in DB).

#### 6. Admin Panel

- Accessible only to admins.
- Design should match the main site’s look and feel.
- Admin can:

  - **Add** new bikes
  - **Edit** existing bikes
  - **Delete** bikes
  - Manage categories

- Display a success or error message after each action.

#### 7. Authentication System

- Login and registration pages with Tailwind UI styling.
- After login, redirect users appropriately based on role.
- Maintain sessions for logged-in users.

#### 8. Database Setup

Include a **`db_setup.sql`** file with all the necessary `CREATE TABLE` statements.
This file will be used in phpMyAdmin for easy setup.

The database should include at least the following tables:

- **users**

  - id, name, email, password, is_admin

- **bikes**

  - id, name, category_id, description, price, image_url, rating

- **categories**

  - id, name

- **bookings**

  - id, user_id, bike_id, start_date, end_date, status

- **reviews**

  - id, user_id, bike_id, rating, comment, created_at

Include any necessary relational foreign keys.

---

### 🧱 Project Structure

Use a clean and modular file structure:

```
/project-root
│
├── /components
│   ├── header.php
│   ├── footer.php
│   └── other reusable parts
│
├── /pages
│   ├── home.php
│   ├── login.php
│   ├── register.php
│   ├── bike_details.php
│   ├── explore.php
│   ├── admin_dashboard.php
│   ├── add_bike.php
│   └── edit_bike.php
│
├── /assets
│   ├── /images
│   ├── /css
│   └── /js
│
├── db_setup.sql
├── db_connect.php
├── index.php
└── README.md
```

---

### 🎨 Design & UI Guidelines

- Use **Tailwind CSS** for all styling.
- Maintain a **premium, minimal, modern** aesthetic.
- Ensure full **responsiveness** for desktop, tablet, and mobile devices.
- Keep consistent color schemes and typography.

---
