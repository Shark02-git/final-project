<?php
header("Access-Control-Allow-Origin: http://localhost:8000");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require_once('db.php');  // Include database connection

// Fetch all departments (GET)
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $sql = "SELECT * FROM Department";
    $result = $conn->query($sql);
    $departments = [];

    // Check if query was successful
    if ($result->num_rows > 0) {
        // Fetch results and add them to an array
        while ($row = $result->fetch_assoc()) {
            $departments[] = $row;  // Add each department to the array
        }
        echo json_encode($departments);  // Return departments as JSON
    } else {
        echo json_encode(['message' => 'No departments found']);
    }
}

// Add a new department (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get department data from POST request
    $data = json_decode(file_get_contents("php://input"));
    $depName = $data->DepName;  // Assume the JSON body contains DepName

    // Validate data
    if (!empty($depName)) {
        $sql = "INSERT INTO Department (DepName) VALUES ('$depName')";
        
        if ($conn->query($sql) === TRUE) {
            echo json_encode(['message' => 'Department added successfully']);
        } else {
            echo json_encode(['message' => 'Error: ' . $conn->error]);
        }
    } else {
        echo json_encode(['message' => 'Department name is required']);
    }
}

// Update an existing department (PUT)
if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    // Get department data from PUT request
    $data = json_decode(file_get_contents("php://input"));
    $depID = $data->DepID;  // Department ID to be updated
    $depName = $data->DepName;  // New department name

    // Validate data
    if (!empty($depID) && !empty($depName)) {
        $sql = "UPDATE Department SET DepName = '$depName' WHERE DepID = $depID";
        
        if ($conn->query($sql) === TRUE) {
            echo json_encode(['message' => 'Department updated successfully']);
        } else {
            echo json_encode(['message' => 'Error: ' . $conn->error]);
        }
    } else {
        echo json_encode(['message' => 'Department ID and name are required']);
    }
}

// Delete a department (DELETE)
if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    // Get the department ID from the DELETE request
    $data = json_decode(file_get_contents("php://input"));
    $depID = $data->DepID;  // The department ID to be deleted

    // Validate data
    if (!empty($depID)) {
        $sql = "DELETE FROM Department WHERE DepID = $depID";
        
        if ($conn->query($sql) === TRUE) {
            echo json_encode(['message' => 'Department deleted successfully']);
        } else {
            echo json_encode(['message' => 'Error: ' . $conn->error]);
        }
    } else {
        echo json_encode(['message' => 'Department ID is required']);
    }
}

$conn->close();  // Close connection
?>
