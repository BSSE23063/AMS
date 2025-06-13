<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_username'])) {
    echo "Access denied.";
    exit();
}

// Get POST data
$name = $_POST['name'];
$passport = $_POST['passport'];
$class = $_POST['class'];
$seat = $_POST['seat'];
$flight_no = $_POST['flight_no'];
$card_number = $_POST['card_number'];
$expiry = $_POST['expiry'];
$cvv = $_POST['cvv'];

// === Price Rules ===
$base_price = 100;
$class_addon = [
    "Economy" => 0,
    "Business" => 150,
    "First Class" => 300
];
$seat_addon = [
    "Window" => 20,
    "Aisle" => 10,
    "Middle" => 0
];

// Final price
$price = $base_price + $class_addon[$class] + $seat_addon[$seat];

// === Insert into bookings table ===
$booking_stmt = $conn->prepare("INSERT INTO bookings (flight_no, name, passport, class, seat) VALUES (?, ?, ?, ?, ?)");
$booking_stmt->bind_param("sssss", $flight_no, $name, $passport, $class, $seat);
$booking_success = $booking_stmt->execute();

if ($booking_success) {
    $booking_id = $booking_stmt->insert_id;

    // === Insert into payments table ===
    $payment_stmt = $conn->prepare("INSERT INTO payments (booking_id, amount, card_number, expiry, cvv) VALUES (?, ?, ?, ?, ?)");
    $payment_stmt->bind_param("idsss", $booking_id, $price, $card_number, $expiry, $cvv);
    $payment_success = $payment_stmt->execute();

    if ($payment_success) {
        echo "Booking and payment successful!";
    } else {
        echo "Booking successful but payment failed.";
    }

} else {
    echo "Booking failed. Please try again.";
}
?>
