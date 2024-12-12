<?php  
// Include the database connection
include('db.php');

// Allow CORS
header("Access-Control-Allow-Origin: http://localhost:8000");  // Ensure this matches your React frontend URL
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

// Get data from the frontend (assuming it's coming via a POST request)
$data = json_decode(file_get_contents("php://input"), true);



// Ensure username and password are provided
if (isset($data['Username']) && isset($data['Password'])) {
    $user = $conn->real_escape_string($data['Username']);
    $pass = $conn->real_escape_string($data['Password']);
    
    // Query to get the employee details along with their position and access level
    $sql = "
    SELECT 
        e.EmpID, e.FirstName, e.LastName, e.PhoneNo, 
        p.PositionName, al.LevelName AS AccessLevel, 
        e.Username, e.`Password`  -- Using backticks to escape the 'Password' column name
    FROM 
        employee e
    JOIN 
        position p ON e.PositionID = p.PositionID
    JOIN
        accesslevel al ON p.AccessLevelID = al.AccessLevelID
    WHERE 
        e.Username = ?
    ";

    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("s", $user); // Bind username parameter
        $stmt->execute();
        $result = $stmt->get_result();
        
        // If a user is found
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            
            // Log the row fetched from DB for debugging
            error_log("Fetched user: " . print_r($row, true));
            
            // Get the stored hashed password
            $storedHash = $row['Password'];  // Assuming the password is stored hashed in the 'Password' column
            
            // Log the stored hashed password for debugging (be cautious in production)
            error_log("Stored hashed password: " . $storedHash);
            
            // Verify the entered password with the stored hash
            if (password_verify($pass, $storedHash)) {
                // Return username, position, and access level in the response data
                $accessLevel = isset($row['AccessLevel']) ? $row['AccessLevel'] : 'Not Available';  // Default value if null
                echo json_encode([
                    'success' => true,
                    'message' => 'Login successful',
                    'EmpID' => $row['EmpID'],
                    'FirstName' => $row['FirstName'],
                    'LastName' => $row['LastName'],
                    'PhoneNo' => $row['PhoneNo'],
                    'PositionName' => $row['PositionName'],
                    'AccessLevel' => $row['AccessLevel'],
                    'Username' => $row['Username'] // Add the Username to the response
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'User not found']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Query preparation failed']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Missing username or password']);
}

// Close the database connection
$conn->close();
?>
