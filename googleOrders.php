<?php
require_once 'database/databaseconnection.php';

// geting  all orders using sql query from database 
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    //  query to get all orders 
    $allOrdersQuery = $pdo->prepare('SELECT orders.OrderID AS OrderID,products.product_name AS productName,products.product_price AS Price , users.username AS userName, users.user_ShippingAddress AS shippingAdress, orders.order_qty AS quantity, orders.order_total AS Total
                                     FROM google_orders AS orders
                                     JOIN google_users AS users  ON orders.UserID = users.UserID
                                     JOIN google_products products ON orders.ProductID = products.ProductID');
    $allOrdersQuery->execute();
    // fetching all orders and saving in googleOrder variable
    $googleOrders = $allOrdersQuery->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($googleOrders);
}


// creating new order in database using query 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try{
    // geting input from client and decoding json data to save data in database
    $orderData = json_decode(file_get_contents('php://input'), true);
    if(!$orderData){
        throw new Exception('Invalid or missing JSON data.');
    }
    if (!is_numeric($orderData['UserID'])||!is_numeric($orderData['ProductID'])||!is_numeric($orderData['order_qty'])||!is_numeric($orderData['order_total'])) {
        throw new Exception("invalid input");
    }
    // setting all data in local variable 
    $orderUserID = $orderData['UserID'];
    $orderProductID = $orderData['ProductID'];
    $OrderQTY = $orderData['order_qty'];
    $orderTotal = $orderData['order_total'];
    if ($OrderQTY<1 || $orderTotal<1 ){
        throw new Exception("enter value greater than zero in Qty or Total");
    }
    // query to create new order using given client data
    $postQuery = $pdo->prepare('INSERT INTO google_orders (userID, ProductID, order_qty, order_total) VALUES (?, ?, ?, ?)');
    $postQuery->execute([$orderUserID, $orderProductID, $OrderQTY, $orderTotal]);

    echo json_encode(['message' => 'Order created successfully']);
}
catch(Exception $e){
    http_response_code(400); // Bad Request
    echo json_encode(['error' => $e->getMessage()]);
}
}

// Update an orders using put method 
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    try {
        // getting input from client and decoding json data 
        $orderData = json_decode(file_get_contents("php://input"), true);
        if (!$orderData) {
            throw new Exception('Invalid or missing JSON data.');
        }
        if (!is_numeric($orderData['OrderID'])||!is_numeric($orderData['UserID'])||!is_numeric($orderData['ProductID'])||!is_numeric($orderData['order_qty'])||!is_numeric($orderData['order_total'])) {
            throw new Exception("invalid input");
        }

        //setting all new data in local variable
        $orderID = $orderData['OrderID']; 
        $orderUserID = $orderData['UserID'];
        $orderProductID = $orderData['ProductID'];
        $OrderQTY = $orderData['order_qty'];
        $orderTotal = $orderData['order_total'];
        
        $searchQuery = $pdo->prepare('SELECT * FROM google_orders WHERE OrderID = ?');
        $searchQuery->execute([$orderID]);
        $result = $searchQuery->fetch(PDO::FETCH_ASSOC);
        if (!$result) {
            throw new Exception("this order id is not avaiable");
        }
        if ($OrderQTY < 1 || $orderTotal < 1) {
            throw new Exception("enter value greater than zero in Qty or Total");
        }
        //query to update existing order in database using order id 
        $updateQuery = $pdo->prepare('UPDATE google_orders SET UserID=?, ProductID=?, order_qty=?, order_total=? WHERE OrderID=?');
        $updateQuery->execute([$orderUserID,$orderProductID,$OrderQTY ,$orderTotal , $orderID]);

        echo json_encode(['message' => 'Order updated successfully']);
    } catch (Exception $e) {
        http_response_code(400); // Bad Request
        echo json_encode(['error' => $e->getMessage()]);
    }
}

// Delete an order from database using query 
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    try {
        
        // getting input from client and decoding json data given by client 
        $orderData = json_decode(file_get_contents("php://input"), true);
        if (!$orderData) {
            throw new Exception('Invalid or missing JSON data.');
        }
        if(!is_numeric($orderData['OrderID'])){
            throw new Exception("enter valid orderID");
        }
        //setting orderid in local variable
        $orderID = $orderData['OrderID'];
        
        $searchQuery = $pdo->prepare('SELECT * FROM google_orders WHERE OrderID = ?');
        $searchQuery->execute([$orderID]);
        $result = $searchQuery->fetch(PDO::FETCH_ASSOC);
        if (!$result) {
            throw new Exception("this order id is not avaiable");
        }
        // query to delete data from database using order id 
        $deleteQuery = $pdo->prepare('DELETE FROM google_orders  WHERE OrderID=?');
        $deleteQuery->execute([$orderID]);

        echo json_encode(['message' => 'Order deleted successfully']);
    } catch (Exception $e) {
        http_response_code(400); // Bad Request
        echo json_encode(['error' => $e->getMessage()]);
    }
}
