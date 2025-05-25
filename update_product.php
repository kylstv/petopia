<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $image = $_POST['image'];
    $hoverImage = $_POST['hoverImage'];
    $rating = $_POST['rating'];
    $reviews = $_POST['reviews'];
    $category = $_POST['category'];
    $stock = $_POST['stock'];

    $file = 'products1.xml';

    if (!file_exists($file)) {
        echo "XML file not found.";
        exit;
    }

    $xml = new DOMDocument();
    $xml->load($file);

    $products = $xml->getElementsByTagName('product');
    foreach ($products as $product) {
        $productId = $product->getElementsByTagName('id')->item(0)->nodeValue;
        if ($productId == $id) {
            $product->getElementsByTagName('name')->item(0)->nodeValue = $name;
            $product->getElementsByTagName('price')->item(0)->nodeValue = $price;
            $product->getElementsByTagName('image')->item(0)->nodeValue = $image;
            $product->getElementsByTagName('hoverImage')->item(0)->nodeValue = $hoverImage;
            $product->getElementsByTagName('rating')->item(0)->nodeValue = $rating;
            $product->getElementsByTagName('reviews')->item(0)->nodeValue = $reviews;
            $product->getElementsByTagName('category')->item(0)->nodeValue = $category;
            $product->getElementsByTagName('stock')->item(0)->nodeValue = $stock;
            break;
        }
    }

    $xml->formatOutput = true;
    $xml->save($file);
    echo "Product updated successfully.";
}
?>
