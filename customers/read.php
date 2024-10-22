<?php

header('Access-Control-Allow-Origin');
header('Content-Type: application/json');
header('Access-Control-Allow-Method: GET');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Request-With');

include ('function.php');

$requestMethod = $_SERVER["REQUEST_METHOD"];

if($requestMethod == "GET") {

    if(isset($_GET['id'])) { //getting a specific record from our database

        $customer = getcustomer($_GET);
        echo $customer;

    } else {
        $customerList = getCustomerList();
        echo $customerList;
    }

} else {
    $data = [
        'status' => 405,
        'message' => $requestMethod, 'Method Not Allolwed',
    ];
    header("HTTP/1.0 405 Method Not Allowed");
    echo json_encode($data); 
}
