# 🏫 Crowther Memorial College - School Enrollment System

A comprehensive web-based school enrollment system for Crowther Memorial College, Lokoja, Kogi State. This system allows parents to register, submit student applications, make payments, and track application status, while administrators can manage applications, verify payments, and generate reports.

---

## 📌 Features

### 👤 Parent/Student Features
- **User Registration & Login** – Secure account creation with password strength requirements
- **Student Application** – Submit enrollment applications with document uploads (passport, birth certificate, report card)
- **Fee Structure** – Automatic fee calculation based on class category (Primary, JSS, SSS)
- **Online Payment** – Make payments and upload payment receipts for verification
- **Application Tracking** – Real-time status updates (Pending Payment, Pending Review, Approved, Rejected)
- **Document Management** – Upload and manage required documents
- **Dashboard** – Centralized view of all applications, payments, and status

### 👨‍💼 Admin Features
- **Admin Dashboard** – Overview with statistics (total applications, pending, approved, rejected, total fees)
- **Application Management** – View all applications, approve/reject with one click
- **Payment Verification** – Verify payments with uploaded receipts
- **Student Management** – View all enrolled students with class categorization
- **Fee Summary** – Fee breakdown by class category (Primary, JSS, SSS)
- **Reports Export** – Export applications, students, and financial reports as CSV
- **System Settings** – Configure academic session, term, and fee structure

### 💰 Fee Structure
| Class Category | Tuition (per term) | Development Levy | Application Fee | Total (1st Term) |
|----------------|-------------------|------------------|-----------------|------------------|
| Primary 1-6 | ₦35,000 | ₦8,000 | ₦5,000 | ₦48,000 |
| JSS 1-3 | ₦45,000 | ₦10,000 | ₦5,000 | ₦60,000 |
| SSS 1-3 | ₦55,000 | ₦12,000 | ₦5,000 | ₦72,000 |

---

## 🛠️ Technologies Used

- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Backend**: PHP 8.0+
- **Database**: MySQL / MariaDB
- **Icons**: Font Awesome 6
- **Server**: XAMPP / Laragon

---

## 📁 Project Structure
crowther-enrollment/
│
├── admin/ # Admin panel
│ ├── dashboard.php # Admin dashboard
│ ├── login.php # Admin login
│ ├── verify_payment.php # Payment verification
│ └── ...
│
├── assets/ # Static assets
│ ├── css/ # Stylesheets
│ ├── js/ # JavaScript
│ └── images/ # Images
│
├── includes/
│ ├── config-sample.php # Configuration template
│ └── config.php # Database config (excluded from Git)
│
├── uploads/ # Uploaded documents (excluded from Git)
│ ├── passports/
│ ├── birth_certificates/
│ ├── report_cards/
│ └── payments/
│
├── index.php # Homepage
├── apply.php # Application form
├── dashboard.php # User dashboard
├── register.php # Registration page
├── login.php # Login page
├── payment.php # Payment page
├── process_*.php # Form processing scripts
└── ...


---

📝 Workflow
text

1. Parent registers → creates account
2. Parent submits application → status: pending_payment
3. Parent makes payment (uploads receipt) → status: paid
4. Admin verifies payment → status: verified
5. Admin approves application → status: approved
6. Student is enrolled ✅


