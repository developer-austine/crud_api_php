<?php 

require_once '../includes/dbh.inc.php'; // Ensure this includes $pdo, headers, and $requestMethod

function error422($message) {
    $data = [
        'status' => 422,
        'message' => $message,
    ];
    header("HTTP/1.0 422 Unprocessable Entity");
    echo json_encode($data); 
    exit();
}

function storeCustomer($customerInput) {
    global $pdo;

    // Validate and sanitize input given by the user
    $name = trim($customerInput['name']);
    $email = trim($customerInput['email']);
    $phone = trim($customerInput['phone']);

    //return errors if any of the field is considered to be empty
    if (empty($name)) {
        return error422('Enter your name');
    } elseif (empty($email)) {
        return error422('Enter your email');
    } elseif (empty($phone)) {
        return error422('Enter your phone');
    } else { //if not empty, we insert into our database;  
        try {
            // Prepare the SQL query
            $query = "INSERT INTO customers (name, email, phone) VALUES (:name, :email, :phone)";
            $stmt = $pdo->prepare($query);

            // Bind the parameters to prevent SQL injection
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':phone', $phone, PDO::PARAM_STR);

            // Execute the query
            $stmt->execute();

            // Check if the insert was successful
            if ($stmt->rowCount()) {
                $data = [
                    'status' => 201,
                    'message' => 'Customer created successfully',
                ];
                header("HTTP/1.0 201 Created");
                return json_encode($data);
            } else {
                throw new Exception('Insert failed');
            }
        } catch (Exception $e) {
            // Handle exceptions and errors
            $data = [
                'status' => 500,
                'message' => 'Internal server error: ' . $e->getMessage(),
            ];
            header("HTTP/1.0 500 Internal Server Error");
            return json_encode($data);
        }
    }
}


function getCustomerList() {
    global $pdo;  // Make sure $pdo is properly imported and initialized

    $query = "SELECT * FROM customers";
    $stmt = $pdo->prepare($query);
    $stmt->execute();

    if ($stmt) {
        if ($stmt->rowCount() > 0) { //checking if there is data/record in the table database
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);  //if data is found, we run this line of code

            $data = [ 
                'status' => 200,
                'message' => 'Customer List Fetched Successfully',
                'data' => $res
            ];
            header("HTTP/1.0 200 OK");
        return json_encode($data);

        } else {  //if no data is found, we run this line of code
            $data = [
                'status' => 404,
                'message' => 'No Record Found!',
            ];
            header("HTTP/1.0 404 NO Customer Found");
        return json_encode($data);
        }
    } else {
        $data = [
            'status' => 500,
            'message' => 'Internal Server Error',
        ];
        header("HTTP 1.0 500 Internal Server Error");
    return json_encode($data);
    }
}

function getCustomer($customerParam) {
    global $pdo;

    // Validate if 'id' exists in the input
    if (empty($customerParam['id'])) {
        return error422('Customer ID is required');
    }

    // Sanitize and trim the input
    $customerId = trim($customerParam['id']);

    try {
        // Use a prepared statement with a parameter placeholder
        $query = "SELECT * FROM customers WHERE id = :id LIMIT 1";
        $stmt = $pdo->prepare($query);

        // Bind the parameter to prevent SQL injection
        $stmt->bindParam(':id', $customerId, PDO::PARAM_INT);

        // Execute the statement
        $stmt->execute();

        // Check if a row was found
        if ($stmt->rowCount() === 1) {
            $res = $stmt->fetch(PDO::FETCH_ASSOC); // Use fetch() for a single row

            $data = [
                'status' => 200,
                'message' => 'Customer Fetched Successfully',
                'data' => $res,
            ];
            header("HTTP/1.0 200 OK");
            return json_encode($data);
        } else {
            $data = [
                'status' => 404,
                'message' => 'No Customer Found',
            ];
            header("HTTP/1.0 404 Not Found");
            return json_encode($data);
        }
    } catch (Exception $e) {
        // Handle exceptions if something goes wrong
        $data = [
            'status' => 500,
            'message' => 'Internal Server Error: ' . $e->getMessage(),
        ];
        header("HTTP/1.0 500 Internal Server Error");
        return json_encode($data);
    }
}

function updateCustomer($customerInput, $customerParams) {
    global $pdo;

    // Validate if 'id' exists in the URL parameters
    if (!isset($customerParams['id'])) {
        return error422('Customer ID not found in URL');
    } elseif($customerParams['id'] == null) {
        return error422("Enter your Customer ID");
    }

    // Validate and sanitize input
    $customerId = trim($customerParams['id']);
    $name = trim($customerInput['name']);
    $email = trim($customerInput['email']);
    $phone = trim($customerInput['phone']);

    // Return errors if any field is empty
    if (empty($name)) {
        return error422('Enter your name');
    } elseif (empty($email)) {
        return error422('Enter your email');
    } elseif (empty($phone)) {
        return error422('Enter your phone');
    }

    try {
        // Prepare the SQL query with placeholders
        $query = "UPDATE customers 
                  SET name = :name, email = :email, phone = :phone 
                  WHERE id = $customerId 
                  LIMIT 1";
        $stmt = $pdo->prepare($query);

        // Bind the parameters to prevent SQL injection
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':phone', $phone, PDO::PARAM_STR);
        // $stmt->bindParam(':id', $customerId, PDO::PARAM_INT);

        // Execute the query
        $stmt->execute();

        // Check if the update was successful
        if ($stmt->rowCount()) {
            $data = [
                'status' => 200,
                'message' => 'Customer updated successfully',
            ];
            header("HTTP/1.0 200 OK");
            return json_encode($data);
        } else {
            $data = [
                'status' => 404,
                'message' => 'Customer not found',
            ];
            header("HTTP/1.0 404 Not Found");
            return json_encode($data);
        }
    } catch (Exception $e) {
        // Handle exceptions
        $data = [
            'status' => 500,
            'message' => 'Internal server error: ' . $e->getMessage(),
        ];
        header("HTTP/1.0 500 Internal Server Error");
        return json_encode($data);
    }
}

function deleteCustomer($customerParams) {

    global $pdo;

    // Validate if 'id' exists in the URL parameters
    if (!isset($customerParams['id'])) {
        return error422('Customer ID not found in URL');
    } elseif($customerParams['id'] == null) {
        return error422("Enter your Customer ID");
    }

    // Sanitize and trim the input
    $customerId = trim($customerParams['id']);

    $query = "DELETE FROM customers WHERE id = :id LIMIT 1;";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $customerId, PDO::PARAM_INT);
    $stmt->execute();
    
    
    // Check if the delete was successful
    if ($stmt) {

        $data = [
           'status' => 200,
           'message' => 'Customer Deleted successfully',
        ];
        header("HTTP/1.0 200 OK");
        return json_encode($data);
    } else {
        $data = [
           'status' => 404,
           'message' => 'Customer not found',
        ];
        header("HTTP/1.0 404 Not Found");
        return json_encode($data);
    }
}    