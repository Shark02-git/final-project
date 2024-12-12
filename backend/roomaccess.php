<?php
// Include database connection file
require_once('db.php');

// Handle POST request to create a new room access record
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data['BookingID']) && isset($data['AccessTime'])) {
        $bookingID = (int)$data['BookingID'];
        $accessTime = $data['AccessTime'];

        // Insert a new room access record
        $sql = "INSERT INTO RoomAccess (BookingID, AccessTime) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $bookingID, $accessTime);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Room access record created successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error creating room access record']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Missing BookingID or AccessTime']);
    }
}

// Handle GET request to fetch all room access records or a specific record by RoomAccessID
elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['RoomAccessID'])) {
        // Fetch specific room access record by RoomAccessID
        $roomAccessID = (int)$_GET['RoomAccessID'];
        $sql = "SELECT * FROM RoomAccess WHERE RoomAccessID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $roomAccessID);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $roomAccess = $result->fetch_assoc();
            echo json_encode($roomAccess);
        } else {
            echo json_encode(['message' => 'Room access record not found']);
        }
        $stmt->close();
    } else {
        // Fetch all room access records
        $sql = "SELECT * FROM RoomAccess";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $roomAccessRecords = [];
            while ($row = $result->fetch_assoc()) {
                $roomAccessRecords[] = $row;
            }
            echo json_encode($roomAccessRecords);
        } else {
            echo json_encode(['message' => 'No room access records found']);
        }
    }
}

// Handle DELETE request to delete a room access record
elseif ($_SERVER['REQUEST_METHOD'] == 'DELETE' && isset($_GET['RoomAccessID'])) {
    $roomAccessID = (int)$_GET['RoomAccessID'];

    // Delete room access record by RoomAccessID
    $sql = "DELETE FROM RoomAccess WHERE RoomAccessID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $roomAccessID);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Room access record deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error deleting room access record']);
    }
    $stmt->close();
}

// Close database connection
$conn->close();
?>
