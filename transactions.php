<?php
// Path to your XML file
$xmlFile = 'transactions.xml';

// Load XML data
if (file_exists($xmlFile)) {
    $xml = simplexml_load_file($xmlFile);
} else {
    $xml = null;
}

// Prepare data for the chart
$salesData = [];
$labels = [];

if ($xml && count($xml->transaction) > 0) {
    // Example: aggregate sales by date (day)
    foreach ($xml->transaction as $txn) {
        $timestamp = strtotime($txn->timestamp);
        $dateLabel = date('Y-m-d', $timestamp);
        if (!isset($salesData[$dateLabel])) {
            $salesData[$dateLabel] = 0;
        }
        $salesData[$dateLabel] += floatval($txn->totalPrice);
    }

    // Sort by date
    ksort($salesData);

    // Prepare labels and data
    $labels = array_keys($salesData);
    $dataPoints = array_values($salesData);
} else {
    $labels = [];
    $dataPoints = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Admin Dashboard - Transactions</title>
<link rel="stylesheet" href="style.css">
<!-- Google Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Bangers&family=Carter+One&family=Nunito+Sans:wght@400;700&display=swap" rel="stylesheet" />
<!-- Ionicons -->
<script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
/* Your existing style adjustments, plus container for the chart */
body {
  font-family: 'Nunito Sans', sans-serif;
  background-color: #f0f2f5;
  margin: 0;
  padding: 0;
}
.admin-container {
  display: flex;
  min-height: 100vh;
}
.sidebar {
  width: 280px;
  background-color: var(--raisin-black);
  color: var(--white);
  padding: 30px 0;
  position: fixed;
  height: 100vh;
  overflow-y: auto;
}
.sidebar-logo {
  padding: 0 25px 25px;
  border-bottom: 1px solid var(--onyx);
  margin-bottom: 25px;
}
.sidebar-logo h1 {
  font-family: 'Bangers', cursive;
  font-size: 24px;
}
.sidebar-nav {
  padding: 0 15px;
}
.sidebar-menu {
  padding: 0;
  margin: 0;
}
.sidebar-menu-item {
  list-style: none;
  margin-bottom: 5px;
}
.sidebar-menu-link {
  display: flex;
  align-items: center;
  padding: 12px 15px;
  color: var(--battleship-gray);
  border-radius: var(--radius-10);
  transition: var(--transition-1);
  text-decoration: none;
}
.sidebar-menu-link.active,
.sidebar-menu-link:hover {
  background-color: var(--eerie-black);
  color: var(--portland-orange);
}
.sidebar-menu-link ion-icon {
  font-size: 20px;
  margin-right: 10px;
}
.main-content {
  margin-left: 280px;
  padding: 30px;
  flex: 1;
}
.dashboard-title {
  font-size: var(--fs-2);
  color: var(--eerie-black);
  font-family: 'Bangers', cursive;
  margin-bottom: 20px;
}
.chart-container {
  background: #fff;
  padding: 20px;
  border-radius: var(--radius-10);
  box-shadow: var(--shadow-1);
  max-width: 1000px;
  margin: 0 auto 30px;
}
canvas {
  max-width: 100%;
  height: auto;
}

/* Table styling for centered layout */
.table-container {
  background: #fff;
  padding: 20px;
  border-radius: var(--radius-10);
  box-shadow: var(--shadow-1);
  margin: 0 auto;
  max-width: 1400px;
  overflow-x: auto;
}

.table-title {
  font-size: 24px;
  color: var(--eerie-black);
  font-family: 'Bangers', cursive;
  margin-bottom: 20px;
  text-align: center;
}

table {
  width: 100%;
  border-collapse: collapse;
  margin: 0 auto;
  background: #fff;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

table th,
table td {
  padding: 12px 15px;
  text-align: left;
  border-bottom: 1px solid #e0e0e0;
  vertical-align: top;
}

table th {
  background-color: #f8f9fa;
  font-weight: 700;
  color: #333;
  text-transform: uppercase;
  font-size: 12px;
  letter-spacing: 0.5px;
}

table tr:hover {
  background-color: #f5f5f5;
}

table td ul {
  margin: 0;
  padding-left: 20px;
}

table td ul li {
  margin-bottom: 5px;
}

.item-detail {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 3px 0;
  border-bottom: 1px solid #eee;
}

.item-detail:last-child {
  border-bottom: none;
}

.item-name {
  font-weight: 500;
  color: #333;
}

.item-qty {
  color: #666;
  font-size: 12px;
}

.item-detail,
.item-price {
  padding: 8px 0;
  margin: 2px 0;
  min-height: 40px;
  display: flex;
  align-items: center;
}

.item-price {
  font-weight: 600;
  color: #007bff;
}

.no-data {
  text-align: center;
  padding: 40px;
  color: #666;
  font-style: italic;
}

.print-btn {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 8px 12px;
  background-color: #007bff;
  color: white;
  text-decoration: none;
  border-radius: 4px;
  font-size: 12px;
  transition: background-color 0.3s ease;
}

.print-btn:hover {
  background-color: #0056b3;
  color: white;
}

.print-btn ion-icon {
  font-size: 14px;
}
</style>
</head>
<body>
<aside class="sidebar">
    <div class="sidebar-logo">
      <h1 class="logo">Store</h1>
    </div>
    <nav class="sidebar-nav">
      <ul class="sidebar-menu">
        <li class="sidebar-menu-item">
          <a href="#" class="sidebar-menu-link">
            <ion-icon name="grid-outline"></ion-icon>
            <span>Dashboard</span>
          </a>
        </li>
        <li class="sidebar-menu-item">
          <a href="admin.html" class="sidebar-menu-link">
            <ion-icon name="bag-outline"></ion-icon>
            <span>Products</span>
          </a>
        </li>
        <li class="sidebar-menu-item">
          <a href="category.php" class="sidebar-menu-link">
            <ion-icon name="pricetags-outline"></ion-icon>
            <span>Categories</span>
          </a>
        </li>
        <li class="sidebar-menu-item">
          <a href="transactions.php" class="sidebar-menu-link active">
            <ion-icon name="cart-outline"></ion-icon>
            <span>Transactions</span>
          </a>
        </li>
        <li class="sidebar-menu-item">
          <a href="customers.php" class="sidebar-menu-link">
            <ion-icon name="people-outline"></ion-icon>
            <span>Customers</span>
          </a>
        <li class="sidebar-menu-item">
          <a href="logout.php" class="sidebar-menu-link">
            <ion-icon name="log-out-outline"></ion-icon>
            <span>Logout</span>
          </a>
        </li>
      </ul>
    </nav>
  </aside>
  
  <!-- Main Content -->
  <main class="main-content">
    <div class="dashboard-title">Sales Overview</div>
    <div class="chart-container">
      <canvas id="salesChart"></canvas>
    </div>
    
    <!-- Centered Transaction Table -->
    <div class="table-container">
      <div class="table-title">Transaction Records</div>
      <?php if ($xml && count($xml->transaction) > 0): ?>
      <table>
        <thead>
          <tr>
            <th>Order Reference</th>
            <th>Email</th>
            <th>Timestamp</th>
            <th>Items</th>
            <th>Price per Item</th>
            <th>Total Price</th>
            <th>Payment Method</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($xml->transaction as $txn): ?>
          <tr>
            <td><?= htmlspecialchars($txn->orderReference) ?></td>
            <td><?= htmlspecialchars($txn->email) ?></td>
            <td><?= htmlspecialchars($txn->timestamp) ?></td>
            <td>
              <?php foreach ($txn->items->item as $item): ?>
                <div class="item-detail">
                  <div class="item-name"><?= htmlspecialchars($item->productName) ?></div>
                  <div class="item-qty">Qty: <?= htmlspecialchars($item->quantity) ?></div>
                </div>
              <?php endforeach; ?>
            </td>
            <td>
              <?php foreach ($txn->items->item as $item): ?>
                <div class="item-price">₱<?= number_format((float)$item->price, 2) ?></div>
              <?php endforeach; ?>
            </td>
            <td>₱<?= number_format((float)$txn->totalPrice, 2) ?></td>
            <td><?= htmlspecialchars($txn->paymentMethod) ?></td>
            <td>
              <a href="print_transaction.php?order=<?= urlencode($txn->orderReference) ?>" 
                 class="print-btn" target="_blank" title="Print PDF">
                <ion-icon name="print-outline"></ion-icon>
                Print PDF
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php else: ?>
      <div class="no-data">No transaction records found.</div>
      <?php endif; ?>
    </div>
  </main>
</div>

<script>
  // Prepare data for the chart
  const labels = <?php echo json_encode($labels); ?>;
  const dataPoints = <?php echo json_encode($dataPoints); ?>;

  const ctx = document.getElementById('salesChart').getContext('2d');
  const salesChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [{
        label: 'Total Sales (₱)',
        data: dataPoints,
        backgroundColor: 'rgba(255, 159, 64, 0.2)',
        borderColor: 'rgba(255, 159, 64, 1)',
        borderWidth: 2,
        fill: true,
        tension: 0.4,
        pointBackgroundColor: 'rgba(255, 159, 64, 1)',
        pointBorderColor: '#fff',
        pointHoverBackgroundColor: '#fff',
        pointHoverBorderColor: 'rgba(255, 159, 64, 1)',
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          display: true,
          position: 'top'
        },
        title: {
          display: false
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: function(value) {
              return '₱' + value.toLocaleString();
            }
          }
        }
      }
    }
  });
</script>
</body>
</html>