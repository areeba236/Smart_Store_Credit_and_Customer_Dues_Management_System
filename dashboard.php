<?php
require_once 'includes/db.php';
checkAuth();
$pageTitle = "Store Credit & Dues Overview";

$total_outstanding = 0.00;
$total_collected_month = 0.00;
$customers_due_count = 0;
$total_customers = 0;

if ($conn) {
    $res1 = mysqli_query($conn, "SELECT 
        COUNT(*) as total_cust,
        SUM(balance) as total_due,
        COUNT(CASE WHEN balance > 0 THEN 1 END) as due_cust
        FROM customers");
    if ($res1 && $row = mysqli_fetch_assoc($res1)) {
        $total_customers = (int)$row['total_cust'];
        $total_outstanding = floatval($row['total_due'] ?? 0);
        $customers_due_count = (int)$row['due_cust'];
    }

    $res2 = mysqli_query($conn, "SELECT SUM(amount) as collected FROM transactions WHERE type='payment_received' AND MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())");
    if ($res2 && $row = mysqli_fetch_assoc($res2)) {
        $total_collected_month = floatval($row['collected'] ?? 0);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Smart Store Credit and Customer Dues Management System</title>
  <link rel="stylesheet" href="css/style.css">
  <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

  <?php include 'includes/sidebar.php'; ?>

  <div class="main-wrapper">
    <?php include 'includes/header.php'; ?>

    <main class="content-area">
      <div class="page-header">
        <div class="header-title">
          <h1>Retail Credit & Dues Dashboard</h1>
          <p>Real-time customer receivables, credit sales, cash recovery and debtor tracking</p>
        </div>
        <div style="display: flex; gap: 12px;">
          <a href="customers.php" class="btn btn-primary">
            <i class="ri-user-add-line"></i> Customer Accounts
          </a>
          <a href="reports.php" class="btn btn-outline">
            <i class="ri-printer-line"></i> Audit Reports
          </a>
        </div>
      </div>

      <!-- 4 High Level Stat Cards -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon icon-red">
            <i class="ri-hand-coin-line"></i>
          </div>
          <div class="stat-info">
            <div class="label">Total Due (Store Receivables)</div>
            <div class="val" style="color: #dc2626;">Rs <?php echo number_format($total_outstanding, 2); ?></div>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon icon-amber">
            <i class="ri-group-line"></i>
          </div>
          <div class="stat-info">
            <div class="label">Debtors with Pending Balance</div>
            <div class="val"><?php echo $customers_due_count; ?> <span style="font-size: 13px; font-weight: 500; color: #64748b;">/ <?php echo $total_customers; ?></span></div>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon icon-green">
            <i class="ri-safe-2-line"></i>
          </div>
          <div class="stat-info">
            <div class="label">Recovered This Month</div>
            <div class="val" style="color: #16a34a;">Rs <?php echo number_format($total_collected_month, 2); ?></div>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon icon-blue">
            <i class="ri-file-list-3-line"></i>
          </div>
          <div class="stat-info">
            <div class="label">Active Customer Accounts</div>
            <div class="val"><?php echo $total_customers; ?></div>
          </div>
        </div>
      </div>

      <!-- Analytical Trend & Quick Action Panels -->
      <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-bottom: 28px;">
        <div class="content-card" style="margin-bottom: 0;">
          <div class="card-head">
            <h3>Store Credit Sales vs Cash Recovery Trends</h3>
            <span style="font-size: 12px; color: var(--text-muted);">6-Month Comparison</span>
          </div>
          <div style="height: 270px; position: relative;">
            <canvas id="trendsChart"></canvas>
          </div>
        </div>

        <div class="content-card" style="margin-bottom: 0;">
          <div class="card-head">
            <h3>Store Operations</h3>
          </div>
          <div style="display: flex; flex-direction: column; gap: 12px;">
            <a href="customers.php" class="btn btn-outline" style="justify-content: flex-start; padding: 14px;">
              <i class="ri-user-add-line" style="color: var(--primary); font-size: 18px;"></i>
              <span>Create Customer Account</span>
            </a>
            <a href="customers.php" class="btn btn-outline" style="justify-content: flex-start; padding: 14px;">
              <i class="ri-arrow-up-circle-line" style="color: #dc2626; font-size: 18px;"></i>
              <span>Give Store Credit</span>
            </a>
            <a href="customers.php" class="btn btn-outline" style="justify-content: flex-start; padding: 14px;">
              <i class="ri-arrow-down-circle-line" style="color: #16a34a; font-size: 18px;"></i>
              <span>Receive Repayment</span>
            </a>
            <a href="settings.php" class="btn btn-outline" style="justify-content: flex-start; padding: 14px;">
              <i class="ri-shield-user-line" style="color: #0284c7; font-size: 18px;"></i>
              <span>Manage Admin Profile & Settings</span>
            </a>
          </div>
        </div>
      </div>

      <!-- Recent Transactions Ledger Activity -->
      <div class="table-card">
        <div style="padding: 20px 24px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
          <h3 style="font-size: 16px; font-weight: 700;">Recent Store Credit Activity</h3>
          <a href="reports.php" style="color: var(--primary); font-size: 13px; font-weight: 700; text-decoration: none;">View Audit Statement &rarr;</a>
        </div>
        <div class="table-responsive">
          <table>
            <thead>
              <tr>
                <th>Date & Time</th>
                <th>Customer Name</th>
                <th>Transaction Nature</th>
                <th>Description / Reason</th>
                <th>Amount</th>
                <th style="text-align: right;">Updated Customer Due</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $tQuery = "SELECT t.*, c.name as customer_name FROM transactions t JOIN customers c ON t.customer_id = c.id ORDER BY t.id DESC LIMIT 5";
              $tRes = $conn ? mysqli_query($conn, $tQuery) : false;
              if ($tRes && mysqli_num_rows($tRes) > 0):
                while ($tr = mysqli_fetch_assoc($tRes)):
                  $isCredit = ($tr['type'] == 'credit_given');
              ?>
              <tr>
                <td><?php echo date('d M Y, h:i A', strtotime($tr['created_at'])); ?></td>
                <td><strong><?php echo htmlspecialchars($tr['customer_name']); ?></strong></td>
                <td>
                  <?php if ($isCredit): ?>
                    <span class="status-badge status-due"><i class="ri-arrow-up-circle-line"></i> Credit Given</span>
                  <?php else: ?>
                    <span class="status-badge status-settled"><i class="ri-arrow-down-circle-line"></i> Payment Received</span>
                  <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($tr['note'] ?: 'Store purchase'); ?></td>
                <td>
                  <strong style="color: <?php echo $isCredit ? '#dc2626' : '#16a34a'; ?>;">
                    <?php echo $isCredit ? '+' : '-'; ?> Rs <?php echo number_format($tr['amount'], 2); ?>
                  </strong>
                </td>
                <td style="text-align: right; font-weight: 700;">
                  Rs <?php echo number_format($tr['current_balance'], 2); ?>
                </td>
              </tr>
              <?php 
                endwhile; 
              else: 
              ?>
              <tr>
                <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 30px;">
                  No transactions recorded yet. Record your first customer credit or repayment!
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
  <script>
    const ctx = document.getElementById('trendsChart').getContext('2d');
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: ['May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct'],
        datasets: [
          {
            label: 'Credit Given (Rs)',
            data: [15000, 22000, 18000, 24000, 19500, <?php echo $total_outstanding; ?>],
            backgroundColor: 'rgba(220, 38, 38, 0.85)',
            borderRadius: 6
          },
          {
            label: 'Cash Recovered (Rs)',
            data: [12000, 19500, 16000, 21000, 17500, <?php echo $total_collected_month; ?>],
            backgroundColor: 'rgba(22, 163, 74, 0.85)',
            borderRadius: 6
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'top' } },
        scales: {
          y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
          x: { grid: { display: false } }
        }
      }
    });
  </script>
</body>
</html>
