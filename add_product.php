<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $file = 'products1.xml';

    if (!file_exists($file)) {
        echo "Error: XML file not found.";
        exit;
    }

    $xml = new DOMDocument();
    $xml->load($file);

    $root = $xml->getElementsByTagName('products')->item(0);

    // Create new product node
    $newProduct = $xml->createElement('product');

    $fields = ['id', 'name', 'price', 'image', 'hoverImage', 'rating', 'reviews', 'category', 'stock'];

    foreach ($fields as $field) {
        $element = $xml->createElement($field, htmlspecialchars($_POST[$field]));
        $newProduct->appendChild($element);
    }

    $root->appendChild($newProduct);
    $xml->formatOutput = true;
    $xml->save($file);

    echo "Product added successfully.";
}
?>