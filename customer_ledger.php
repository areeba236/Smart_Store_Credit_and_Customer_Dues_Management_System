<?php
require_once 'includes/db.php';
checkAuth();
$pageTitle = "Customer Ledger Statement";

$customerId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$alertMessage = "";
$alertType = "success";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_transaction') {
    $type = $_POST['type'];
    $amount = floatval($_POST['amount'] ?? 0);
    $note = trim($_POST['note'] ?? '');

    if ($amount > 0 && $customerId > 0 && $conn) {
        $stmt = mysqli_prepare($conn, "SELECT balance FROM customers WHERE id = ? FOR UPDATE");
        mysqli_stmt_bind_param($stmt, "i", $customerId);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        if ($cRow = mysqli_fetch_assoc($res)) {
            $prevBal = floatval($cRow['balance']);
            if ($type === 'credit_given') {
                $newBal = $prevBal + $amount;
            } else {
                $newBal = max(0, $prevBal - $amount);
            }

            if ($newBal <= 0) {
                $newStatus = 'Settled';
            } elseif ($prevBal > 0 && $newBal < $prevBal) {
                $newStatus = 'Partial';
            } else {
                $newStatus = 'Due';
            }

            $uStmt = mysqli_prepare($conn, "UPDATE customers SET balance = ?, status = ? WHERE id = ?");
            mysqli_stmt_bind_param($uStmt, "dsi", $newBal, $newStatus, $customerId);
            mysqli_stmt_execute($uStmt);

            $tStmt = mysqli_prepare($conn, "INSERT INTO transactions (customer_id, type, amount, previous_balance, current_balance, note) VALUES (?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($tStmt, "isddds", $customerId, $type, $amount, $prevBal, $newBal, $note);
            mysqli_stmt_execute($tStmt);

            $alertMessage = ($type === 'credit_given') ? "Store credit recorded successfully!" : "Cash repayment received and balance adjusted successfully!";
        }
    } else {
        $alertMessage = "Please enter an amount greater than 0.";
        $alertType = "danger";
    }
}

$customer = null;
if ($customerId > 0 && $conn) {
    $cQuery = mysqli_query($conn, "SELECT * FROM customers WHERE id = $customerId LIMIT 1");
    if ($cQuery) {
        $customer = mysqli_fetch_assoc($cQuery);
    }
}

if (!$customer) {
    header("Location: customers.php");
    exit;
}

$transactions = [];
if ($conn) {
    $tRes = mysqli_query($conn, "SELECT * FROM transactions WHERE customer_id = $customerId ORDER BY id DESC");
    if ($tRes) {
        while ($row = mysqli_fetch_assoc($tRes)) {
            $transactions[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($customer['name']); ?> - Ledger Passbook</title>
  <link rel="stylesheet" href="css/style.css">
  <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body>

  <?php include 'includes/sidebar.php'; ?>

  <div class="main-wrapper">
    <?php include 'includes/header.php'; ?>

    <main class="content-area">
      <?php if (!empty($alertMessage)): ?>
        <div class="alert alert-<?php echo $alertType; ?>">
          <i class="ri-information-line"></i> <?php echo htmlspecialchars($alertMessage); ?>
        </div>
      <?php endif; ?>

      <div class="page-header">
        <div class="header-title">
          <div style="display: flex; align-items: center; gap: 10px;">
            <a href="customers.php" class="btn btn-outline" style="padding: 6px 12px;"><i class="ri-arrow-left-line"></i> Back to Accounts</a>
            <h1><?php echo htmlspecialchars($customer['name']); ?></h1>
          </div>
          <p>Contact: <?php echo htmlspecialchars($customer['phone']); ?> | Address: <?php echo htmlspecialchars($customer['address'] ?: 'Not recorded'); ?></p>
        </div>

        <div style="display: flex; gap: 12px;">
          <button class="btn btn-danger" onclick="openTxModal('credit_given')">
            <i class="ri-add-circle-line"></i> Give Credit (Items Taken)
          </button>
          <button class="btn btn-primary" onclick="openTxModal('payment_received')">
            <i class="ri-checkbox-circle-line"></i> Receive Repayment (Cash Repaid)
          </button>
        </div>
      </div>

      <!-- Current Balance Banner -->
      <div class="stats-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 28px;">
        <div class="stat-card">
          <div class="stat-icon icon-red">
            <i class="ri-money-dollar-circle-line"></i>
          </div>
          <div class="stat-info">
            <div class="label">Current Due Balance</div>
            <div class="val" style="color: <?php echo ($customer['balance'] > 0) ? '#dc2626' : '#16a34a'; ?>;">
              Rs <?php echo number_format($customer['balance'], 2); ?>
            </div>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon icon-blue">
            <i class="ri-shield-check-line"></i>
          </div>
          <div class="stat-info">
            <div class="label">Account Status</div>
            <div class="val" style="font-size: 20px; text-transform: uppercase;">
              <?php 
                $st = $customer['status'];
                $cls = ($st == 'Settled') ? 'status-settled' : (($st == 'Partial') ? 'status-partial' : 'status-due');
                echo "<span class='status-badge $cls'>$st</span>";
              ?>
            </div>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon icon-amber">
            <i class="ri-history-line"></i>
          </div>
          <div class="stat-info">
            <div class="label">Total Recorded Transactions</div>
            <div class="val"><?php echo count($transactions); ?> Entries</div>
          </div>
        </div>
      </div>

      <!-- Transaction Passbook Table -->
      <div class="table-card">
        <div style="padding: 18px 24px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
          <h3 style="font-size: 16px; font-weight: 700;">Account Statement & Passbook</h3>
          <button class="btn btn-outline" style="padding: 6px 14px;" onclick="window.print()">
            <i class="ri-printer-line"></i> Print Customer Passbook
          </button>
        </div>
        <div class="table-responsive">
          <table>
            <thead>
              <tr>
                <th>Date & Time</th>
                <th>Transaction Type</th>
                <th>Description / Reason</th>
                <th>Debit / Credit Given</th>
                <th>Repayment Received</th>
                <th style="text-align: right;">Remaining Due Balance</th>
              </tr>
            </thead>
            <tbody>
              <?php if (count($transactions) > 0): ?>
                <?php foreach ($transactions as $t): 
                  $isCredit = ($t['type'] === 'credit_given');
                ?>
                <tr>
                  <td><?php echo date('d M Y, h:i A', strtotime($t['created_at'])); ?></td>
                  <td>
                    <?php if ($isCredit): ?>
                      <span class="status-badge status-due">Credit Given</span>
                    <?php else: ?>
                      <span class="status-badge status-settled">Payment Received</span>
                    <?php endif; ?>
                  </td>
                  <td><?php echo htmlspecialchars($t['note'] ?: '-'); ?></td>
                  <td>
                    <?php echo $isCredit ? '<strong style="color: #dc2626;">+ Rs ' . number_format($t['amount'], 2) . '</strong>' : '-'; ?>
                  </td>
                  <td>
                    <?php echo !$isCredit ? '<strong style="color: #16a34a;">- Rs ' . number_format($t['amount'], 2) . '</strong>' : '-'; ?>
                  </td>
                  <td style="text-align: right; font-weight: 700;">
                    Rs <?php echo number_format($t['current_balance'], 2); ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 36px;">
                    No ledger transactions recorded for this customer yet.
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>

  <!-- Transaction Modal -->
  <div class="modal-overlay" id="txModal">
    <div class="modal-window">
      <div class="modal-header">
        <h3 id="txModalTitle">Record Transaction</h3>
        <button class="modal-close" onclick="closeTxModal()"><i class="ri-close-line"></i></button>
      </div>
      <form action="customer_ledger.php?id=<?php echo $customerId; ?>" method="POST">
        <input type="hidden" name="action" value="save_transaction">
        <input type="hidden" name="type" id="txTypeInput" value="credit_given">
        
        <div class="modal-body">
          <div style="background: var(--bg-body); border: 1px solid var(--border); padding: 12px 16px; border-radius: 8px; margin-bottom: 18px;">
            <div style="font-size: 13px; color: var(--text-muted);">Current Outstanding:</div>
            <div style="font-size: 18px; font-weight: 800; color: #dc2626;">Rs <?php echo number_format($customer['balance'], 2); ?></div>
          </div>

          <div class="form-group">
            <label id="txAmountLabel">Amount (Rs) *</label>
            <input type="number" step="0.01" min="1" name="amount" class="form-control" placeholder="Enter amount (e.g. 500)" required>
          </div>

          <div class="form-group">
            <label>Description / Note</label>
            <input type="text" name="note" class="form-control" placeholder="e.g. Grocery items on credit or Cash repayment">
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeTxModal()">Cancel</button>
            <button type="submit" id="txSubmitBtn" class="btn btn-primary">Save Transaction</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <script src="js/main.js"></script>
  <script>
    function openTxModal(type) {
      const modal = document.getElementById('txModal');
      const title = document.getElementById('txModalTitle');
      const typeInput = document.getElementById('txTypeInput');
      const submitBtn = document.getElementById('txSubmitBtn');
      const amountLabel = document.getElementById('txAmountLabel');

      typeInput.value = type;
      if (type === 'credit_given') {
        title.innerText = "Give Credit (Store Items / Udhaar)";
        amountLabel.innerText = "Credit Amount (Rs) *";
        submitBtn.className = "btn btn-danger";
        submitBtn.innerText = "Add to Customer Due";
      } else {
        title.innerText = "Receive Repayment (Cash Repaid)";
        amountLabel.innerText = "Repayment Amount (Rs) *";
        submitBtn.className = "btn btn-primary";
        submitBtn.innerText = "Deduct from Customer Due";
      }
      modal.classList.add('show');
    }

    function closeTxModal() {
      document.getElementById('txModal').classList.remove('show');
    }
  </script>
</body>
</html>
