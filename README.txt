ACTIVITY #2 FINAL TERM - ACTIVITY 1 WEBSITE WITH PHP AND MYSQL
==============================================================

This project continues your Activity 1 Sign In/Register website. It keeps the
blue-and-white design and adds server-side login, access control, user
management, and CRUD operations.

REQUIREMENTS
------------
1. XAMPP (Apache and MySQL)
2. Visual Studio Code
3. A web browser

PROJECT FILES
-------------
index.php             Redirects to login or dashboard
login.php             Secure login using email or username
register.php          Creates a regular user account
dashboard.php         Shows the signed-in user's account details
records.php           Create, read, update, and delete records
users.php             Admin-only user management
logout.php            Secure logout
config/db.php         MySQL database connection
config/auth.php       Sessions, access checks, and CSRF protection
includes/             Shared page header and footer
assets/style.css      Styling based on Activity 1
database.sql          Database and table creation script

HOW TO RUN IN XAMPP
-------------------
1. Extract this ZIP file.
2. Copy the folder "Activity2_Act1_Integrated" into:
   C:\xampp\htdocs\
3. Open XAMPP Control Panel.
4. Start Apache and MySQL.
5. Open http://localhost/phpmyadmin in your browser.
6. Click the Import tab.
7. Choose the database.sql file from this project and click Import.
8. Open this address:
   http://localhost/Activity2_Act1_Integrated/

HOW TO CREATE YOUR FIRST ADMIN ACCOUNT
--------------------------------------
1. Open the website and register a normal account.
2. Open phpMyAdmin and select the signin_register database.
3. Open the SQL tab.
4. Run this command, replacing your registered username:

   UPDATE users SET role = 'admin' WHERE username = 'YOUR_USERNAME';

5. Log out and log in again. The User Management link will appear.
6. Keep your admin account credentials private.

HOW TO TEST THE REQUIREMENTS
----------------------------
REGISTRATION:
- Register a new account.
- Try registering the same username or email again; it should be rejected.

LOGIN AND LOGOUT:
- Sign in using your email or username and password.
- Click Logout to end the session.

ACCESS CONTROL:
- Sign in as a normal user and try opening users.php directly.
- The page should show 403 Forbidden.
- Sign in as an admin to access User Management.

USER MANAGEMENT:
- Admin can view registered accounts.
- Admin can change another user's role between User and Admin.
- Admin can delete another user.
- The currently signed-in admin cannot delete or change their own role here.

CRUD:
- Open My Records.
- Add a record (CREATE).
- View the saved records (READ).
- Click Edit and save changes (UPDATE).
- Click Delete (DELETE).
- A regular user can manage only their own records. An admin can manage all records.

IMPORTANT NOTES
---------------
- Public registration always creates a normal "user" account. A visitor cannot
  choose the admin role on the registration form.
- Passwords are stored as secure password hashes, not plain text.
- SQL queries use prepared statements.
- This is a school project starter. For a public production website, add
  HTTPS, rate limiting, email verification, and additional deployment security.
