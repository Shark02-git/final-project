<?php
header("Access-Control-Allow-Origin: http://localhost:8000");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Include database connection file
require_once('db.php');

// Handle POST request to create a new lock
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data['EmployeeID'])) {
        $employeeID = (int)$data['EmployeeID'];

        // Insert a new lock record with the default status of 'Default'
        $sql = "INSERT INTO `lock` (EmpID, Status) VALUES (?, 'Default')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $employeeID);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Lock created successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error creating lock']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Missing EmployeeID']);
    }
}

// Handle GET request to fetch all lock records or a specific lock by LockID
elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['LockID'])) {
        // Fetch specific lock record by LockID
        $lockID = (int)$_GET['LockID'];
        $sql = "SELECT `Lock`.`LockID`, `Lock`.`EmpID`, `Employee`.`FirstName`, `Employee`.`LastName`, 
                       IFNULL(`Lock`.`Status`, 'Default') AS Status
                FROM `Lock`
                RIGHT JOIN `Employee` ON `Lock`.`EmpID` = `Employee`.`EmpID`
                WHERE `Lock`.`LockID` = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $lockID);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $lock = $result->fetch_assoc();
            echo json_encode($lock);
        } else {
            echo json_encode(['message' => 'Lock record not found']);
        }
        $stmt->close();
    } else {
        // Fetch all lock records with employee names, showing 'Default' if no lock record exists
        $sql = "SELECT `Employee`.`EmpID`, `Employee`.`FirstName`, `Employee`.`LastName`, 
                       IFNULL(`Lock`.`Status`, 'Default') AS Status
                FROM `Employee`
                LEFT JOIN `Lock` ON `Lock`.`EmpID` = `Employee`.`EmpID`";

        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $locks = [];
            while ($row = $result->fetch_assoc()) {
                $locks[] = $row;
            }
            echo json_encode($locks);
        } else {
            echo json_encode(['message' => 'No lock records found']);
        }
    }
}

// Handle PUT request to update lock record (to unlock an employee)
elseif ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data['LockID']) && isset($data['UnlockDate'])) {
        $lockID = (int)$data['LockID'];
        $unlockDate = $data['UnlockDate']; // Date format: 'YYYY-MM-DD HH:MM:SS'

        // Update lock record with unlock date and change status to 'Unlocked'
        $sql = "UPDATE `lock` SET UnlockDate = ?, Status = 'Unlocked' WHERE LockID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $unlockDate, $lockID);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Lock record updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating lock record']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Missing LockID or UnlockDate']);
    }
}

// Handle DELETE request to delete a lock record
elseif ($_SERVER['REQUEST_METHOD'] == 'DELETE' && isset($_GET['LockID'])) {
    $lockID = (int)$_GET['LockID'];

    // Delete lock record by LockID
    $sql = "DELETE FROM `lock` WHERE LockID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $lockID);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Lock record deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error deleting lock record']);
    }
    $stmt->close();
}

// Close database connection
$conn->close();
?>
