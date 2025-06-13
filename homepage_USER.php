<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_username']) && !isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$flights = $conn->query("SELECT * FROM flights ORDER BY departure ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Skyport - Airport Management System Dashboard" />
  <title>Dashboard - SkyPort AMS</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
  <style>
    /* === Your Existing CSS === */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
        background: url('AirportPic.jpg') no-repeat center center fixed;
        background-size: cover;
        min-height: 100vh;
        position: relative;
        color: #333;
    }

    body::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.9);
        z-index: 0;
    }

    header {
        background: rgba(0, 51, 102, 0.95);
        color: white;
        padding: 15px 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
        z-index: 1;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .logo-container {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .logo-container img {
        height: 40px;
    }

    nav ul {
        display: flex;
        list-style: none;
        gap: 25px;
    }

    nav a {
        color: white;
        text-decoration: none;
        font-weight: 500;
        padding: 8px 12px;
        border-radius: 4px;
        transition: all 0.3s;
    }

    nav a:hover {
        background: rgba(255, 255, 255, 0.2);
    }

    .logout-btn {
        background: #e74c3c;
        padding: 8px 15px;
        border-radius: 4px;
    }

    .logout-btn:hover {
        background: #c0392b;
    }

    main {
        position: relative;
        z-index: 1;
        padding: 30px;
        max-width: 1200px;
        margin: 0 auto;
    }

    .intro {
        text-align: center;
        margin-bottom: 40px;
        padding: 30px;
        background: rgba(255, 255, 255, 0.8);
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }

    .intro h2 {
        color: #003366;
        font-size: 2.2rem;
        margin-bottom: 15px;
    }

    .intro p {
        color: #555;
        font-size: 1.1rem;
        max-width: 700px;
        margin: 0 auto;
    }

    .features {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 25px;
        margin-bottom: 40px;
    }

    .feature {
        background: white;
        padding: 25px;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        transition: transform 0.3s, box-shadow 0.3s;
    }

    .feature:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }

    .feature h3 {
        color: #003366;
        margin-bottom: 15px;
        font-size: 1.3rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .feature p {
        color: #666;
        line-height: 1.6;
    }

    footer {
        background: rgba(0, 51, 102, 0.95);
        color: white;
        text-align: center;
        padding: 20px;
        position: relative;
        z-index: 1;
        margin-top: 40px;
    }

    .stats-bar {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
        text-align: center;
    }

    .stat-card h3 {
        color: #0066cc;
        font-size: 2rem;
        margin-bottom: 5px;
    }

    .stat-card a, .stat-card p {
        color: #0066cc;
        text-decoration: none;
        font-size: 0.9rem;
    }

    @media (max-width: 768px) {
        header {
            flex-direction: column;
            padding: 15px;
        }

        .logo-container {
            margin-bottom: 15px;
        }

        nav ul {
            gap: 15px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .stats-bar {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 480px) {
        .stats-bar {
            grid-template-columns: 1fr;
        }

        .features {
            grid-template-columns: 1fr;
        }
    }

    /* Additional CSS for table and modal */
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        background: white;
        border-radius: 10px;
        overflow: hidden;
    }

    table th, table td {
        padding: 12px;
        border: 1px solid #ddd;
        text-align: center;
    }

    .booking-btn {
        background-color: #0066cc;
        color: white;
        padding: 6px 12px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    .booking-btn:hover {
        background-color: #004999;
    }

    .modal {
    display: none;
    position: fixed;
    z-index: 999;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    overflow-y: auto;
    padding: 30px 15px; /* Added padding for spacing on small screens */
    box-sizing: border-box;
}

.modal-content {
    background: #fff;
    margin: auto;
    padding: 30px;
    border-radius: 8px;
    width: 100%;
    max-width: 450px;
    position: relative;
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
    animation: fadeIn 0.3s ease-in-out;
}

/* Smooth entrance */
@keyframes fadeIn {
    from {
        transform: translateY(-20px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}
    .modal-content h2 {
        margin-bottom: 20px;
        color: #003366;
    }

    .modal-content label {
        display: block;
        margin-top: 10px;
        font-weight: bold;
    }

    .modal-content input, .modal-content select {
        width: 100%;
        padding: 8px;
        margin-top: 5px;
        margin-bottom: 15px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    .close-btn {
        position: absolute;
        top: 10px;
        right: 15px;
        background: none;
        border: none;
        font-size: 1.5rem;
        color: #333;
        cursor: pointer;
    }

    .submit-btn {
        width: 100%;
        background: #003366;
        color: white;
        border: none;
        padding: 10px;
        border-radius: 4px;
        font-size: 1rem;
        cursor: pointer;
    }

    .submit-btn:hover {
        background: #003366;
    }
  </style>
</head>
<body>
<header>
  <div class="logo-container">
    <img src="AirportLogoPic.jpg" alt="SkyPort Logo" />
    <h1>SkyPort AMS</h1>
  </div>
  <nav>
    <ul>
      <li><a href="#">Home</a></li>
      <li><a href="#">About</a></li>
      <li><a href="#">Services</a></li>
      <li><a href="#">Contact</a></li>
    </ul>
  </nav>
  <button class="logout-btn" onclick="location.href='logout.php'">Logout</button>
</header>

<main>
  <div class="intro">
    <h2>Welcome to SkyPort Airport Management System</h2>
    <p>Your one-stop solution for efficient airport management.</p>
  </div>

  <div class="stats-bar">
    <div class="stat-card">
    <button 
      onclick="toggleFlightTable()" 
      style="
        background-color: #003366; 
        color: #fff; 
        font-size: 1.3rem; 
        font-weight: bold; 
        padding: 18px 40px; 
        border: none; 
        border-radius: 8px; 
        box-shadow: 0 4px 16px rgba(0,0,0,0.10); 
        cursor: pointer; 
        transition: background 0.2s, transform 0.2s;
        margin: 10px 0;
        letter-spacing: 1px;
      "
      onmouseover="this.style.backgroundColor='#0050a0'; this.style.transform='scale(1.04)'"
      onmouseout="this.style.backgroundColor='#003366'; this.style.transform='scale(1)'"
    >
      <i class="fas fa-plane-departure" style="margin-right: 12px;"></i>
      Flights Today
    </button>
    <!-- Empty div for layout spacing -->
    <div style="width: 50%; display: inline-block;"></div>
    <div style="clear: both;"></div>

    </div>
    <!-- You can keep other stat cards -->
  </div>

  <!-- Flights Table -->
  <div id="flightTable" style="display: none;">
    <table>
      <thead>
        <tr style="background-color:#003366; color:white;">
          <th>Flight No</th>
          <th>Airline</th>
          <th>Departure</th>
          <th>Arrival</th>
          <th>Gate</th>
          <th>Booking</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $flights->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($row['flight_no']) ?></td>
            <td><?= htmlspecialchars($row['airline']) ?></td>
            <td><?= htmlspecialchars($row['departure']) ?></td>
            <td><?= htmlspecialchars($row['arrival']) ?></td>
            <td><?= htmlspecialchars($row['gate']) ?></td>
            <td><button class="booking-btn" onclick="openBookingModal('<?= $row['flight_no'] ?>')">Book</button></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <!-- Booking Modal -->
  <div class="modal" id="bookingModal">
    <div class="modal-content">
      <button class="close-btn" onclick="closeBookingModal()">&times;</button>
      <h2>Book Flight</h2>
      <form id="bookingForm">
         <input type="hidden" id="flight_no" name="flight_no" />
        <input type="hidden" id="user_id" name="user_id" value="<?= isset($_SESSION['user_id']) ? htmlspecialchars($_SESSION['user_id']) : '' ?>" />
        <label>Full Name</label>
        <input type="text" name="name" required />

        <label>Passport No</label>
        <input type="text" name="passport" required />

        <label>Class</label>
        <select name="class" required>
          <option value="Economy">Economy</option>
          <option value="Business">Business</option>
          <option value="First Class">First Class</option>
        </select>

        <label>Seat Preference</label>
        <select name="seat" required>
          <option value="Window">Window</option>
          <option value="Aisle">Aisle</option>
          <option value="Middle">Middle</option>
        </select>
        <label>Card Number</label>
        <input type="text" name="card_number" required pattern="\d{16}" title="Enter 16 digit card number" />

        <label>Expiry Date</label>
        <input type="month" name="expiry" required />

        <label>CVV</label>
        <input type="text" name="cvv" required pattern="\d{3}" title="Enter 3 digit CVV" />

        <button type="submit" class="submit-btn">Confirm Booking</button>
      </form>
    </div>
  </div>
</main>

<footer>
  <p>&copy; <?= date('Y') ?> SkyPort AMS. All rights reserved.</p>
</footer>

<script>
  function toggleFlightTable() {
    const table = document.getElementById("flightTable");
    table.style.display = table.style.display === "none" ? "block" : "none";
  }

  function openBookingModal(flightNo) {
    document.getElementById("flight_no").value = flightNo;
    document.getElementById("bookingModal").style.display = "flex";
  }

  function closeBookingModal() {
    document.getElementById("bookingModal").style.display = "none";
  }

  document.getElementById("bookingForm").addEventListener("submit", function (e) {
    e.preventDefault();
    const formData = new FormData(this);
    alert("Booking confirmed for " + formData.get("name") + " on flight " + formData.get("flight_no"));
    closeBookingModal();
  });
  document.getElementById("bookingForm").addEventListener("submit", function (e) {
  e.preventDefault();

  const formData = new FormData(this);

  fetch('booking.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.text())
  .then(data => {
    alert(data); // success or error
    closeBookingModal();
  })
  .catch(err => alert("Error: " + err));
});
</script>
</body>
</html>