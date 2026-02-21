# Hostel Management System - CampusNest !

A role-based Hostel Management System built with PHP, MySQL, HTML, CSS, and JavaScript. It supports Admin, Manager, and Student dashboards with authentication, booking, payments, complaints, and events management.

## Features

### Authentication
- Login / Register / Logout
- Password reset
- Session-based access control (role protection)

### Admin Module
- Dashboard overview (counts/stats)
- Manage Students (view, activate/block)
- Manage Rooms (CRUD)
- Manage Bookings (approve/reject/view)
- Manage Payments (view/history/status)
- Manage Complaints (view/assign/track)
- Manage Events (CRUD)
- Settings (profile/password)

### Manager Module
- Dashboard overview
- View Students / Rooms / Bookings (as provided)
- Handle Complaints (assigned complaints workflow)
- Meals (if implemented)
- Profile (update info/password)

### Student Module
- Dashboard overview
- View Rooms and request Bookings
- Payments (view/history, pay if implemented)
- Complaints (create/track)
- Meals (if implemented)
- Profile (update info/password)

## Tech Stack
- **Backend:** PHP (mysqli)
- **Database:** MySQL
- **Frontend:** HTML, CSS, JavaScript
- **Local Server:** XAMPP / Apache

## Folder Structure
```txt
HostelManagementSystem/
├── admin/
│   ├── dashboard.php
│   ├── students.php
│   ├── rooms.php
│   ├── bookings.php
│   ├── payments.php
│   ├── complaints.php
│   ├── events.php
│   └── settings.php
├── manager/
│   ├── dashboard.php
│   ├── students.php
│   ├── rooms.php
│   ├── bookings.php
│   ├── complaints.php
│   ├── meals.php
│   └── profile.php
├── student/
│   ├── dashboard.php
│   ├── rooms.php
│   ├── bookings.php
│   ├── complaints.php
│   ├── payments.php
│   ├── meals.php
│   └── profile.php
├── auth/
│   ├── login.php
│   ├── register.php
│   ├── logout.php
│   └── reset_password.php
├── includes/
│   ├── config.php
│   ├── functions.php
│   └── header.php
├── assets/
│   ├──Here’s a ready-to-paste **`README.md`** for your GitHub repo (edit the placeholders like DB name, credentials, screenshots).

```md
# Hostel Management System (PHP + MySQL)

A role-based Hostel Management System built with **PHP**, **MySQL**, **HTML/CSS**, and **JavaScript**.  
It supports three user roles (**Admin**, **Manager**, **Student**) with separate dashboards and modules for rooms, bookings, payments, complaints, events, and settings.

---

## Features

### Admin
- Dashboard overview (students, rooms, bookings, payments, complaints)
- Manage students (view, activate/block)
- Manage rooms (add/update/delete, availability)
- Manage bookings (approve/reject)
- Manage payments (view history/status)
- Manage complaints (view + assign)
- Events management (CRUD)
- Settings (profile update, change password)

### Manager
- Dashboard overview
- View students / rooms / bookings (as provided in your modules)
- Complaints handling (assigned complaints, update status)
- Meals module (if implemented)
- Profile management

### Student
- Dashboard overview
- View rooms & request bookings
- View booking status/history
- Payments (history / status)
- Complaints submission & tracking
- Meals module (if implemented)
- Profile management

### Security / Technical
- Session-based authentication + role-based access control
- Password hashing (recommended: `password_hash()` / `password_verify()`)
- Input validation + basic sanitization
- Optional “Remember Me” cookie (if enabled)


