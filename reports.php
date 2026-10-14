<?php
require_once 'includes/db.php';
checkAuth();
$pageTitle = "Financial Reports & Statement";

$totalCreditGiven = 0;
$totalPaymentsReceived = 0;
$netOutstanding = 0;

if ($conn) {
    $q1 = mysqli_query($conn, "SELECT SUM(amount) as s FROM transactions WHERE type='credit_given'");
    if ($q1 && $r = mysqli_fetch_assoc($q1)) $totalCreditGiven = floatval($r['s'] ?? 0);

    $q2 = mysqli_query($conn, "SELECT SUM(amount) as s FROM transactions WHERE type='payment_received'");
    if ($q2 && $r = mysqli_fetch_assoc($q2)) $totalPaymentsReceived = floatval($r['s'] ?? 0);

    $q3 = mysqli_query($conn, "SELECT SUM(balance) as s FROM customers");
    if ($q3 && $r = mysqli_fetch_assoc($q3)) $netOutstanding = floatval($r['s'] ?? 0);
}

$reports = [];
if ($conn) {
    $rQuery = "SELECT t.*, c.name as customer_name, c.phone as customer_phone 
               FROM transactions t 
               JOIN customers c ON t.customer_id = c.id 
               ORDER BY t.id DESC";
    $res = mysqli_query($conn, $rQuery);
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $reports[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Financial Reports - Smart Store Credit</title>
  <link rel="stylesheet" href="css/style.css">
  <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body>

  <?php include 'includes/sidebar.php'; ?>

  <div class="main-wrapper">
    <?php include 'includes/header.php'; ?>

    <main class="content-area">
      <div class="page-header">
        <div class="header-title">
          <h1>Financial Reports & Audit Trail</h1>
          <p>Complete historical logs of credit extended and cash repayments recovered</p>
        </div>
        <div style="display: flex; gap: 12px;">
          <button class="btn btn-outline" onclick="window.print()">
            <i class="ri-printer-line"></i> Print Audit Report
          </button>
        </div>
      </div>

      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon icon-red">
            <i class="ri-arrow-up-circle-line"></i>
          </div>
          <div class="stat-info">
            <div class="label">Total Credit Extended</div>
            <div class="val" style="color: #dc2626;">Rs <?php echo number_format($totalCreditGiven, 2); ?></div>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon icon-green">
            <i class="ri-arrow-down-circle-line"></i>
          </div>
          <div class="stat-info">
            <div class="label">Total Cash Recovered</div>
            <div class="val" style="color: #16a34a;">Rs <?php echo number_format($totalPaymentsReceived, 2); ?></div>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon icon-amber">
            <i class="ri-wallet-3-line"></i>
          </div>
          <div class="stat-info">
            <div class="label">Net Current Receivables</div>
            <div class="val">Rs <?php echo number_format($netOutstanding, 2); ?></div>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon icon-blue">
            <i class="ri-list-check-2"></i>
          </div>
          <div class="stat-info">
            <div class="label">Total Recorded Transactions</div>
            <div class="val"><?php echo count($reports); ?></div>
          </div>
        </div>
      </div>

      <div class="table-card">
        <div style="padding: 18px 24px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
          <h3 style="font-size: 16px; font-weight: 700;">Consolidated Transaction Log</h3>
          <div style="font-size: 13px; color: var(--text-muted);">Real-time Audit Log</div>
        </div>
        <div class="table-responsive">
          <table>
            <thead>
              <tr>
                <th>Date & Time</th>
                <th>Customer</th>
                <th>Phone</th>
                <th>Action Nature</th>
                <th>Description</th>
                <th>Amount</th>
                <th style="text-align: right;">Post-Balance</th>
              </tr>
            </thead>
            <tbody>
              <?php if (count($reports) > 0): ?>
                <?php foreach ($reports as $r): 
                  $isCredit = ($r['type'] === 'credit_given');
                ?>
                <tr>
                  <td><?php echo date('d M Y, h:i A', strtotime($r['created_at'])); ?></td>
                  <td><strong><?php echo htmlspecialchars($r['customer_name']); ?></strong></td>
                  <td><?php echo htmlspecialchars($r['customer_phone']); ?></td>
                  <td>
                    <?php if ($isCredit): ?>
                      <span class="status-badge status-due">Credit Given</span>
                    <?php else: ?>
                      <span class="status-badge status-settled">Payment Received</span>
                    <?php endif; ?>
                  </td>
                  <td><?php echo htmlspecialchars($r['note'] ?: '-'); ?></td>
                  <td>
                    <strong style="color: <?php echo $isCredit ? '#dc2626' : '#16a34a'; ?>;">
                      <?php echo $isCredit ? '+' : '-'; ?> Rs <?php echo number_format($r['amount'], 2); ?>
                    </strong>
                  </td>
                  <td style="text-align: right; font-weight: 700;">
                    Rs <?php echo number_format($r['current_balance'], 2); ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 40px;">
                    No report records available.
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>

  <script src="js/main.js"></script>
</body>
</html>
