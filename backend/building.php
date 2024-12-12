<?php
// Allow cross-origin requests from any origin (use a specific origin for more security)
header("Access-Control-Allow-Origin: http://localhost:8000"); // Change this to the URL of your frontend if necessary
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS"); // Allowed methods
header("Access-Control-Allow-Headers: Content-Type"); // Allowed headers
require_once('db.php'); // Ensure your database connection logic is correct

// Handle preflight requests (OPTIONS method)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Your existing PHP code to handle requests (e.g., get, post, delete data from the database)
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Example of retrieving multiple buildings from the database
    $sql = "SELECT BuildingID, BuildingName FROM building";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $buildings = [];
        while ($row = $result->fetch_assoc()) {
            $buildings[] = $row;
        }
        echo json_encode($buildings); // Return array of buildings
    } else {
        echo json_encode(['message' => 'No buildings found.']);
    }
}
elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle POST request to add new building
    $data = json_decode(file_get_contents("php://input"));
    
    if (isset($data->BuildingName) && !empty($data->BuildingName)) {
        $buildingName = $data->BuildingName;

        // Prepare SQL query to insert a new building
        $stmt = $conn->prepare("INSERT INTO building (BuildingName) VALUES (?)");
        if ($stmt === false) {
            echo json_encode(['message' => 'Error preparing the SQL statement.']);
            exit();
        }

        $stmt->bind_param("s", $buildingName); // 's' for string type

        // Execute the prepared statement
        if ($stmt->execute()) {
            echo json_encode(['message' => 'Building added successfully']);
        } else {
            echo json_encode(['message' => 'Error adding building: ' . $stmt->error]);
        }

        $stmt->close();
    } else {
        echo json_encode(['message' => 'Building name is required']);
    }
}
elseif ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    // Handle DELETE request to delete a building
    if (isset($_GET['BuildingID'])) {
        $buildingID = $_GET['BuildingID'];

        // Prepare and execute the DELETE query
        $stmt = $conn->prepare("DELETE FROM building WHERE BuildingID = ?");
        $stmt->bind_param("i", $buildingID);

        if ($stmt->execute()) {
            echo json_encode(['message' => 'Building deleted successfully']);
        } else {
            echo json_encode(['message' => 'Error deleting building', 'error' => $stmt->error]);
        }

        $stmt->close();
    } else {
        echo json_encode(['message' => 'BuildingID is required']);
    }
    exit; // Ensure no additional output is sent
}
?>
