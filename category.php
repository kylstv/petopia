<?php
$xmlFile = 'products1.xml';
$xml = simplexml_load_file($xmlFile);

// Get unique categories
$categories = [];
foreach ($xml->product as $product) {
    $cat = (string)$product->category;
    if ($cat && !in_array($cat, $categories)) {
        $categories[] = $cat;
    }
}

// Add Category
if (isset($_POST['add_category'])) {
    $newCategory = trim($_POST['new_category']);
    if ($newCategory !== '' && !in_array($newCategory, $categories)) {
        // Create a new product element with the new category
        // This ensures the category is saved to XML immediately
        $newProduct = $xml->addChild('product');
        $newProduct->addChild('id', ''); // Empty placeholder
        $newProduct->addChild('name', ''); // Empty placeholder
        $newProduct->addChild('description', ''); // Empty placeholder
        $newProduct->addChild('price', '0'); // Default price
        $newProduct->addChild('image', ''); // Empty placeholder
        $newProduct->addChild('category', $newCategory);
        $newProduct->addChild('stock', '0'); // Default stock
        
        // Save to XML file
        $xml->asXML($xmlFile);
        
        $message = "Category '$newCategory' added successfully.";
        
        // Redirect to refresh the page and show updated categories
        header("Location: " . $_SERVER['PHP_SELF'] . "?success=" . urlencode($message));
        exit;
    } else {
        $message = "Category already exists or empty.";
    }
}

// Check for success message from redirect
if (isset($_GET['success'])) {
    $message = $_GET['success'];
}

// Edit Category
if (isset($_POST['edit_category'])) {
    $oldCategory = $_POST['old_category'];
    $newCategoryName = trim($_POST['new_category_name']);
    if ($newCategoryName !== '' && $oldCategory !== $newCategoryName) {
        foreach ($xml->product as $product) {
            if ((string)$product->category === $oldCategory) {
                $product->category = $newCategoryName;
            }
        }
        $xml->asXML($xmlFile);
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Delete Category
if (isset($_POST['delete_category'])) {
    $delCategory = $_POST['del_category'];
    
    // Remove all products with this category OR just clear the category field
    // Option 1: Remove entire products (commented out)
    /*
    $productsToRemove = [];
    $i = 0;
    foreach ($xml->product as $product) {
        if ((string)$product->category === $delCategory) {
            $productsToRemove[] = $i;
        }
        $i++;
    }
    // Remove in reverse order to maintain indices
    foreach (array_reverse($productsToRemove) as $index) {
        unset($xml->product[$index]);
    }
    */
    
    // Option 2: Just clear the category field (current implementation)
    foreach ($xml->product as $product) {
        if ((string)$product->category === $delCategory) {
            $product->category = '';
        }
    }
    
    $xml->asXML($xmlFile);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Refresh categories after any changes
$categories = [];
foreach ($xml->product as $product) {
    $cat = (string)$product->category;
    if ($cat && !in_array($cat, $categories)) {
        $categories[] = $cat;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Dashboard - Category Management</title>
  <!-- Link to your style.css if needed -->
  <link rel="stylesheet" href="style.css" />
  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Bangers&family=Carter+One&family=Nunito+Sans:wght@400;700&display=swap" rel="stylesheet" />
  <!-- Ionicons -->
  <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
  <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>

  <style>
    /* --- Style mula sa `transactions.php` style --- */
    body {
      font-family: 'Nunito Sans', sans-serif;
      margin: 0;
      padding: 0;
      background-color: #f0f2f5;
    }

    .admin-container {
      display: flex;
      min-height: 100vh;
    }

    /* Sidebar style mula sa `transactions.php` */
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

    /* Main Content */
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

    /* Category Table Styles */
    .category-table {
      width: 100%;
      border-collapse: collapse;
    }
    .category-table th, .category-table td {
      border: 1px solid var(--platinum);
      padding: 12px 15px;
      text-align: left;
    }
    .category-table th {
      background-color: var(--platinum);
      font-weight: var(--fw-700);
      color: var(--eerie-black);
    }
    /* Buttons & Modals styling (from previous style) */
    button {
      padding: 8px 16px;
      border: none;
      border-radius: var(--radius-4);
      cursor: pointer;
      font-weight: var(--fw-700);
      transition: var(--transition-1);
    }
    .btn-primary {
      background-color: var(--portland-orange);
      color: var(--white);
    }
    .btn-primary:hover {
      opacity: 0.9;
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
    /* Modal styles (same as previous) */
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      overflow: auto;
      background-color: rgba(0, 0, 0, 0.4);
    }
    .modal-content {
      background-color: #fff;
      margin: 15% auto;
      padding: 30px;
      border-radius: var(--radius-10);
      width: 400px;
    }
    .close {
      float: right;
      font-size: 24px;
      font-weight: bold;
      cursor: pointer;
    }
    /* Responsive adjustments (optional) */
    @media(max-width: 992px){
      .sidebar {
        width: 80px;
      }
      .main-content {
        margin-left: 80px;
      }
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
  /* Style for the transaction items list */
  ul {
    margin: 0;
    padding-left: 20px;
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
      <h1 class="dashboard-title">Category Management</h1>
      <button class="btn btn-primary" onclick="openAddModal()">
        <ion-icon name="add-outline"></ion-icon> Add Category
      </button>
    </div>

    <?php if (!empty($message)): ?>
      <div class="success-message"><?= $message ?></div>
    <?php endif; ?>

    <!-- Categories Table -->
    <div class="card">
      <div class="card-header">
        <h2 class="card-title">Categories List</h2>
      </div>
      <table class="category-table">
        <thead>
          <tr>
            <th>Category</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($categories as $category): ?>
            <tr>
              <td><?= htmlspecialchars($category) ?></td>
              <td style="min-width:160px;">
                <button class="action-btn edit-btn" onclick="openEditModal('<?= htmlspecialchars($category) ?>')">
                  <ion-icon name="create-outline"></ion-icon> Edit
                </button>
                <button class="action-btn delete-btn" onclick="deleteCategory('<?= htmlspecialchars($category) ?>')">
                  <ion-icon name="trash-outline"></ion-icon> Delete
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>

<!-- Add Category Modal -->
<div id="addModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeAddModal()">×</span>
    <h3>Add New Category</h3>
    <form method="post">
      <input type="text" name="new_category" class="form-control" placeholder="Enter category name" required>
      <button type="submit" name="add_category" class="btn btn-primary">
        <ion-icon name="add-outline"></ion-icon> Add Category
      </button>
    </form>
  </div>
</div>

<!-- Edit Category Modal -->
<div id="editModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeEditModal()">×</span>
    <h3>Edit Category</h3>
    <form method="post">
      <input type="hidden" id="old_category" name="old_category" />
      <input type="text" id="new_category_name" name="new_category_name" class="form-control" placeholder="Enter new category name" required>
      <button type="submit" name="edit_category" class="btn btn-primary">
        <ion-icon name="checkmark-outline"></ion-icon> Update Category
      </button>
    </form>
  </div>
</div>

<!-- Hidden form for delete -->
<form id="deleteForm" method="post" style="display: none;">
  <input type="hidden" name="del_category" id="delete_category_input" />
  <input type="hidden" name="delete_category" value="1" />
</form>

<script>
  function openAddModal() {
    document.getElementById('addModal').style.display = 'block';
  }
  function closeAddModal() {
    document.getElementById('addModal').style.display = 'none';
  }
  function openEditModal(category) {
    document.getElementById('editModal').style.display = 'block';
    document.getElementById('old_category').value = category;
    document.getElementById('new_category_name').value = category;
  }
  function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
  }
  function deleteCategory(category) {
    if (confirm('Are you sure you want to delete this category?')) {
      document.getElementById('delete_category_input').value = category;
      document.getElementById('deleteForm').submit();
    }
  }
  // Close modal if outside click
  window.onclick = function(e) {
    if (e.target.classList.contains('modal')) {
      closeAddModal();
      closeEditModal();
    }
  }
  // Escape key closes modals
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      closeAddModal();
      closeEditModal();
    }
  });
</script>
</body>
</html>