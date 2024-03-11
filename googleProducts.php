<?php
// Include database configuration
require_once 'database/databaseconnection.php';

// geting  all product using sql query from database 
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // creating query to get products 
    $allProductQuery = $pdo->query('SELECT * FROM google_products');
    // fetching all productdetails and saving in googleProduct variable
    $googleProducts = $allProductQuery->fetchAll(PDO::FETCH_ASSOC);
    // display all details in json foam
    echo json_encode($googleProducts);
}

// creating new product in database using query 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try{
    // geting input from client and decoding json data to save data in database
    $productData = json_decode(file_get_contents('php://input'), true);
   

    if (!$productData) {
        throw new Exception('Invalid or missing JSON data.');
    }
    if (empty($productData['product_name']) || empty($productData['product_description']) || empty($productData['product_image']) || empty($productData['product_price']) || empty($productData['product_shippingCost'])|| !is_numeric($productData['product_price']) || !is_numeric($productData['product_shippingCost'])) {
        throw new Exception("invalid input");
    }

    // setting all data in local variable 
   
    $productName=$productData['product_name'];
    $productDescription = $productData['product_description'];
    $productImage = $productData['product_image'];
    $productPrice = $productData['product_price'];
    $productShippingCost = $productData['product_shippingCost'];

    // query to create new product using client data
    $postQuery = $pdo->prepare('INSERT INTO google_products (product_name, product_description,product_image, product_price, product_shippingCost) VALUES (?, ?, ?, ?,?)');
    $postQuery->execute([$productName,$productDescription,$productImage,$productPrice,$productShippingCost]);

    echo json_encode(['message' => 'Product created successfully in database']);
}
catch(Exception $e){
    // Respond with error message in case of failure
    http_response_code(400); // Bad Request
    echo json_encode(['error' => $e->getMessage()]);
}
}

// update  products using put method
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    try{
    // getting input from client and decodeing json data 
    $productData = json_decode(file_get_contents("php://input"), true);
    if (!$productData) {
        throw new Exception('Invalid or missing JSON data.');
    }
    if (empty($productData['ProductID'])|| !is_numeric($productData['ProductID']) ||empty($productData['product_name']) || empty($productData['product_description']) || empty($productData['product_image']) || empty($productData['product_price']) || empty($productData['product_shippingCost'])|| !is_numeric($productData['product_price']) || !is_numeric($productData['product_shippingCost'])) {
        throw new Exception("invalid input");
    }
    $productID = $productData['ProductID'];
    $productName=$productData['product_name'];
    $productDescription = $productData['product_description'];
    $productImage = $productData['product_image'];
    $productPrice = $productData['product_price'];
    $productShippingCost = $productData['product_shippingCost'];

    $searchQuery = $pdo->prepare('SELECT * FROM google_products WHERE ProductID = ?');
    $searchQuery->execute([$productID]);
    $result = $searchQuery->fetch(PDO::FETCH_ASSOC);
    if (!$result) {
            throw new Exception("this productid is not avaiable");
    }
    // query to update existing product in database
    $updateQuery = $pdo->prepare('UPDATE google_products SET product_name=?,product_description=?, product_image=?, product_price=?, product_shippingCost=? WHERE ProductID=?');
    $updateQuery->execute([$productName, $productDescription, $productImage,$productPrice, $productShippingCost, $productID]);

    echo json_encode(['message' => 'Product updated successfully in database']);
}
catch(Exception $e){
    // Respond with error message in case of failure
    http_response_code(400); // Bad Request
    echo json_encode(['error' => $e->getMessage()]);
}
}

// Delete a product from database
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    try{
    //getting input form client and decoding json to delete product from database
    $productData = json_decode(file_get_contents("php://input"), true);
    if (!$productData) {
        throw new Exception('Invalid or missing JSON data.');
    }
    if(empty($productData['ProductID'])|| !is_numeric($productData['ProductID'])) {
        throw new Exception('enter valid product id');
    }
    
    //setting product id to local variable
    $productID = $productData['ProductID'];
    $searchQuery = $pdo->prepare('SELECT * FROM google_products WHERE ProductID = ?');
    $searchQuery->execute([$productID]);
    $result = $searchQuery->fetch(PDO::FETCH_ASSOC);
    if (!$result) {
            throw new Exception("this productid is not avaiable");
    }
    
    // query to delete product from database using product id 
    $deleteQuery = $pdo->prepare('DELETE FROM google_products WHERE ProductID =?');
    $deleteQuery->execute([$productID]);

    echo json_encode(['message' => 'Product deleted successfully from database']);
}
catch(Exception $e){
    // Respond with error message in case of failure
    http_response_code(400); // Bad Request
    echo json_encode(['error' => $e->getMessage()]);
}

}
?>