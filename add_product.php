<?php
header('Content-Type: text/plain');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $file = 'products1.xml';

    if (!file_exists($file)) {
        echo "Error: XML file not found.";
        exit;
    }

    // Load XML with error handling
    $xml = new DOMDocument();
    $xml->preserveWhiteSpace = false;
    $xml->formatOutput = true;
    
    if (!$xml->load($file)) {
        echo "Error: Could not load XML file.";
        exit;
    }

    $root = $xml->getElementsByTagName('products')->item(0);
    
    if (!$root) {
        echo "Error: No 'products' root element found.";
        exit;
    }

    // Validate required fields
    $fields = ['id', 'name', 'price', 'image', 'hoverImage', 'rating', 'reviews', 'category', 'stock'];
    
    foreach ($fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            echo "Error: Missing required field: $field";
            exit;
        }
    }

    // Check if product ID already exists
    $existingProducts = $xml->getElementsByTagName('product');
    foreach ($existingProducts as $product) {
        $existingId = $product->getElementsByTagName('id')->item(0)->textContent;
        if ($existingId == $_POST['id']) {
            echo "Error: Product with ID " . $_POST['id'] . " already exists.";
            exit;
        }
    }

    // Create new product node
    $newProduct = $xml->createElement('product');

    foreach ($fields as $field) {
        $element = $xml->createElement($field);
        $element->appendChild($xml->createTextNode($_POST[$field]));
        $newProduct->appendChild($element);
    }

    $root->appendChild($newProduct);
    
    // Save with error handling
    if ($xml->save($file)) {
        echo "Product added successfully.";
    } else {
        echo "Error: Could not save XML file.";
    }
} else {
    echo "Error: Invalid request method.";
}
?>