<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $property_id    = $_POST['property_id'] ?? null;
    $guest_name     = $_POST['Name'] ?? '';
    $guest_email    = $_POST['Email'] ?? '';
    $guest_phone    = $_POST['Phone'] ?? '';
    $checkin_date   = $_POST['Check-in'] ?? '';
    $checkout_date  = $_POST['Check-out'] ?? '';
    $occupancy      = $_POST['Occupancy'] ?? '';
    $package_type   = $_POST['Package_Type'] ?? '';
    $nights         = $_POST['Nights'] ?? 1;
    $extra_persons  = $_POST['Extra_Persons'] ?? 0;
    $total_price    = $_POST['Total_Price'] ?? 0.00;

    // Validate required fields
    $missing_fields = [];
    if (!$property_id)    $missing_fields[] = 'property_id';
    if (!$guest_name)     $missing_fields[] = 'Name';
    if (!$guest_email)    $missing_fields[] = 'Email';
    if (!$guest_phone)    $missing_fields[] = 'Phone';
    if (!$checkin_date)   $missing_fields[] = 'Check-in';
    if (!$checkout_date)  $missing_fields[] = 'Check-out';
    if (!$occupancy)      $missing_fields[] = 'Occupancy';
    if (!$package_type)   $missing_fields[] = 'Package_Type';
    if (!$total_price)    $missing_fields[] = 'Total_Price';

    if (!empty($missing_fields)) {
        die('Missing fields: ' . implode(', ', $missing_fields));
    }

    $sql = "INSERT INTO bookings (
                property_id, guest_name, guest_email, guest_phone,
                checkin_date, checkout_date, occupancy, package_type,
                nights, extra_persons, total_price, payment_status, created_at
            ) VALUES (
                :property_id, :guest_name, :guest_email, :guest_phone,
                :checkin_date, :checkout_date, :occupancy, :package_type,
                :nights, :extra_persons, :total_price, 'pending', NOW()
            )";

    $stmt = $pdo->prepare($sql);

    $stmt->bindParam(':property_id', $property_id);
    $stmt->bindParam(':guest_name', $guest_name);
    $stmt->bindParam(':guest_email', $guest_email);
    $stmt->bindParam(':guest_phone', $guest_phone);
    $stmt->bindParam(':checkin_date', $checkin_date);
    $stmt->bindParam(':checkout_date', $checkout_date);
    $stmt->bindParam(':occupancy', $occupancy);
    $stmt->bindParam(':package_type', $package_type);
    $stmt->bindParam(':nights', $nights);
    $stmt->bindParam(':extra_persons', $extra_persons);
    $stmt->bindParam(':total_price', $total_price);

    if ($stmt->execute()) {
        echo "✅ Booking saved successfully!";
    } else {
        echo "❌ Failed to save booking.";
    }
} else {
    die("Invalid request.");
}
