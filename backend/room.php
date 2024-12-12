<?php
header("Access-Control-Allow-Origin: http://localhost:8000"); // Update to match your front-end URL
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include('db.php');

// Handle GET request to fetch all rooms
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = "SELECT r.RoomID, r.RoomName, r.Capacity, r.RoomType, r.Status, f.FloorNumber, b.BuildingName
              FROM room r
              JOIN floor f ON r.FloorID = f.FloorID
              JOIN building b ON f.BuildingID = b.BuildingID";  // Join with building and floor for full data
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $rooms = [];
        while ($row = $result->fetch_assoc()) {
            $rooms[] = $row;  // Add room data to the array
        }
        echo json_encode(['success' => true, 'data' => $rooms]);  // Return the rooms
    } else {
        echo json_encode(['success' => false, 'message' => 'No rooms found']);
    }
}

// Handle POST request to add a room
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data['name']) && isset($data['floorId']) && isset($data['capacity'])) {
        $roomName = $data['name'];
        $floorId = $data['floorId'];
        $capacity = is_numeric($data['capacity']) ? $data['capacity'] : null;
        $roomType = isset($data['roomType']) ? $data['roomType'] : 'Standard';
        $status = isset($data['status']) ? $data['status'] : 'Available';

        // Check if the room already exists (same name on the same floor)
        $checkQuery = "SELECT COUNT(*) FROM room r 
                       JOIN floor f ON r.FloorID = f.FloorID 
                       WHERE r.RoomName = '$roomName' AND r.FloorID = '$floorId'";

        $checkResult = $conn->query($checkQuery);
        $count = $checkResult->fetch_row()[0];

        if ($count > 0) {
            echo json_encode(['success' => false, 'message' => 'Room already exists on this floor']);
        } else {
            // Insert new room into the database
            $query = "INSERT INTO room (RoomName, FloorID, Capacity, RoomType, Status) 
                      VALUES ('$roomName', '$floorId', '$capacity', '$roomType', '$status')";

            if ($conn->query($query) === TRUE) {
                $last_id = $conn->insert_id;

                // Fetch the newly added room with its floor and building details
                $roomQuery = "
                    SELECT r.RoomID, r.RoomName, r.Capacity, r.RoomType, r.Status, f.FloorNumber, b.BuildingName
                    FROM room r
                    JOIN floor f ON r.FloorID = f.FloorID
                    JOIN building b ON f.BuildingID = b.BuildingID
                    WHERE r.RoomID = $last_id
                ";
                $roomResult = $conn->query($roomQuery);

                if ($roomResult->num_rows > 0) {
                    $roomDetails = $roomResult->fetch_assoc();
                    echo json_encode([
                        'success' => true,
                        'message' => 'Room added successfully',
                        'data' => $roomDetails
                    ]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error fetching room details']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
            }
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid input']);
    }
}
?>
