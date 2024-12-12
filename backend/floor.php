<?php
header("Access-Control-Allow-Origin: http://localhost:8000"); // Replace with your front-end URL
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Include database connection
include('db.php');

// Handle GET request to fetch floors
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = "SELECT f.FloorID, f.FloorNumber, b.BuildingName
              FROM floor f
              JOIN building b ON f.BuildingID = b.BuildingID";  // Join floors with buildings
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $floors = [];
        while ($row = $result->fetch_assoc()) {
            $floors[] = $row;  // Add floor data to the array
        }
        echo json_encode(['success' => true, 'data' => $floors]);  // Return the floors
    } else {
        echo json_encode(['success' => false, 'message' => 'No floors found']);
    }
}

// Handle POST request to add a floor
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the POST data
    $data = json_decode(file_get_contents("php://input"), true);
    if (isset($data['FloorNumber']) && isset($data['BuildingID'])) {
        $floorNumber = $data['FloorNumber'];
        $buildingID = $data['BuildingID'];

        // Insert the new floor into the database
        $query = "INSERT INTO floor (FloorNumber, BuildingID) VALUES (?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $floorNumber, $buildingID);  // Ensure types match

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Floor added successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add floor']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid data']);
    }
}

$conn->close();
?>