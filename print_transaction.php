<?php
// Get the order reference from URL parameter
$orderRef = $_GET['order'] ?? '';

if (empty($orderRef)) {
    die('Order reference not provided');
}

// Path to your XML file
$xmlFile = 'transactions.xml';

// Load XML data
if (!file_exists($xmlFile)) {
    die('Transaction file not found');
}

$xml = simplexml_load_file($xmlFile);
$transaction = null;

// Find the specific transaction
foreach ($xml->transaction as $txn) {
    if ((string)$txn->orderReference === $orderRef) {
        $transaction = $txn;
        break;
    }
}

if (!$transaction) {
    die('Transaction not found');
}

// Format timestamp
$timestamp = date('F j, Y g:i A', strtotime($transaction->timestamp));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - <?= htmlspecialchars($orderRef) ?></title>
    <style>
        @media print {
            .no-print { display: none; }
            body { margin: 0; }
        }
        
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
        }
        
        .receipt-container {
            border: 2px solid #333;
            padding: 30px;
            background: #fff;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        
        .store-name {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        
        .receipt-title {
            font-size: 18px;
            color: #666;
        }
        
        .info-section {
            margin: 20px 0;
        }
        
        .info-table {
            width: 100%;
            margin-bottom: 30px;
        }
        
        .info-table td {
            padding: 8px 0;
            vertical-align: top;
        }
        
        .label {
            font-weight: bold;
            color: #333;
            width: 150px;
        }
        
        .value {
            color: #666;
        }
        
        .items-section {
            margin: 30px 0;
        }
        
        .items-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #333;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        
        .items-table th {
            background-color: #f8f9fa;
            padding: 12px;
            border: 1px solid #ddd;
            font-weight: bold;
            text-align: left;
        }
        
        .items-table td {
            padding: 12px;
            border: 1px solid #ddd;
        }
        
        .items-table .number {
            text-align: right;
        }
        
        .total-section {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #333;
        }
        
        .total-amount {
            font-size: 20px;
            font-weight: bold;
            text-align: right;
            color: #333;
        }
        
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .print-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">🖨️ Print Receipt</button>
    
    <div class="receipt-container">
        <div class="header">
            <div class="store-name">Petopia</div>
            <div class="receipt-title">OFFICIAL RECEIPT</div>
        </div>

        <div class="info-section">
            <table class="info-table">
                <tr>
                    <td class="label">Order Reference:</td>
                    <td class="value"><?= htmlspecialchars($transaction->orderReference) ?></td>
                </tr>
                <tr>
                    <td class="label">Date & Time:</td>
                    <td class="value"><?= $timestamp ?></td>
                </tr>
                <tr>
                    <td class="label">Customer Email:</td>
                    <td class="value"><?= htmlspecialchars($transaction->email) ?></td>
                </tr>
                <tr>
                    <td class="label">Payment Method:</td>
                    <td class="value"><?= htmlspecialchars($transaction->paymentMethod) ?></td>
                </tr>
            </table>
        </div>

        <div class="items-section">
            <div class="items-title">Items Purchased</div>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th class="number">Qty</th>
                        <th class="number">Unit Price</th>
                        <th class="number">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $grandTotal = 0;
                    foreach ($transaction->items->item as $item):
                        $itemSubtotal = (float)$item->price * (int)$item->quantity;
                        $grandTotal += $itemSubtotal;
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($item->productName) ?></td>
                        <td class="number"><?= htmlspecialchars($item->quantity) ?></td>
                        <td class="number">₱<?= number_format((float)$item->price, 2) ?></td>
                        <td class="number">₱<?= number_format($itemSubtotal, 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="total-section">
            <div class="total-amount">
                TOTAL AMOUNT: ₱<?= number_format((float)$transaction->totalPrice, 2) ?>
            </div>
        </div>

        <div class="footer">
            <p><strong>Thank you for your purchase!</strong></p>
            <p>This receipt was generated on <?= date('F j, Y g:i A') ?></p>
            <p>Order Reference: <?= htmlspecialchars($transaction->orderReference) ?></p>
        </div>
    </div>

    <script>
        // Auto-focus for better printing experience
        window.addEventListener('load', function() {
            // Optional: Auto-print when page loads
            // window.print();
        });
    </script>
</body>
</html>