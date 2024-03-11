<?php
require_once 'database/databaseconnection.php';

// Get all users from database using query 
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // query to get all user from data base 
    $allUserQuery = $pdo->query('SELECT * FROM google_users');
    $allUsers = $allUserQuery->fetchAll(PDO::FETCH_ASSOC);
    // encode data in json formet 
    echo json_encode($allUsers);
}

// create new user using query 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // getting user data from client and decoding json 
    try {
        // Getting user data from client and decoding json 
        $inputData = file_get_contents('php://input');
        $userData = json_decode($inputData, true);

        if (!$userData) {
            throw new Exception('Invalid or missing JSON data.');
        }

        // Checking for required fields

        if (empty($userData['username']) || empty($userData['user_email']) || empty($userData['password']) || empty($userData['user_purchaseHistory']) || empty($userData['user_ShippingAddress'])) {
            throw new Exception("invalid input");
        }



        // Extracting all user data into local variables
        $username = $userData['username'];
        $userEmail = $userData['user_email'];
        $password = $userData['password'];
        $userPurchaseHistory = $userData['user_purchaseHistory'];
        $userShippingAddress = $userData['user_ShippingAddress'];

        // Query to create a new user in the database
        $postQuery = $pdo->prepare('INSERT INTO google_users (username, user_email, password, user_purchaseHistory, user_ShippingAddress) VALUES (?, ?, ?, ?, ?)');

        // Check if the query was successfully executed
        if (!$postQuery->execute([$username, $userEmail, $password, $userPurchaseHistory, $userShippingAddress])) {
            throw new Exception('Failed to create the user.');
        }

        echo json_encode(['message' => 'User created successfully']);
    } catch (Exception $e) {
        // Respond with error message in case of failure
        http_response_code(400); // Bad Request
        echo json_encode(['error' => $e->getMessage()]);
    }
}

// Update a existing user using query 
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    try {
        // getting updated user data from client and decoding json 
        $userData = json_decode(file_get_contents("php://input"), true);
        if (!$userData) {
            throw new Exception('Invalid or missing JSON data.');
        }
        // validate all field
        if (empty($userData['UserID'])|| !is_numeric($userData['UserID']) || empty($userData['username']) || empty($userData['user_email']) || empty($userData['password'])) {
            throw new Exception("invalid input");
        }
        // setting all given updated user data into local variable 
        $userID = $userData['UserID'];
        $username = $userData['username'];
        $userEmail = $userData['user_email'];
        $password = $userData['password'];
        $userPurchaseHistory = $userData['user_purchaseHistory'];
        $userShippingAddress = $userData['user_ShippingAddress'];

        $searchQuery = $pdo->prepare('SELECT * FROM google_users WHERE UserID = ?');
        $searchQuery->execute([$userID]);
        $result = $searchQuery->fetch(PDO::FETCH_ASSOC);
        if (!$result) {
            throw new Exception("this user id is not avaiable");
        }
        //query to update user data using Userid 
        $updateQuery = $pdo->prepare('UPDATE google_users SET username =?,user_email =?, password=?, user_purchaseHistory=?, user_ShippingAddress=? WHERE UserID=?');
        $updateQuery->execute([$username, $userEmail, $password, $userPurchaseHistory, $userShippingAddress, $userID]);

        echo json_encode(['message' => 'User updated successfully']);
    } catch (Exception $e) {
        http_response_code(400); // Bad Request
        echo json_encode(['error' => $e->getMessage()]);
    }
}


// Delete a user using query from database
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    try{
    //getting client input 
    $userData = json_decode(file_get_contents("php://input"), true);
    if (!$userData) {
        throw new Exception('Invalid or missing JSON data.');
    }
    //validate user id
    if(empty($userData['UserID'])|| !is_numeric($userData['UserID'])) {
        throw new Exception('enter valid user id');
    }
    // setting userid in local variable given by client
    $userID = $userData['UserID'];
    $searchQuery = $pdo->prepare('SELECT * FROM google_users WHERE UserID = ?');
        $searchQuery->execute([$userID]);
        $result = $searchQuery->fetch(PDO::FETCH_ASSOC);
        if (!$result) {
            throw new Exception("this user id is not avaiable");
        }
    // query to delele user using userid 
    $deleteQuery = $pdo->prepare('DELETE FROM google_users WHERE UserID = ?');
    $deleteQuery->execute([$userID]);

    echo json_encode(['message' => 'User deleted successfully']);
    }
    catch(Exception $e){
        http_response_code(400); // Bad Request
        echo json_encode(['error' => $e->getMessage()]);

    }
}
