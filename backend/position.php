<?php
header("Access-Control-Allow-Origin: http://localhost:8000");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
// Include database connection
include('db.php');

// Get the request method
$method = $_SERVER['REQUEST_METHOD'];

// Handle GET request
if ($method === 'GET') {
    $query = "SELECT * FROM position";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $positions = [];
        while ($row = $result->fetch_assoc()) {
            $positions[] = $row;
        }
        echo json_encode(['success' => true, 'data' => $positions]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No positions found']);
    }
}

// Handle POST request (add new position)
if ($method === 'POST') {
    // Get the POST data
    $data = json_decode(file_get_contents("php://input"), true);
    if (isset($data['PositionName']) && isset($data['AccessLevelID'])) {
        $positionName = $data['PositionName'];
        $accessLevelID = $data['AccessLevelID'];

        // Insert the new position into the database
        $query = "INSERT INTO position (PositionName, AccessLevelID) VALUES (?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $positionName, $accessLevelID);

        if ($stmt->execute()) {
            $newPositionID = $stmt->insert_id;
            // Fetch the newly inserted position
            $newPositionQuery = "SELECT * FROM positions WHERE PositionID = ?";
            $stmt = $conn->prepare($newPositionQuery);
            $stmt->bind_param("i", $newPositionID);
            $stmt->execute();
            $result = $stmt->get_result();
            $newPosition = $result->fetch_assoc();
            echo json_encode(['success' => true, 'data' => $newPosition]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add position']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid data']);
    }
}

// Handle PUT request (update position)
if ($method === 'PUT') {
    // Get the PUT data
    $data = json_decode(file_get_contents("php://input"), true);
    if (isset($data['PositionID']) && isset($data['PositionName']) && isset($data['AccessLevelID'])) {
        $positionID = $data['PositionID'];
        $positionName = $data['PositionName'];
        $accessLevelID = $data['AccessLevelID'];

        // Update position in the database
        $query = "UPDATE position SET PositionName = ?, AccessLevelID = ? WHERE PositionID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sii", $positionName, $accessLevelID, $positionID);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Position updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update position']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid data']);
    }
}

// Close the database connection
$conn->close();
?>
