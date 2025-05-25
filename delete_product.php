<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $xmlFile = 'products1.xml';
    $idToDelete = $_POST['id'];

    if (!file_exists($xmlFile)) {
        echo 'XML file not found.';
        exit;
    }

    // Load the XML as a DOM document
    $dom = new DOMDocument('1.0');
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;
    $dom->load($xmlFile);

    // Create an XPath object to search for the product
    $xpath = new DOMXPath($dom);
    $query = "/products/product[id='" . htmlspecialchars($idToDelete, ENT_QUOTES) . "']";
    $products = $xpath->query($query);

    $found = false;
    if ($products->length > 0) {
        // Get the first product matching our criteria
        $productNode = $products->item(0);
        // Remove the product from its parent node
        $productNode->parentNode->removeChild($productNode);
        $found = true;
        
        // Save the XML back to file
        $dom->save($xmlFile);
    }

    if ($found) {
        echo 'Product deleted successfully.';
    } else {
        echo 'Product ID not found.';
    }
} else {
    echo 'Invalid request method.';
}
?>