<?php
require_once 'database/databaseconnection.php';
// getting cart data using query 
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // query to show cart data
    $allCartQuery = $pdo->prepare('SELECT carts.CartID AS CartID, users.username AS userName, products.product_name AS productName, products.product_price AS productPrice, products.product_shippingCost AS shippingCosts, carts.product_Qty AS Quantity, carts.cart_total AS total
                                FROM google_carts carts
                                JOIN google_users users ON carts.UserID = users.UserID
                                JOIN google_products products ON carts.ProductID = products.ProductID');
    // executing query 
    $allCartQuery->execute();
    // feching all query and saving that data in allCart variable 
    $allCarts = $allCartQuery->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(array_values($allCarts));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // getting cart data from client 
        $cartData = json_decode(file_get_contents('php://input'), true);
        // validating cartData 
        if (!$cartData) {
            throw new Exception('Invalid or missing JSON data.');
        }
        // validating data of client 
        if (!is_numeric($cartData['UserID']) || !is_numeric($cartData['UserID']) || !is_numeric($cartData['UserID']) || !is_numeric($cartData['cart_total'])) {
            throw new Exception("invaild input");
        }
        
        $cartUserID = $cartData['UserID'];
        $cartProdutID = $cartData['ProductID'];
        $productQty = $cartData['product_Qty'];
        $cartTotal = $cartData['cart_total'];
        // checking for valid qty and total
        if ($productQty < 1 || $cartTotal < 1) {
            throw new Exception("enter value greater than zero in Qty or Total");
        }


        // query to insert data in database which is given by client
        $postQuery = $pdo->prepare('INSERT INTO google_carts (UserID,ProductID,product_Qty,cart_total) VALUES (?, ?, ?,?)');
        $postQuery->execute([$cartUserID, $cartProdutID, $productQty, $cartTotal]);

        echo json_encode(['message' => 'Cart created successfully']);
    } catch (Exception $e) {
        http_response_code(400); // Bad Request
        echo json_encode(['error' => $e->getMessage()]);
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'PUT'){
    try {
        // getting cart data from client to update existing cart
        $cartData = json_decode(file_get_contents("php://input"), true);
        // validating cartData
        if (!$cartData) {
            throw new Exception('Invalid or missing JSON data.');
        }
        //validating all data given by client 
        if (!is_numeric($cartData['CartID'])||!is_numeric($cartData['UserID']) || !is_numeric($cartData['ProductID']) || !is_numeric($cartData['product_Qty']) || !is_numeric($cartData['cart_total'])) {
            throw new Exception("invaild input");
        }

        $cartID = $cartData['CartID'];
        $cartUserID = $cartData['UserID'];
        $cartProdutID = $cartData['ProductID'];
        $productQty = $cartData['product_Qty'];
        $cartTotal = $cartData['cart_total'];
        // query to check given cart is avaiable or not 
        $searchQuery = $pdo->prepare('SELECT * FROM google_carts WHERE CartID = ?');
        $searchQuery->execute([$cartID]);
        $result = $searchQuery->fetch(PDO::FETCH_ASSOC);
        if (!$result) {
            throw new Exception("this cart id is not avaiable");
        }
        // validate for qty and total 
        if ($productQty < 1 || $cartTotal < 1) {
            throw new Exception("enter value greater than zero in Qty or Total");
        }
        // query to update existing data 
        $updateQuery = $pdo->prepare('UPDATE google_carts SET UserID=?, ProductID=?, product_qty=?, cart_total=? WHERE cartID=?');
        $updateQuery->execute([$cartUserID,$cartProdutID,$productQty,$cartTotal,$cartID]);
        echo json_encode(['message' => 'cart data updated  successfully']);

    } catch (Exception $e) {
        http_response_code(400); // Bad Request
        echo json_encode(['error' => $e->getMessage()]);
    }
}
// deteling data using delete method
if($_SERVER['REQUEST_METHOD'] === 'DELETE'){
    try{
        //getting data from client and decoding json data
    $cartData = json_decode(file_get_contents("php://input"), true);
    // validating cartData
    if(!$cartData){
        throw new Exception('Invalid or missing JSON data.');
    }
    //checking for valid cartID
    if( !is_numeric($cartData['CartID'])){
        throw new Exception("enter valid cart ID");
    }
    // query to check cart is avalable or not 
    $cartID = $cartData['CartID'];
    $searchQuery = $pdo->prepare('SELECT * FROM google_carts WHERE CartID = ?');
    $searchQuery->execute([$cartID]);
    $result = $searchQuery->fetch(PDO::FETCH_ASSOC);
    if (!$result) {
            throw new Exception("this cart id is not avaiable");
    }
    //query to delete data 
    $deleteQuery = $pdo->prepare('DELETE FROM google_carts WHERE cartID=?');
    $deleteQuery->execute([$cartID]);
    echo json_encode(['message' => 'Order deleted successfully']);
} catch (Exception $e){
    http_response_code(400); // Bad Request
    echo json_encode(['error' => $e->getMessage()]);
}
}

?>