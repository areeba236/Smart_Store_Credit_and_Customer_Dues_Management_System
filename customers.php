<?php
require_once 'includes/db.php';
checkAuth();
$pageTitle = "Customer Accounts Directory";

$message = "";
$msgType = "success";

// 1. Add New Customer Account
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_customer') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $balance = floatval($_POST['balance'] ?? 0);
    $status = ($balance > 0) ? 'Due' : 'Settled';

    if (!empty($name) && !empty($phone)) {
        if ($conn) {
            $stmt = mysqli_prepare($conn, "INSERT INTO customers (name, phone, address, balance, status) VALUES (?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "sssds", $name, $phone, $address, $balance, $status);
            if (mysqli_stmt_execute($stmt)) {
                $newId = mysqli_insert_id($conn);
                if ($balance > 0) {
                    $tStmt = mysqli_prepare($conn, "INSERT INTO transactions (customer_id, type, amount, previous_balance, current_balance, note) VALUES (?, 'credit_given', ?, 0, ?, 'Opening credit balance')");
                    mysqli_stmt_bind_param($tStmt, "idd", $newId, $balance, $balance);
                    mysqli_stmt_execute($tStmt);
                }
                $message = "Customer account registered successfully!";
            } else {
                $message = "Error creating customer account.";
                $msgType = "danger";
            }
        }
    }
}

// 2. Edit / Update Existing Customer Account
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_customer') {
    $id = intval($_POST['customer_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $balance = floatval($_POST['balance'] ?? 0);
    $status = ($balance > 0) ? 'Due' : 'Settled';

    if ($id > 0 && !empty($name) && !empty($phone)) {
        if ($conn) {
            $stmt = mysqli_prepare($conn, "UPDATE customers SET name = ?, phone = ?, address = ?, balance = ?, status = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "sssdsi", $name, $phone, $address, $balance, $status, $id);
            if (mysqli_stmt_execute($stmt)) {
                $message = "Customer account updated successfully!";
            } else {
                $message = "Error updating customer account.";
                $msgType = "danger";
            }
        }
    }
}

// 3. Delete Customer Account
if (isset($_GET['delete_id']) && $conn) {
    $delId = intval($_GET['delete_id']);
    $dStmt = mysqli_prepare($conn, "DELETE FROM customers WHERE id = ?");
    mysqli_stmt_bind_param($dStmt, "i", $delId);
    if (mysqli_stmt_execute($dStmt)) {
        $message = "Customer ledger account removed successfully.";
    }
}

// Fetch Customers List
$customers = [];
if ($conn) {
    $res = mysqli_query($conn, "SELECT * FROM customers ORDER BY id DESC");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $customers[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Customer Accounts & Ledgers - Smart Store Credit</title>
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
          <i class="ri-checkbox-circle-line"></i> <?php echo htmlspecialchars($message); ?>
        </div>
      <?php endif; ?>

      <div class="page-header">
        <div class="header-title">
          <h1>Customer Ledgers</h1>
          <p><span id="customerCount"><?php echo count($customers); ?></span> customer accounts recorded. Click any account to view passbook.</p>
        </div>

        <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
          <div class="search-box">
            <i class="ri-search-line"></i>
            <input type="text" id="searchInput" placeholder="Search by customer name, phone...">
          </div>
          <button class="btn btn-primary" onclick="openAddModal()">
            <i class="ri-user-add-line"></i> Add Customer Account
          </button>
        </div>
      </div>

      <!-- Modern Table Card -->
      <div class="table-card">
        <div class="table-responsive">
          <table id="customerTable">
            <thead>
              <tr>
                <th>Customer Name</th>
                <th>Phone Number</th>
                <th>Outstanding Due</th>
                <th>Account Status</th>
                <th style="text-align: right;">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (count($customers) > 0): ?>
                <?php foreach ($customers as $c): 
                  $initial = strtoupper(substr($c['name'], 0, 1));
                  $bal = floatval($c['balance']);
                  $badgeClass = 'status-settled';
                  $badgeText = 'Settled';

                  if ($bal > 0) {
                      $badgeClass = ($c['status'] == 'Partial') ? 'status-partial' : 'status-due';
                      $badgeText = ($c['status'] == 'Partial') ? 'Partial Payment' : 'Due Balance';
                  }
                ?>
                <tr class="customer-row">
                  <td>
                    <div class="cust-profile">
                      <div class="cust-avatar"><?php echo $initial; ?></div>
                      <div>
                        <a href="customer_ledger.php?id=<?php echo $c['id']; ?>" class="cust-name">
                          <?php echo htmlspecialchars($c['name']); ?>
                        </a>
                        <div class="cust-address"><?php echo htmlspecialchars($c['address'] ?: 'No address specified'); ?></div>
                      </div>
                    </div>
                  </td>
                  <td class="cust-phone"><?php echo htmlspecialchars($c['phone']); ?></td>
                  <td>
                    <strong style="color: <?php echo ($bal > 0) ? '#dc2626' : '#0f172a'; ?>; font-size: 15px;">
                      Rs <?php echo number_format($bal, 2); ?>
                    </strong>
                  </td>
                  <td>
                    <span class="status-badge <?php echo $badgeClass; ?>">
                      <?php echo $badgeText; ?>
                    </span>
                  </td>
                  <td>
                    <div class="action-btns" style="justify-content: flex-end;">
                      <a href="customer_ledger.php?id=<?php echo $c['id']; ?>" class="btn btn-outline" style="padding: 6px 12px; font-size: 12.5px;" title="Open Passbook Statement">
                        <i class="ri-book-open-line" style="color: var(--primary);"></i> Passbook
                      </a>
                      <button class="btn-icon edit" title="Edit Customer Details" 
                        onclick="openEditModal(<?php echo htmlspecialchars(json_encode($c)); ?>)">
                        <i class="ri-edit-line"></i>
                      </button>
                      <a href="customers.php?delete_id=<?php echo $c['id']; ?>" class="btn-icon delete" title="Delete Account" onclick="return confirm('Delete customer account for <?php echo addslashes($c['name']); ?>? All ledger records will be removed.');">
                        <i class="ri-delete-bin-line"></i>
                      </a>
                    </div>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 40px;">
                    No customers found. Click <strong>Add Customer Account</strong> to register your first client.
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>

  <!-- 1. Add Customer Modal -->
  <div class="modal-overlay" id="addCustomerModal">
    <div class="modal-window">
      <div class="modal-header">
        <h3>Add New Customer Account</h3>
        <button class="modal-close" onclick="closeAddModal()"><i class="ri-close-line"></i></button>
      </div>
      <form action="customers.php" method="POST">
        <input type="hidden" name="action" value="add_customer">
        <div class="modal-body">
          <div class="form-group">
            <label>Customer Full Name *</label>
            <input type="text" name="name" class="form-control" placeholder="e.g. Bilal Khan" required>
          </div>
          <div class="form-group">
            <label>Phone Number *</label>
            <input type="text" name="phone" class="form-control" placeholder="e.g. 03245495550" required>
          </div>
          <div class="form-group">
            <label>Address / Reference Area</label>
            <input type="text" name="address" class="form-control" placeholder="e.g. Barkat Market, Faisalabad">
          </div>
          <div class="form-group">
            <label>Opening Due Balance (Rs)</label>
            <input type="number" step="0.01" min="0" name="balance" class="form-control" placeholder="0.00" value="0">
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeAddModal()">Cancel</button>
            <button type="submit" class="btn btn-primary">Save Customer</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- 2. Edit Customer Modal -->
  <div class="modal-overlay" id="editCustomerModal">
    <div class="modal-window">
      <div class="modal-header">
        <h3>Edit Customer Details</h3>
        <button class="modal-close" onclick="closeEditModal()"><i class="ri-close-line"></i></button>
      </div>
      <form action="customers.php" method="POST">
        <input type="hidden" name="action" value="edit_customer">
        <input type="hidden" name="customer_id" id="edit_customer_id">
        <div class="modal-body">
          <div class="form-group">
            <label>Customer Full Name *</label>
            <input type="text" name="name" id="edit_name" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Phone Number *</label>
            <input type="text" name="phone" id="edit_phone" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Address / Reference Area</label>
            <input type="text" name="address" id="edit_address" class="form-control">
          </div>
          <div class="form-group">
            <label>Adjust Balance (Rs) *</label>
            <input type="number" step="0.01" min="0" name="balance" id="edit_balance" class="form-control" required>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeEditModal()">Cancel</button>
            <button type="submit" class="btn btn-primary">Update Details</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <script src="js/main.js"></script>
  <script>
    function openAddModal() {
      document.getElementById('addCustomerModal').classList.add('show');
    }
    function closeAddModal() {
      document.getElementById('addCustomerModal').classList.remove('show');
    }

    function openEditModal(customer) {
      document.getElementById('edit_customer_id').value = customer.id;
      document.getElementById('edit_name').value = customer.name;
      document.getElementById('edit_phone').value = customer.phone;
      document.getElementById('edit_address').value = customer.address || '';
      document.getElementById('edit_balance').value = customer.balance;
      document.getElementById('editCustomerModal').classList.add('show');
    }
    function closeEditModal() {
      document.getElementById('editCustomerModal').classList.remove('show');
    }
  </script>
</body>
</html>
