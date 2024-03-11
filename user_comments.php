<?php
require_once 'database/databaseconnection.php';

// Get all comments from database using query 
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $allCommmentsQuery = $pdo->query('SELECT * FROM user_comments');
    $allComments = $allCommmentsQuery->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($allComments);
}

// Create a new comment in database using query 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try{
    $commentData = json_decode(file_get_contents('php://input'), true);
    if(!$commentData){
        throw new Exception('Invalid or missing JSON data.');
    }
    if(!is_numeric($commentData['UserID'])|| !is_numeric($commentData['ProductID'])|| !is_numeric($commentData['user_rating'])|| empty($commentData['comment_images'])|| empty($commentData['comment_text'])){
        throw new Exception('invalid input');
    }

    $commentUserID = $commentData['UserID'];
    $commentProductID = $commentData['ProductID'];
    $userRating = $commentData['user_rating'];
    $commentImages = $commentData['comment_images'];
    $CommentText = $commentData['comment_text'];
    if($userRating<1||$userRating>5){
        throw new Exception("enter rating between 1 to 5");
    }

    $postQuery = $pdo->prepare('INSERT INTO user_comments (UserID,ProductID,user_rating,comment_images,comment_text) VALUES (?, ?, ?, ?, ?)');
    $postQuery->execute([$commentUserID,$commentProductID,$userRating,$commentImages,$CommentText]);

    echo json_encode(['message' => 'Comment created successfully in database']);
}
catch(Exception $e){
    http_response_code(400); // Bad Request
    echo json_encode(['error' => $e->getMessage()]);
}
}

// Update a existing comment using comment id in database
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    try{
    //getting input from client and decoding json data 
    $commentData = json_decode(file_get_contents("php://input"), true);

    if(!is_numeric($commentData['CommentID'])||!is_numeric($commentData['UserID'])|| !is_numeric($commentData['ProductID'])|| !is_numeric($commentData['user_rating'])|| empty($commentData['comment_images'])|| empty($commentData['comment_text'])){
        throw new Exception('invalid input');
    }
    //setting all new data in local variable 
    $commentID = $commentData['CommentID'];
    $commentUserID = $commentData['UserID'];
    $commentProductID = $commentData['ProductID'];
    $userRating = $commentData['user_rating'];
    $commentImages = $commentData['comment_images'];
    $CommentText = $commentData['comment_text'];

    $searchQuery = $pdo->prepare('SELECT * FROM user_comments WHERE CommentID = ?');
    $searchQuery->execute([$commentID]);
    $result = $searchQuery->fetch(PDO::FETCH_ASSOC);
    if (!$result) {
            throw new Exception("this comment id is not avaiable");
    }
    // query to update existing comment in database using comment ID
    $updateQuery = $pdo->prepare('UPDATE user_comments SET UserID=?, ProductID=?, user_rating=?, comment_images=?, comment_text=? WHERE CommentID=?');
    $updateQuery->execute([$commentUserID,$commentProductID,$userRating,$commentImages,$CommentText,$commentID]);

    echo json_encode(['message' => 'Comment updated successfully in database']);
} catch(Exception $e){
    http_response_code(400); // Bad Request
    echo json_encode(['error' => $e->getMessage()]);
}
}
//delete comment using commnet id from database 
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    try{
    // getting input from client and decoding json data 
    $commentData = json_decode(file_get_contents("php://input"), true);
    if(!$commentData){
        throw new Exception('Invalid or missing JSON data.');
    }

    $commentID = $commentData['CommentID'];
    if(!is_numeric($commentID)){
        throw new Exception('Enter valid comment id');
    }



    $searchQuery = $pdo->prepare('SELECT * FROM user_comments WHERE CommentID = ?');
    $searchQuery->execute([$commentID]);
    $result = $searchQuery->fetch(PDO::FETCH_ASSOC);
    if (!$result) {
            throw new Exception("this comment id is not avaiable");
    }
// query to delete comment form database using commentID
    $deleteQuery = $pdo->prepare('DELETE FROM user_comments WHERE CommentID=?');
    $deleteQuery->execute([$commentID]);

    echo json_encode(['message' => 'Comment deleted successfully from database']);
}
catch(Exception $e){
    http_response_code(400); // Bad Request
    echo json_encode(['error' => $e->getMessage()]);
}
}
?> 