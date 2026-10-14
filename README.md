# 🏪 Smart Store Credit & Customer Dues Management System

A web-based customer credit and financial ledger application built with PHP and MySQL. Designed for local retail stores and shopkeepers to replace paper-based credit registers with a secure, real-time digital system.

---

## 🚀 Key Modules & System Workflow

### 1. 🔐 Secure Admin Authentication
- Dedicated login portal with secure session management to safeguard store data.
- Session-based gatekeeper preventing unauthorized URL access.
- Working Sign Out functionality to safely terminate active sessions.

### 2. 📊 Real-time Dashboard & Analytics
- Live overview tracking **Total Pending Dues**, **Active Debtors**, and **Monthly Cash Collections**.
- Interactive sales insights and transaction trend visualization.
- Built-in theme switcher supporting both Dark and Light modes.

### 3. 👥 Customer Directory & Management
- Fast customer lookups by name or contact number.
- Seamless editing of customer profiles directly from the directory table.
- **Opening Due Balance Support:**
  - Initial balance set to `Rs 0.00` for new customers.
  - Option to import existing pending dues carried over from manual record books.

### 4. 📖 Automated Passbook & Ledger System
- **Give Credit:** Log credit sales to increment dues; updates status to `Due`.
- **Receive Repayment:** Record cash returns to deduct dues; automatically updates status to `Partial`.
- **Auto Settlement:** Marks account as `Settled` once the remaining balance hits `Rs 0.00`.
- **Printable Statements:** One-click generation of formatted customer ledger slips.

### 5. 📑 Financial Audit Reports
- Detailed transaction logs tracking credit issues and cash collections date-wise.
- Printable and exportable audit summaries for store financial review.

### 6. ⚙️ Admin Settings & Preferences
- Store branding customization (store name update).
- Account security management (password reset).
- Administrator profile picture upload and management.

---

## 🛠️ Tech Stack

- **Backend:** PHP
- **Database:** MySQL
- **Frontend:** HTML5, CSS3, JavaScript (ES6+), Bootstrap

---

## 💻 Local Setup & Installation

1. **Clone the repository:**
   ```bash
   git clone [https://github.com/areeba236/Smart_Store_Credit_and_Customer_Dues_Management_System.git](https://github.com/areeba236/Smart_Store_Credit_and_Customer_Dues_Management_System.git)