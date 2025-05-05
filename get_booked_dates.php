<?php
// Database connection settings
$host = 'localhost'; // Your host (usually localhost)
$dbname = 'earmacs'; // Your database name
$username = 'root'; // Your database username
$password = ''; // Your database password

// Create a PDO instance
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    die();
}

// Fetch the property_id from the URL
$property_id = $_GET['property_id'] ?? null;

if ($property_id) {
    // Prepare the SQL query to get booked dates
    $stmt = $pdo->prepare("SELECT checkin_date, checkout_date FROM bookings WHERE property_id = :property_id");
    $stmt->execute(['property_id' => $property_id]);
    $bookedDates = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $dates = [];
    // Loop through each booking date range and store each day in the $dates array
    foreach ($bookedDates as $date) {
        $startDate = new DateTime($date['checkin_date']);
        $endDate = new DateTime($date['checkout_date']);
        
        // Loop through the range of booked dates
        while ($startDate <= $endDate) {
            $dates[] = $startDate->format('Y-m-d');
            $startDate->modify('+1 day');
        }
    }

    // Return booked dates as JSON to the frontend
    echo json_encode($dates);
} else {
    // If no property_id is passed, return an empty array
    echo json_encode([]);
}
?>
