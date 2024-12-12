<?php
// Include database connection file
require_once('db.php');

// Handle POST request to create a new booking
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data['EmployeeID']) && isset($data['RoomID']) && isset($data['BookingDate']) && isset($data['StartTime']) && isset($data['EndTime']) && isset($data['SecretNumber'])) {
        $employeeID = (int)$data['EmployeeID'];
        $roomID = (int)$data['RoomID'];
        $bookingDate = $data['BookingDate'];
        $startTime = $data['StartTime'];
        $endTime = $data['EndTime'];
        $bookingStatus = isset($data['BookingStatus']) ? $data['BookingStatus'] : 'Pending';
        $secretNumber = $data['SecretNumber'];

        // Insert booking data into the Booking table
        $sql = "INSERT INTO Booking (EmployeeID, RoomID, BookingDate, StartTime, EndTime, BookingStatus, SecretNumber) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iisssss", $employeeID, $roomID, $bookingDate, $startTime, $endTime, $bookingStatus, $secretNumber);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Booking created successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error creating booking']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    }
}

// Handle GET request to fetch all bookings
elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['BookingID'])) {
        // Fetch specific booking by BookingID
        $bookingID = (int)$_GET['BookingID'];
        $sql = "SELECT * FROM Booking WHERE BookingID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $bookingID);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $booking = $result->fetch_assoc();
            echo json_encode($booking);
        } else {
            echo json_encode(['message' => 'Booking not found']);
        }
        $stmt->close();
    } else {
        // Fetch all bookings
        $sql = "SELECT * FROM Booking";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $bookings = [];
            while ($row = $result->fetch_assoc()) {
                $bookings[] = $row;
            }
            echo json_encode($bookings);
        } else {
            echo json_encode(['message' => 'No bookings found']);
        }
    }
}

// Handle PUT request to update booking details
elseif ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data['BookingID']) && isset($data['EmployeeID']) && isset($data['RoomID']) && isset($data['BookingDate']) && isset($data['StartTime']) && isset($data['EndTime']) && isset($data['SecretNumber'])) {
        $bookingID = (int)$data['BookingID'];
        $employeeID = (int)$data['EmployeeID'];
        $roomID = (int)$data['RoomID'];
        $bookingDate = $data['BookingDate'];
        $startTime = $data['StartTime'];
        $endTime = $data['EndTime'];
        $bookingStatus = isset($data['BookingStatus']) ? $data['BookingStatus'] : 'Pending';
        $secretNumber = $data['SecretNumber'];

        $sql = "UPDATE Booking SET EmployeeID = ?, RoomID = ?, BookingDate = ?, StartTime = ?, EndTime = ?, BookingStatus = ?, SecretNumber = ? WHERE BookingID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iisssssi", $employeeID, $roomID, $bookingDate, $startTime, $endTime, $bookingStatus, $secretNumber, $bookingID);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Booking updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating booking']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    }
}

// Handle DELETE request to delete a booking
elseif ($_SERVER['REQUEST_METHOD'] == 'DELETE' && isset($_GET['BookingID'])) {
    $bookingID = (int)$_GET['BookingID'];

    $sql = "DELETE FROM Booking WHERE BookingID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $bookingID);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Booking deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error deleting booking']);
    }
    $stmt->close();
}

// Close database connection
$conn->close();
?>
