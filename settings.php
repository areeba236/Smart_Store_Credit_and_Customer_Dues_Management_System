<?php
require_once 'includes/db.php';
checkAuth();
$pageTitle = "Admin & Store Profile Settings";

$admin = getAdminProfile($conn);
$message = "";
$msgType = "success";

// 1. Update Profile & Picture Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $store_name = trim($_POST['store_name'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $profile_image = $admin['profile_image'];

    // Handle Profile Image Upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['profile_image']['tmp_name'];
        $fileName = $_FILES['profile_image']['name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        if (in_array($fileExt, $allowed)) {
            $newFileName = 'admin_avatar_' . time() . '.' . $fileExt;
            $uploadDest = 'uploads/' . $newFileName;
            if (move_uploaded_file($fileTmp, $uploadDest)) {
                $profile_image = $newFileName;
            }
        }
    }

    if (!empty($store_name) && !empty($full_name) && !empty($username) && $conn) {
        $stmt = mysqli_prepare($conn, "UPDATE admins SET store_name = ?, full_name = ?, email = ?, phone = ?, username = ?, profile_image = ? WHERE id = 1");
        mysqli_stmt_bind_param($stmt, "ssssss", $store_name, $full_name, $email, $phone, $username, $profile_image);
        if (mysqli_stmt_execute($stmt)) {
            $message = "Store profile information and picture updated successfully!";
            $admin = getAdminProfile($conn);
        } else {
            $message = "Error updating profile.";
            $msgType = "danger";
        }
    }
}

// 2. Password Reset / Change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reset_password') {
    $current_pass = $_POST['current_password'] ?? '';
    $new_pass = $_POST['new_password'] ?? '';
    $confirm_pass = $_POST['confirm_password'] ?? '';

    if (strlen($new_pass) < 6) {
        $message = "New password must be at least 6 characters long.";
        $msgType = "danger";
    } elseif ($new_pass !== $confirm_pass) {
        $message = "New password and Confirm password do not match.";
        $msgType = "danger";
    } else {
        if ($conn) {
            $hashed = password_hash($new_pass, PASSWORD_BCRYPT);
            $stmt = mysqli_prepare($conn, "UPDATE admins SET password = ? WHERE id = 1");
            mysqli_stmt_bind_param($stmt, "s", $hashed);
            if (mysqli_stmt_execute($stmt)) {
                $message = "Store administrator password reset successfully!";
            } else {
                $message = "Failed to update password.";
                $msgType = "danger";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin & Store Settings - Smart Store Credit</title>
  <link rel="stylesheet" href="css/style.css">
  <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body>

  <?php include 'includes/sidebar.php'; ?>

  <div class="main-wrapper">
    <?php include 'includes/header.php'; ?>

    <main class="content-area">
      <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $msgType; ?>">
          <i class="ri-information-line"></i> <?php echo htmlspecialchars($message); ?>
        </div>
      <?php endif; ?>

      <div class="page-header">
        <div class="header-title">
          <h1>Store Profile & Security Settings</h1>
          <p>Configure store identity, terminal operator avatar and security credentials</p>
        </div>
      </div>

      <div class="form-grid">
        <!-- Store & Admin Profile Card -->
        <div class="content-card">
          <div class="card-head">
            <h3>Store & Admin Information</h3>
            <span style="font-size: 12px; color: var(--text-muted);">Business Profile</span>
          </div>
          <form action="settings.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="update_profile">

            <!-- Profile Picture Preview & Upload -->
            <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 22px;">
              <?php if (!empty($admin['profile_image']) && file_exists('uploads/' . $admin['profile_image'])): ?>
                <img src="uploads/<?php echo htmlspecialchars($admin['profile_image']); ?>" alt="Profile" style="width: 64px; height: 64px; border-radius: 50%; object-fit: cover; border: 3px solid var(--primary);">
              <?php else: ?>
                <div style="width: 64px; height: 64px; border-radius: 50%; background: var(--primary); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 26px; font-weight: 800;">
                  <?php echo strtoupper(substr($admin['full_name'], 0, 1)); ?>
                </div>
              <?php endif; ?>
              <div>
                <label style="font-size: 13px; font-weight: 700; margin-bottom: 4px; display: block;">Profile Picture</label>
                <input type="file" name="profile_image" accept="image/*" style="font-size: 12px; color: var(--text-muted);">
              </div>
            </div>
            
            <div class="form-group">
              <label>Store / Business Name *</label>
              <input type="text" name="store_name" class="form-control" value="<?php echo htmlspecialchars($admin['store_name']); ?>" required>
            </div>

            <div class="form-group">
              <label>Administrator / Operator Name *</label>
              <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($admin['full_name']); ?>" required>
            </div>

            <div class="form-group">
              <label>Official Email Address *</label>
              <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($admin['email']); ?>" required>
            </div>

            <div class="form-group">
              <label>Store Contact Number *</label>
              <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($admin['phone']); ?>" required>
            </div>

            <div class="form-group">
              <label>Login Username *</label>
              <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($admin['username']); ?>" required>
            </div>

            <button type="submit" class="btn btn-primary" style="margin-top: 10px;">
              <i class="ri-save-line"></i> Save Profile Details
            </button>
          </form>
        </div>

        <!-- Security & Credentials Card -->
        <div class="content-card">
          <div class="card-head">
            <h3>Security & Password Management</h3>
            <span style="font-size: 12px; color: var(--text-muted);">Credentials</span>
          </div>
          <form action="settings.php" method="POST">
            <input type="hidden" name="action" value="reset_password">

            <div class="form-group">
              <label>Current Password</label>
              <input type="password" name="current_password" class="form-control" placeholder="Enter current password">
            </div>

            <div class="form-group">
              <label>New Password *</label>
              <input type="password" name="new_password" class="form-control" placeholder="Minimum 6 characters" required>
            </div>

            <div class="form-group">
              <label>Confirm New Password *</label>
              <input type="password" name="confirm_password" class="form-control" placeholder="Repeat new password" required>
            </div>

            <div style="background: var(--bg-body); padding: 14px; border-radius: 8px; border: 1px solid var(--border); margin-bottom: 20px;">
              <div style="font-size: 12.5px; color: var(--text-muted); line-height: 1.5;">
                <i class="ri-shield-keyhole-line" style="color: var(--primary);"></i>
            <button type="submit" class="btn btn-outline" style="border-color: var(--border);">
              <i class="ri-lock-password-line"></i> Reset Password
            </button>
          </form>
        </div>
      </div>
    </main>
  </div>

  <script src="js/main.js"></script>
</body>
</html>
