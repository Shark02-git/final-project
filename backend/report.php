<?php
// Include database connection file
require_once('db.php');

// Handle POST request to create a new report record
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data['ReportType'], $data['StartDate'], $data['EndDate'], $data['EmpID'])) {
        $reportType = $data['ReportType'];
        $startDate = $data['StartDate'];
        $endDate = $data['EndDate'];
        $empID = (int)$data['EmpID'];

        // Insert a new report record
        $sql = "INSERT INTO Report (ReportType, StartDate, EndDate, EmpID) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $reportType, $startDate, $endDate, $empID);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Report created successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error creating report']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    }
}

// Handle GET request to fetch all reports or a specific report by ReportID
elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['ReportID'])) {
        // Fetch specific report by ReportID
        $reportID = (int)$_GET['ReportID'];
        $sql = "SELECT * FROM Report WHERE ReportID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $reportID);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $report = $result->fetch_assoc();
            echo json_encode($report);
        } else {
            echo json_encode(['message' => 'Report not found']);
        }
        $stmt->close();
    } else {
        // Fetch all reports
        $sql = "SELECT * FROM Report";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $reports = [];
            while ($row = $result->fetch_assoc()) {
                $reports[] = $row;
            }
            echo json_encode($reports);
        } else {
            echo json_encode(['message' => 'No reports found']);
        }
    }
}

// Handle PUT request to update a report record
elseif ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data['ReportID'], $data['ReportType'], $data['StartDate'], $data['EndDate'], $data['EmpID'])) {
        $reportID = (int)$data['ReportID'];
        $reportType = $data['ReportType'];
        $startDate = $data['StartDate'];
        $endDate = $data['EndDate'];
        $empID = (int)$data['EmpID'];

        // Update report record
        $sql = "UPDATE Report SET ReportType = ?, StartDate = ?, EndDate = ?, EmpID = ? WHERE ReportID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssii", $reportType, $startDate, $endDate, $empID, $reportID);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Report updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating report']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    }
}

// Handle DELETE request to delete a report record
elseif ($_SERVER['REQUEST_METHOD'] == 'DELETE' && isset($_GET['ReportID'])) {
    $reportID = (int)$_GET['ReportID'];

    // Delete report record
    $sql = "DELETE FROM Report WHERE ReportID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $reportID);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Report deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error deleting report']);
    }
    $stmt->close();
}

// Close database connection
$conn->close();
?>
