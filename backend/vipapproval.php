<?php
// Include database connection file
require_once('db.php');

// Handle POST request to create a new VIP approval
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data['BookingID']) && isset($data['ManagerID']) && isset($data['ApprovalStatus'])) {
        $bookingID = (int)$data['BookingID'];
        $managerID = (int)$data['ManagerID'];
        $approvalStatus = $data['ApprovalStatus'];

        // Insert VIP approval data into the VIPApproval table
        $sql = "INSERT INTO VIPApproval (BookingID, ManagerID, ApprovalStatus) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iis", $bookingID, $managerID, $approvalStatus);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'VIP approval created successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error creating VIP approval']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    }
}

// Handle GET request to fetch all VIP approvals or a specific one by ApprovalID
elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['ApprovalID'])) {
        // Fetch specific VIP approval by ApprovalID
        $approvalID = (int)$_GET['ApprovalID'];
        $sql = "SELECT * FROM VIPApproval WHERE ApprovalID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $approvalID);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $approval = $result->fetch_assoc();
            echo json_encode($approval);
        } else {
            echo json_encode(['message' => 'VIP approval not found']);
        }
        $stmt->close();
    } else {
        // Fetch all VIP approvals
        $sql = "SELECT * FROM VIPApproval";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $approvals = [];
            while ($row = $result->fetch_assoc()) {
                $approvals[] = $row;
            }
            echo json_encode($approvals);
        } else {
            echo json_encode(['message' => 'No VIP approvals found']);
        }
    }
}

// Handle PUT request to update VIP approval details
elseif ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data['ApprovalID']) && isset($data['BookingID']) && isset($data['ManagerID']) && isset($data['ApprovalStatus'])) {
        $approvalID = (int)$data['ApprovalID'];
        $bookingID = (int)$data['BookingID'];
        $managerID = (int)$data['ManagerID'];
        $approvalStatus = $data['ApprovalStatus'];

        // Update VIP approval data
        $sql = "UPDATE VIPApproval SET BookingID = ?, ManagerID = ?, ApprovalStatus = ? WHERE ApprovalID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iisi", $bookingID, $managerID, $approvalStatus, $approvalID);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'VIP approval updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating VIP approval']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    }
}

// Handle DELETE request to delete a VIP approval
elseif ($_SERVER['REQUEST_METHOD'] == 'DELETE' && isset($_GET['ApprovalID'])) {
    $approvalID = (int)$_GET['ApprovalID'];

    // Delete VIP approval by ApprovalID
    $sql = "DELETE FROM VIPApproval WHERE ApprovalID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $approvalID);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'VIP approval deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error deleting VIP approval']);
    }
    $stmt->close();
}

// Close database connection
$conn->close();
?>
