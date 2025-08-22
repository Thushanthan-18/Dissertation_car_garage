Car Garage Management System
This project is part of my MSc dissertation.
It is a secure web-based application for managing a car garage, with separate dashboards for admins and customers.
The system demonstrates user authentication, role-based access control, and protection against SQL injection.



Features:

Authentication
- Customer login (customer/login.html)
- Admin login (admin/login_a.php)
- Session-based access control
Admin Dashboard (admin/dashboard.php)
- Vehicle Inventory (connected to services table)
- Service Bookings (links customers + services + dates)
- Customers (registered users list)
- Staff Management (users with admin/staff roles)
- Reports (KPIs: total users, bookings, popular services)
Customer Dashboard (customer/dashboard.html)
- Customers can log in and view their bookings
Database
- users → customers, staff, admin accounts
- services → vehicles/services offered
- bookings → customer service bookings
- roles → user roles (admin, staff, customer)
Security
- PHP sessions for authentication
- Prepared statements in database queries
- Tested with sqlmap to ensure login inputs are not SQL injectable

  
 Technologies
- Frontend: HTML, CSS, JavaScript
- Backend: PHP (PDO)
- Database: MySQL (phpMyAdmin for import/export)
- Server: XAMPP (Apache + MySQL)


 Project Structure
car_garage/
├── admin/          # Admin login + dashboard
├── customer/       # Customer login + dashboard
├── php_file/       # Backend PHP (connect, login, register, etc.)
├── css/            # Stylesheets
├── js/             # JavaScript files
├── database/       # SQL schema & data dump
│   └── car_garage.sql



⚙️ Setup Instructions
1. Clone the repository:
   git clone https://github.com/Thushanthan-18/Dissertation_car_garage.git

2. Move the project into your web server directory (e.g., htdocs for XAMPP).

3. Start Apache and MySQL in XAMPP.

4. Import the database:
   - Open phpMyAdmin → Create a new database (e.g., car_garage).
   - Import the file: database/car_garage.sql

5. Configure database connection:
   - File: php_file/connect.php
   - Update with your MySQL credentials:
     $host = 'localhost';
     $db   = 'car_garage';
     $user = 'root';
     $pass = '';

6. Access the system:
   http://localhost/car_garage/

   
Security Testing
- Tested with sqlmap to check SQL Injection vulnerabilities.
- Login forms resisted injection attempts.
- Session-based access prevents direct URL access to admin pages.


  Notes
- This repository is the project corpus submission (not including dissertation, logbook, or video).
- Future improvements may include:
  - Better frontend design
  - Full CRUD features for all admin pages
  - Enhanced reporting & analytics
