<?php
// save_transaction.php

// Paths to your XML files
$transactionXmlFile = 'transactions.xml'; // Changed to match your display file
$productsXmlFile = 'products1.xml';

// Get JSON POST data
$transactionData = json_decode(file_get_contents('php://input'), true);

if (!$transactionData) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid data']);
    exit;
}

// Load or create transactions XML
if (file_exists($transactionXmlFile)) {
    $xml = simplexml_load_file($transactionXmlFile);
    if ($xml === false) {
        $xml = new SimpleXMLElement('<transactions></transactions>');
    }
} else {
    $xml = new SimpleXMLElement('<transactions></transactions>');
}

// Initialize products XML object
if (!file_exists($productsXmlFile)) {
    http_response_code(500);
    echo json_encode(['error' => 'Products data not found']);
    exit;
}

// Use file locking to prevent race conditions
$fp = fopen($productsXmlFile, 'r+');
if (!$fp) {
    http_response_code(500);
    echo json_encode(['error' => 'Unable to open products file']);
    exit;
}

if (flock($fp, LOCK_EX)) {
    // Read current products XML
    $filesize = filesize($productsXmlFile);
    if ($filesize > 0) {
        $productsXmlStr = fread($fp, $filesize);
        $productsXml = simplexml_load_string($productsXmlStr);
        if ($productsXml === false) {
            fclose($fp);
            http_response_code(500);
            echo json_encode(['error' => 'Failed to parse products data']);
            exit;
        }
    } else {
        // If empty, create new
        $productsXml = new SimpleXMLElement('<products></products>');
    }

    // Process each purchased item to update stock
    foreach ($transactionData['items'] as $item) {
        $productId = $item['productId'];
        $quantityPurchased = (int)$item['quantity'];

        $productFound = false;
        foreach ($productsXml->product as $product) {
            if ((string)$product->id === $productId) {
                $productFound = true;
                $currentStock = (int)$product->stock;
                $newStock = $currentStock - $quantityPurchased;
                if ($newStock < 0) {
                    $newStock = 0; // prevent negative stock
                }
                $product->stock = $newStock;
                break;
            }
        }
        // Optional: handle case if product not found
        if (!$productFound) {
            // Could log or handle missing product
            // For now, just continue
        }
    }

    // Rewind and write updated products back to file
    rewind($fp);
    ftruncate($fp, 0); // Clear the file first
    fwrite($fp, $productsXml->asXML());
    fflush($fp);
    flock($fp, LOCK_UN);
} else {
    fclose($fp);
    http_response_code(500);
    echo json_encode(['error' => 'Could not lock products file']);
    exit;
}
fclose($fp);

// Save transaction details
$transaction = $xml->addChild('transaction');
$transaction->addChild('orderReference', htmlspecialchars($transactionData['orderReference']));
$transaction->addChild('email', htmlspecialchars($transactionData['email']));
$transaction->addChild('totalPrice', htmlspecialchars($transactionData['totalPrice']));
$transaction->addChild('paymentMethod', htmlspecialchars($transactionData['paymentMethod']));
$transaction->addChild('timestamp', date('Y-m-d\TH:i:s'));

// Add items section
$items = $transaction->addChild('items');

foreach ($transactionData['items'] as $item) {
    $itemNode = $items->addChild('item');
    $itemNode->addChild('productId', htmlspecialchars($item['productId']));
    $itemNode->addChild('productName', htmlspecialchars($item['productName']));
    $itemNode->addChild('quantity', htmlspecialchars($item['quantity']));
    
    // Make sure to save the unit price and subtotal properly
    $unitPrice = isset($item['unitPrice']) ? $item['unitPrice'] : (isset($item['price']) ? $item['price'] : '0');
    $subtotal = isset($item['subtotal']) ? $item['subtotal'] : ($unitPrice * $item['quantity']);
    
    $itemNode->addChild('price', htmlspecialchars($unitPrice)); // This matches what your display expects
    $itemNode->addChild('unitPrice', htmlspecialchars($unitPrice)); // Additional field for clarity
    $itemNode->addChild('subtotal', htmlspecialchars($subtotal));
}

// Save transactions XML with proper formatting
$dom = new DOMDocument('1.0', 'UTF-8');
$dom->formatOutput = true;
$dom->loadXML($xml->asXML());
$dom->save($transactionXmlFile);

// Respond with success
echo json_encode([
    'status' => 'success',
    'message' => 'Transaction saved successfully',
    'orderReference' => $transactionData['orderReference']
]);
?>