<?php
$admin = getAdminProfile($conn);
$adminInitial = strtoupper(substr($admin['full_name'], 0, 1));
$hasProfileImg = !empty($admin['profile_image']) && file_exists('uploads/' . $admin['profile_image']);
?>
<header class="top-bar">
  <div style="display: flex; align-items: center; gap: 14px;">
    <i class="ri-menu-2-line menu-toggle" id="menuToggle"></i>
    <div class="system-title-badge">
      <i class="ri-store-3-line"></i>
      <span>Smart Store Credit & Dues</span>
    </div>
  </div>
  
  <div class="top-bar-right">
    <!-- Dark / Light Theme Mode Toggle Button -->
    <button class="theme-toggle-btn" onclick="toggleTheme()" title="Switch Theme (Dark / Light)">
      <i class="ri-moon-line" id="themeToggleIcon"></i>
    </button>

    <!-- Admin Profile Pill -->
    <a href="settings.php" class="user-pill" title="Click to view & edit Store Admin Profile">
      <?php if ($hasProfileImg): ?>
        <img src="uploads/<?php echo htmlspecialchars($admin['profile_image']); ?>" alt="Profile" class="user-avatar-img">
      <?php else: ?>
        <div class="user-avatar"><?php echo $adminInitial; ?></div>
      <?php endif; ?>
      <div style="display: flex; flex-direction: column; text-align: left;">
        <span style="font-size: 13.5px; font-weight: 700; color: var(--text-main);"><?php echo htmlspecialchars($admin['full_name']); ?></span>
        <span style="font-size: 11px; color: var(--text-muted);"><?php echo htmlspecialchars($admin['store_name']); ?></span>
      </div>
      <i class="ri-arrow-down-s-line" style="color: var(--text-muted); font-size: 16px;"></i>
    </a>
  </div>
</header>
