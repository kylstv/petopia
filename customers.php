<?php

include "connect.php";

// Verify admin password function
function verifyAdminPassword($pdo, $password) {
    try {
        $stmt = $pdo->prepare("SELECT password FROM admin WHERE id = 1");
        $stmt->execute();
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($admin) {
            // Password verification (adjust if hashed)
            return $password === $admin['password'];
        }
        return false;
    } catch(PDOException $e) {
        return false;
    }
}

// Handle delete request
if (isset($_POST['verify_delete'])) {
    $adminPassword = $_POST['admin_password'];
    $customerId = $_POST['customer_id'];
    if (verifyAdminPassword($pdo, $adminPassword)) {
        try {
            $stmt = $pdo->prepare("DELETE FROM signup WHERE id = :id");
            $stmt->bindParam(':id', $customerId);
            $stmt->execute();
            $message = "Customer deleted successfully!";
        } catch(PDOException $e) {
            $error = "Error deleting customer: " . $e->getMessage();
        }
    } else {
        $error = "Invalid admin password. Customer deletion failed.";
    }
}

// Handle status update
if (isset($_POST['verify_update'])) {
    $adminPassword = $_POST['admin_password'];
    $customerId = $_POST['customer_id'];
    $newStatus = $_POST['status'];
    if (verifyAdminPassword($pdo, $adminPassword)) {
        try {
            $stmt = $pdo->prepare("UPDATE signup SET status = :status WHERE id = :id");
            $stmt->bindParam(':status', $newStatus);
            $stmt->bindParam(':id', $customerId);
            $stmt->execute();
            $message = "Customer status updated successfully!";
        } catch(PDOException $e) {
            $error = "Error updating status: " . $e->getMessage();
        }
    } else {
        $error = "Invalid admin password. Status update failed.";
    }
}

// Fetch customers
try {
    $stmt = $pdo->query("SELECT * FROM signup ORDER BY id DESC");
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $customers = [];
    $error = "Error fetching customers: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Admin Dashboard - Customers</title>
<!-- Style and fonts (from transactions.php) -->
<link rel="stylesheet" href="style.css" />
<!-- Google Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Bangers&family=Carter+One&family=Nunito+Sans:wght@400;700&display=swap" rel="stylesheet" />
<!-- Ionicons -->
<script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>

<!-- Style (from transactions.php style) -->
<style>
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
.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
}
.dashboard-title {
  font-size: var(--fs-2);
  color: var(--eerie-black);
  font-family: 'Bangers', cursive;
}
table {
  width: 100%;
  border-collapse: collapse;
  background-color: #fff;
  border-radius: var(--radius-10);
  box-shadow: var(--shadow-1);
}
th, td {
  padding: 12px 15px;
  border-bottom: 1px solid var(--platinum);
  text-align: left;
}
th {
  background-color: var(--platinum);
  font-weight: var(--fw-700);
  color: var(--eerie-black);
}
tr:nth-child(even) {
  background-color: #fafafa;
}
.action-btn {
  padding: 8px 12px;
  margin-right: 5px;
  margin-bottom: 5px;
  border-radius: var(--radius-4);
  cursor: pointer;
  border: none;
  font-size: var(--fs-6);
  font-weight: var(--fw-700);
  display: inline-flex;
  align-items: center;
  gap: 5px;
  transition: var(--transition-1);
}
.edit-btn {
  background-color: var(--amber);
  color: var(--black);
}
.edit-btn:hover {
  background-color: hsl(45, 100%, 45%);
}
.delete-btn {
  background-color: var(--bittersweet);
  color: var(--white);
}
.delete-btn:hover {
  background-color: hsl(9, 96%, 60%);
}
.status-badge {
  padding: 4px 8px;
  border-radius: var(--radius-4);
  font-size: var(--fs-7);
  font-weight: var(--fw-700);
}
.status-verified {
  background-color: rgba(34, 197, 94, 0.1);
  color: rgb(34, 197, 94);
}
.status-unverified {
  background-color: rgba(239, 68, 68, 0.1);
  color: rgb(239, 68, 68);
}
.status-blocked {
  background-color: rgba(107, 114, 128, 0.1);
  color: rgb(107, 114, 128);
}
/* Additional modal styles for confirmation dialogs */
.modal {
  display: none; /* Hidden by default */
  position: fixed;
  z-index: 1000;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  overflow: auto;
  background-color: rgba(0,0,0,0.4);
}
.modal-content {
  background-color: #fff;
  margin: 10% auto;
  padding: 20px;
  border-radius: var(--radius-10);
  width: 400px;
  max-width: 90%;
}
.close {
  float: right;
  font-size: 28px;
  font-weight: bold;
  cursor: pointer;
}
</style>
</head>
<body>
<div class="admin-container">
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
    <div class="dashboard-header">
      <h1 class="dashboard-title">Customer Records</h1>
    </div>
    <?php if ($customers): ?>
    <input type="text" id="searchCustomer" placeholder="Search customers..." style="margin-bottom:15px; padding:8px; width:100%; max-width:300px; border-radius:4px; border:1px solid #ccc;">
    <table id="customerTable">
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Email</th>
          <th>Username</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($customers as $customer): ?>
        <tr>
          <td><?= htmlspecialchars($customer['id']) ?></td>
          <td><?= htmlspecialchars($customer['first_name'].' '.($customer['middle_name']??'').' '.$customer['last_name']) ?></td>
          <td><?= htmlspecialchars($customer['email']) ?></td>
          <td><?= htmlspecialchars($customer['username']) ?></td>
          <td>
            <?php
            $status = $customer['status'] ?? 'verified';
            $statusClass = 'status-' . strtolower($status);
            ?>
            <span class="status-badge <?= $statusClass ?>"><?= ucfirst($status) ?></span>
          </td>
          <td style="min-width:160px;">
            <button class="action-btn edit-btn" onclick="showEditConfirmation(<?= $customer['id'] ?>, '<?= $status ?>')">
              <ion-icon name="create-outline"></ion-icon> Edit
            </button>
            <button class="action-btn delete-btn" onclick="showDeleteConfirmation(<?= $customer['id'] ?>)">
              <ion-icon name="trash-outline"></ion-icon> Delete
            </button>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php else: ?>
    <p>No customer data available.</p>
    <?php endif; ?>
  </main>
</div>

<!-- Modal for edit confirmation -->
<div id="editConfirmModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeEditConfirmModal()">&times;</span>
    <h3>Admin Authentication</h3>
    <p>Enter admin password to change status:</p>
    <input type="password" id="editAdminPassword" placeholder="Admin Password" style="width:100%; padding:8px; margin-bottom:10px; border-radius:4px; border:1px solid #ccc;">
    <div id="editPasswordError" style="color:red; display:none;">Invalid admin password</div>
    <div style="margin-top:10px;">
      <label for="editStatusSelect">New Status:</label>
      <select id="editStatusSelect" style="width:100%; padding:8px; margin-top:5px;">
        <option value="verified">Verified</option>
        <option value="unverified">Unverified</option>
        <option value="blocked">Blocked</option>
      </select>
    </div>
    <div style="margin-top:15px; text-align:right;">
      <button onclick="closeEditConfirmModal()" style="margin-right:10px;">Cancel</button>
      <button onclick="confirmEdit()">Update</button>
    </div>
  </div>
</div>

<!-- Modal for delete confirmation -->
<div id="deleteConfirmModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeDeleteConfirmModal()">&times;</span>
    <h3>Admin Authentication</h3>
    <p>Enter admin password to delete customer:</p>
    <input type="password" id="deleteAdminPassword" placeholder="Admin Password" style="width:100%; padding:8px; margin-bottom:10px; border-radius:4px; border:1px solid #ccc;">
    <div id="deletePasswordError" style="color:red; display:none;">Invalid admin password</div>
    <p style="margin-top:10px; color:red; font-weight:bold;">⚠️ This action cannot be undone!</p>
    <div style="margin-top:15px; text-align:right;">
      <button onclick="closeDeleteConfirmModal()" style="margin-right:10px;">Cancel</button>
      <button onclick="confirmDelete()">Delete</button>
    </div>
  </div>
</div>

<!-- Hidden forms for submission -->
<form id="editForm" method="post" style="display:none;">
  <input type="hidden" name="customer_id" id="editCustomerId" />
  <input type="hidden" name="status" id="editStatus" />
  <input type="hidden" name="admin_password" id="editAdminPasswordHidden" />
  <input type="hidden" name="verify_update" value="1" />
</form>
<form id="deleteForm" method="post" style="display:none;">
  <input type="hidden" name="customer_id" id="deleteCustomerId" />
  <input type="hidden" name="admin_password" id="deleteAdminPasswordHidden" />
  <input type="hidden" name="verify_delete" value="1" />
</form>

<script>
const customers = <?= json_encode($customers) ?>;
let selectedCustomerId = null;

// Show edit confirmation modal
function showEditConfirmation(id, status) {
  selectedCustomerId = id;
  document.getElementById('editStatusSelect').value = status;
  document.getElementById('editAdminPassword').value = '';
  document.getElementById('editPasswordError').style.display = 'none';
  document.getElementById('editConfirmModal').style.display = 'block';
  setTimeout(() => { document.getElementById('editAdminPassword').focus(); }, 100);
}
function closeEditConfirmModal() {
  document.getElementById('editConfirmModal').style.display = 'none';
  selectedCustomerId = null;
}
function confirmEdit() {
  const adminPass = document.getElementById('editAdminPassword').value;
  const newStatus = document.getElementById('editStatusSelect').value;
  if (!adminPass) {
    document.getElementById('editPasswordError').style.display = 'block';
    return;
  }
  document.getElementById('editCustomerId').value = selectedCustomerId;
  document.getElementById('editStatus').value = newStatus;
  document.getElementById('editAdminPasswordHidden').value = adminPass;
  document.getElementById('editForm').submit();
}

// Show delete confirmation modal
function showDeleteConfirmation(id) {
  selectedCustomerId = id;
  document.getElementById('deleteAdminPassword').value = '';
  document.getElementById('deletePasswordError').style.display = 'none';
  document.getElementById('deleteConfirmModal').style.display = 'block';
  setTimeout(() => { document.getElementById('deleteAdminPassword').focus(); }, 100);
}
function closeDeleteConfirmModal() {
  document.getElementById('deleteConfirmModal').style.display = 'none';
}
function confirmDelete() {
  const adminPass = document.getElementById('deleteAdminPassword').value;
  if (!adminPass) {
    document.getElementById('deletePasswordError').style.display = 'block';
    return;
  }
  document.getElementById('deleteCustomerId').value = selectedCustomerId;
  document.getElementById('deleteAdminPasswordHidden').value = adminPass;
  document.getElementById('deleteForm').submit();
}

// Search filter
document.getElementById('searchCustomer')?.addEventListener('input', function() {
  const filter = this.value.toLowerCase();
  document.querySelectorAll('#customerTable tbody tr').forEach(row => {
    const name = row.cells[1].textContent.toLowerCase();
    const email = row.cells[2].textContent.toLowerCase();
    const username = row.cells[3].textContent.toLowerCase();
    if (name.includes(filter) || email.includes(filter) || username.includes(filter)) {
      row.style.display = '';
    } else {
      row.style.display = 'none';
    }
  });
});

// Modal close on outside click
window.onclick = function(e) {
  if (e.target.classList.contains('modal')) {
    closeEditConfirmModal();
    closeDeleteConfirmModal();
  }
};
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    closeEditConfirmModal();
    closeDeleteConfirmModal();
  }
});
</script>
</body>
</html>