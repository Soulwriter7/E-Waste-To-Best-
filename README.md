# E-Waste to Best: Smart E-Waste Recycling & Metal Recovery System
> **Academic Final Year / Capstone College Project**  
> Built with: **PHP 8.x, MySQL, HTML5, CSS3, JavaScript (ES6+), Bootstrap 5.3 & Chart.js**

---

## 📖 Project Overview
**E-Waste to Best** is a comprehensive smart web application engineered to bridge conscious citizens and institutions with certified metallurgical e-waste recovery centers. The platform enables users to calculate empirical theoretical recovery yields for precious and industrial metals (**Gold, Silver, Copper, Aluminium**) inside obsolete electronics, schedule verified doorstep collections, and audit their environmental savings (CO₂ emissions avoided, groundwater toxins sequestered) with downloadable official Green Certificates.

---

## 🌟 Key Features

1. **Modern Responsive Home Platform:**
   - Dynamic Hero section with interactive call-to-actions.
   - Live Metal Market Rate Ticker (Gold, Silver, Copper, Aluminium in INR/gram).
   - Real-time cumulative platform impact counters (Total pickups, CO₂ offset, Gold reclaimed, Copper saved).
   - "From Scrap to Best" 4-step circular economy workflow.
   - Quick Metal Estimator widget directly embedded on the landing page.

2. **E-Waste Hazards & Public Awareness (`awareness.php`):**
   - In-depth chemical toxicology breakdown: Lead (Pb), Mercury (Hg), Cadmium (Cd), BFRs, Beryllium, Lithium.
   - Global and Indian e-waste statistics.
   - Interactive Do's and Don'ts comparison cards for households and college campuses.

3. **Refurbishment & Reuse Suggestions (`reuse-tips.php`):**
   - Circular economy 4R hierarchy (Refuse > Reduce > Reuse > Recycle).
   - Categorized guides to give a second life to old laptops (Linux/Plex servers), phones (dashcams, security cameras), hard drives (DIY USB external storage), monitors, and cables.
   - Interactive **"Should You Repair or Recycle?"** decision matrix assistant.

4. **Metallurgical Recovery & Scrap Value Estimator (`estimator.php`):**
   - Multi-device selector (Smartphones, Laptops, Motherboards, Full Towers, RAM Sticks, HDDs, Monitors, Mixed Cables, Tablets).
   - Live JavaScript computation preview syncing with PHP backend algorithms.
   - 4-metal breakdown showing yield in grams and benchmark market scrap valuation.
   - Clear scientific citations (USGS Circular 1400 / EPA WEEE reports) and prominent disclaimer distinguishing theoretical projections from physical laboratory assay.
   - Direct button to book a pickup for the estimated device.

5. **Authorized Collection Centre Locator (`centers.php`):**
   - Searchable and filterable directory of state-certified e-waste depots across major Indian cities (Delhi, Bengaluru, Mumbai, Hyderabad, Lucknow, Pune).
   - Filter by City and keyword.
   - Details: contact person, phone, email, operating hours, accepted electronics, and one-click Google Maps directions.

6. **Citizen Authentication (`login.php` & `register.php`):**
   - Secure registration with address, city, phone, and pincode.
   - Passwords securely hashed with `PASSWORD_DEFAULT` (bcrypt).
   - **One-Click Demo Login Buttons** (Admin & Citizen) for easy college viva presentation and examiner review.
   - Session regeneration and CSRF protection on all forms.

7. **Citizen Portal (`user/`):**
   - **Dashboard (`user/dashboard.php`):** Summary metrics, total estimated valuation, CO₂ offset, and recent requests queue.
   - **Pickup Request Form (`user/pickup-request.php`):** Multi-step booking form with automatic theoretical estimation sidebar, address auto-fill, date/slot picker, and center selection.
   - **Request History (`user/history.php`):** Complete request log with status filter pills (`Pending`, `Assigned`, `Collected`, `Completed`, `Cancelled`).
   - **Printable Voucher / Receipt (`user/view-request.php`):** Official collection voucher with side-by-side comparison of **Theoretical Estimates vs. Verified Laboratory Assay** and print button.
   - **Environmental Impact & Certificate (`user/impact.php`):** Graphical Chart.js analytics (metal breakdown doughnut chart & carbon balance bar chart) + **Printable Institutional Green Citizen Certificate**.
   - **Profile Settings (`user/profile.php`):** Update contact info, address, and password.

8. **Admin Command Center (`admin/`):**
   - **Dashboard (`admin/dashboard.php`):** System KPIs, incoming pickup queue, and category distribution charts.
   - **Pickup Dispatch (`admin/pickups.php`):** Manage request lifecycle and quick center assignment.
   - **Laboratory Assay Verification Interface (`admin/verify-pickup.php`):** Record physical weighed gross weight, actual recovered grams for Gold, Silver, Copper, Aluminium, and certified payout. Updates status to `Completed` and logs environmental impact.
   - **Collection Centers CRUD (`admin/centers.php`):** Add, Edit, Delete, and Toggle Active/Inactive status for collection depots.
   - **Metal Rates Manager (`admin/metal-rates.php`):** Real-time editor for benchmark market scrap rates per gram.
   - **Citizen Directory (`admin/users.php`):** Searchable list of registered users and collection statistics.

---

## 🛠️ Technology Stack & Architecture

- **Frontend:** HTML5, CSS3 (Modern Eco-Tech theme), JavaScript (ES6+), Bootstrap 5.3, Bootstrap Icons, Chart.js.
- **Backend:** PHP 8.x (Modular, Object-Oriented PDO prepared statements).
- **Database:** MySQL 8.x / MariaDB (InnoDB, Foreign Key constraints, UTF-8 unicode).
- **Server:** Apache via XAMPP.

---

## ⚡ One-Click Quick Start (Windows Batch Launcher)

Simply double-click:
```text
run_website.bat
```
*(or double-click `START_PROJECT.bat`)*

**What this script automatically does for you:**
1. Detects your XAMPP installation (`C:\xampp`).
2. Checks and starts **Apache** and **MySQL** services automatically if not already running.
3. Synchronizes project files directly into `C:\xampp\htdocs\Waste to best\`.
4. Verifies database `ewaste_db` (and imports `config\seed_data.sql` if missing).
5. Automatically opens your default web browser directly to:
   ```text
   http://localhost/Waste to best/
   ```

---

## 🚀 Manual Step-by-Step Installation & Setup (XAMPP)

### Step 1: Place the Project in XAMPP
Copy the project folder `Waste to best` to your XAMPP `htdocs` directory:
```text
C:\xampp\htdocs\Waste to best\
```

### Step 2: Start Apache and MySQL
1. Launch the **XAMPP Control Panel**.
2. Click **Start** for both **Apache** and **MySQL**.
3. Ensure both modules are highlighted green.

### Step 3: Import the Database
1. Open your browser and navigate to: `http://localhost/phpmyadmin/`
2. Click on the **Import** tab in the top navigation bar.
3. Click **Choose File** and select the seed file located at:
   ```text
   config/seed_data.sql
   ```
4. Click **Import** (or **Go**) at the bottom of the page.
5. The database `ewaste_db` with all 6 relational tables and sample seed records will be created automatically.

### Step 4: Access the Application
Open your web browser and visit:
```text
http://localhost/Waste to best/
```

---

## 🔑 Default Credentials for Academic Presentation

For seamless college viva evaluation, you can use the **Quick Demo Login buttons** on `login.php` or enter:

| Role | Email | Password | Access Portal |
| :--- | :--- | :--- | :--- |
| **System Administrator** | `admin@ewaste.org` | `admin123` | Full Admin Command Center (`/admin/dashboard.php`) |
| **Citizen Demo 1** | `rahul@gmail.com` | `user123` | Citizen Dashboard & Requests (`/user/dashboard.php`) |
| **Citizen Demo 2** | `priya@gmail.com` | `user123` | Citizen Dashboard & Requests (`/user/dashboard.php`) |

---

## 🛡️ Security Best Practices Implemented

- **SQL Injection Prevention:** Every query is executed through **PDO with parameter binding (`?` placeholders)**.
- **Password Security:** Passwords are never stored as plaintext; they are hashed with `password_hash($pass, PASSWORD_DEFAULT)` and verified with `password_verify()`.
- **Cross-Site Scripting (XSS):** All dynamic browser outputs pass through `sanitize()` (`htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`).
- **CSRF Protection:** Critical POST forms include synchronized cryptographic tokens (`validateCsrfToken()`).
- **Session Fixation Defense:** Sessions are regenerated via `session_regenerate_id(true)` upon successful authentication.
- **Role-Based Access Control (RBAC):** Middleware guards (`auth_check.php` and `admin_check.php`) strictly restrict user and admin routes.

---

## 📜 Scientific & Ethical Transparency Standards

- **Theoretical vs. Verified:** The application explicitly labels mathematical estimates prior to physical intake as **"Theoretical Projections"**.
- **No False Booking Claims:** Pickups are assigned a confirmation reference code only when verified and booked into the MySQL database.
- **Authorized Facilities:** Collection center directory contains structured address, phone, email, operating hours, and accepted materials.

Author
Created and maintained by Akash Shukla.
