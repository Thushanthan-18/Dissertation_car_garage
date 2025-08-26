This project is part of my MSc dissertation.
It is a secure web-based application for managing a car garage, with separate dashboards for admins and customers.
The system demonstrates user authentication, role-based access control, and protection against SQL injection.

Features

Authentication
	•	Customer login (customer/login.php)
	•	Admin login (admin/login_a.php)
	•	Session-based access control

Admin Dashboard (admin/dashboard.php)
	•	Vehicle Inventory (connected to services table)
	•	Service Bookings (links customers + services + dates)
	•	Customers (registered users list)
	•	Staff Management (users with admin/staff roles)
	•	Reports (KPIs: total users, bookings, popular services)

Customer Dashboard (customer/dashboard.php)
	•	Single-page style dashboard with sections:
	•	Book Service
	•	My Bookings
	•	My Vehicles
	•	Service History
	•	Profile Settings

Database
	•	users → customers, staff, admin accounts
	•	services → vehicles/services offered
	•	bookings → customer service bookings
	•	roles → user roles (admin, staff, customer)

Security
	•	PHP sessions for authentication
	•	Prepared statements in all database queries
	•	Tested with sqlmap to ensure login inputs are not SQL injectable


 Technologies
	•	Frontend: HTML, CSS, JavaScript
	•	Backend: PHP (PDO)
	•	Database: MySQL (phpMyAdmin for import/export)
	•	Server: XAMPP (Apache + MySQL)


Project Structure:

car_garage/
├─ landing.html
├─ sales-rentals/
│  └─ sales-rentals.html
├─ services/
│  ├─ car-servicing.html
│  ├─ mot-testing.html
│  ├─ repairs.html
│  └─ diagnostics.html
├─ admin/
│  ├─ dashboard.php
│  └─ login_a.php
├─ admin_pages/
│  ├─ inventory.php
│  ├─ bookings.php
│  ├─ customers.php
│  ├─ staff.php
│  └─ reports.php
├─ customer/
│  ├─ dashboard.php
│  └─ login.php
├─ php_file/
│  ├─ connect.php
│  ├─ csrf.php
│  ├─ contact_submit.php
│  ├─ process_login.php
│  └─ register.php
├─ css/
│  ├─ styles.css
│  ├─ services.css
│  ├─ admin.css
│  └─ app.css
├─ js/
│  ├─ main.js
│  ├─ admin.js
│  └─ customer.js
├─ images/
│  ├─ logo files (Logo 2.jpg, favicon, etc.)
│  ├─ about-img.jpg
│  └─ cars/
│     ├─ bmw-3-series.jpg
│     ├─ audi-a3.jpg
│     ├─ ford-focus.jpg
│     ├─ vw-golf.jpg
│     └─ toyota-yaris.jpg
└─ database/
   └─ car_garage.sql

   Setup Instructions
   1. Clone the repo
      git clone https://github.com/Thushanthan-18/Dissertation_car_garage.git

   2. Move into your web server directory (XAMPP)
      C:\xampp\htdocs\car_garage
   3. Start Apache and MYSQL in XAMPP.
   4. Import the database
     - Open phpMyAdmin -> Create new DB car_garage
     - Import file: database/car_garage.sql
   5. Configure database connection
      Edit php_file/connect.php
      
      $host = 'localhost';
      $db   = 'car_garage';
      $user = 'root';
      $pass = '';
    6. Access the system
      http://localhost/car_garage/Landing.html
      Admin -> admin/login_a.php

Default Login Credentials

For convenience, the SQL script (database/car_garage.sql) includes sample accounts:
	•	Admin
	 	  Email: st1818@example.com
	    Password: Password123@
	•	Customer
		  Email: thush18@gmail.com
	    Password: Password123@
     
Security Testing
	-	Tested with sqlmap to check SQL Injection vulnerabilities
	-	Login forms resisted injection attempts
	-	Session-based access prevents direct URL access to admin pages


Notes

This repository is the project corpus submission (not including dissertation, logbook, or video).

Future Improvements
	•	Better frontend design
	•	Full CRUD features for all admin pages
	•	Enhanced reporting & analytics
