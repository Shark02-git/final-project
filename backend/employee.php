<?php

header("Access-Control-Allow-Origin: http://localhost:8000");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
require_once('db.php');

// Fetch employee data (GET)
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $sql = "
    SELECT 
        e.EmpID, 
        e.FirstName, 
        e.LastName, 
        d.DepName AS DepartmentName, 
        p.PositionName AS PositionName, 
        al.LevelName AS AccessLevel, 
        e.PhoneNo, 
        e.Username,
        COALESCE(l.Status, 'Default') AS Status  -- Adding Status from lock table
    FROM employee e
    JOIN department d ON e.DepartmentID = d.DepID
    JOIN position p ON e.PositionID = p.PositionID
    JOIN accesslevel al ON p.AccessLevelID = al.AccessLevelID
    LEFT JOIN `lock` l ON e.EmpID = l.EmployeeID  -- Join lock table for status, escape `lock` to avoid reserved keyword issue
    ";
    $result = $conn->query($sql);
    $employees = [];
    
    while ($row = $result->fetch_assoc()) {
        $employees[] = $row;
    }
    
    echo json_encode($employees);
}

// Add new employee (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    $firstName = $data['firstName'];
    $lastName = $data['lastName'];
    $departmentID = $data['departmentID'];
    $positionID = $data['positionID'];
    $phoneNo = $data['phoneNo'];
    $username = $data['username'];
    $password = password_hash($data['password'], PASSWORD_DEFAULT);

    $sql = "
        INSERT INTO employee (FirstName, LastName, DepartmentID, PositionID, PhoneNo, Username, Password) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssiiiss", $firstName, $lastName, $departmentID, $positionID, $phoneNo, $username, $password);
        if ($stmt->execute()) {
            echo json_encode(["message" => "Employee added successfully."]);
        } else {
            echo json_encode(["error" => "Error adding employee: " . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(["error" => "Error preparing SQL query."]);
    }

    $conn->close();
}

// Update employee (PUT)
if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    $data = json_decode(file_get_contents("php://input"), true);

    $empID = $data['empID'];
    $firstName = $data['firstName'];
    $lastName = $data['lastName'];
    $departmentID = $data['departmentID'];
    $positionID = $data['positionID'];
    $phoneNo = $data['phoneNo'];
    $username = $data['username'];

    // Check if the employee exists
    $checkSql = "SELECT EmpID FROM Employee WHERE EmpID = ?";
    if ($stmt = $conn->prepare($checkSql)) {
        $stmt->bind_param("i", $empID);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows === 0) {
            echo json_encode(["error" => "Employee with ID $empID does not exist."]);
            $stmt->close();
            exit;
        }
        $stmt->close();
    }

    // Update the employee data
    $sql = "
        UPDATE employee 
        SET FirstName = ?, LastName = ?, DepartmentID = ?, PositionID = ?, PhoneNo = ?, Username = ? 
        WHERE EmpID = ?
    ";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssiiisi", $firstName, $lastName, $departmentID, $positionID, $phoneNo, $username, $empID);
        if ($stmt->execute()) {
            echo json_encode(["message" => "Employee updated successfully."]);
        } else {
            echo json_encode(["error" => "Error updating employee: " . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(["error" => "Error preparing SQL query."]);
    }

    $conn->close();
}

// Delete employee (DELETE)
if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    // Get the employee ID from the request body
    $data = json_decode(file_get_contents("php://input"), true);
    $empID = $data['empID'];

    // Prepare the DELETE SQL query
    $sql = "DELETE FROM employee WHERE EmpID = ?";

    if ($stmt = $conn->prepare($sql)) {
        // Bind parameter
        $stmt->bind_param("i", $empID);

        // Execute the query
        if ($stmt->execute()) {
            echo json_encode(["message" => "Employee deleted successfully."]);
        } else {
            echo json_encode(["error" => "Error deleting employee: " . $stmt->error]);
        }

        $stmt->close();
    } else {
        echo json_encode(["error" => "Error preparing SQL query."]);
    }

    // Close the database connection
    $conn->close();
}
?>
