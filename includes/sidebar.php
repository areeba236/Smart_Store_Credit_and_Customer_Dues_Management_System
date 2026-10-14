<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$adminInfo = getAdminProfile($conn);
?>
<aside class="sidebar" id="sidebar">
  <div class="sidebar-top">
    <div class="brand">
      <div class="brand-icon"><i class="ri-store-2-fill"></i></div>
      <div class="brand-text">Smart<span>Store</span></div>
    </div>
    
    <div class="brand-subtitle">Credit & Dues Management</div>

    <nav class="nav-links">
      <a href="dashboard.php" class="nav-item <?php echo ($currentPage == 'dashboard.php') ? 'active' : ''; ?>">
        <i class="ri-dashboard-line"></i>
        <span>Dashboard</span>
      </a>
      <a href="customers.php" class="nav-item <?php echo ($currentPage == 'customers.php' || $currentPage == 'customer_ledger.php') ? 'active' : ''; ?>">
        <i class="ri-user-follow-line"></i>
        <span>Customer Ledgers</span>
      </a>
      <a href="reports.php" class="nav-item <?php echo ($currentPage == 'reports.php') ? 'active' : ''; ?>">
        <i class="ri-file-chart-line"></i>
        <span>Financial Reports</span>
      </a>
      <a href="settings.php" class="nav-item <?php echo ($currentPage == 'settings.php') ? 'active' : ''; ?>">
        <i class="ri-settings-3-line"></i>
        <span>Admin & Settings</span>
      </a>
    </nav>
  </div>

  <div class="sidebar-footer">
    <div class="store-badge">
      <i class="ri-verified-badge-fill"></i>
      <div>
        <div class="store-name"><?php echo htmlspecialchars($adminInfo['store_name']); ?></div>
        <div class="store-status">Active Retail Store</div>
      </div>
    </div>
    <a href="settings.php" class="signout-btn" style="color: #cbd5e1; margin-bottom: 6px;">
      <i class="ri-user-settings-line"></i>
      <span>Edit Admin Profile</span>
    </a>
    <a href="logout.php" class="signout-btn" onclick="return confirm('Do you really want to sign out?');">
      <i class="ri-logout-box-r-line"></i>
      <span>Sign out</span>
    </a>
  </div>
</aside>
