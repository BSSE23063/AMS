<?php
session_start();
require 'db_connect.php';
require 'weather.php';
require 'archive_flights.php';

// Set timezone to Pakistan timezone
date_default_timezone_set('Asia/Karachi');

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Check and archive completed flights
archiveCompletedFlights($conn);

// Fetch flights from database
$flights = $conn->query("SELECT * FROM flights ORDER BY departure ASC");

// Get weather data for Lahore
$weatherData = getWeather('Lahore');
$temperature = isset($weatherData['current']['temp_c']) ? round($weatherData['current']['temp_c']) : 'N/A';
$weatherDesc = isset($weatherData['current']['condition']['text']) ? $weatherData['current']['condition']['text'] : 'N/A';
$weatherIcon = isset($weatherData['current']['condition']['icon']) ? $weatherData['current']['condition']['icon'] : '//cdn.weatherapi.com/weather/64x64/day/113.png';
$currentTime = date('h:i A'); // Current time in 12-hour format
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flight Management - SkyPort AMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        :root {
            --primary-color: #003366;
            --secondary-color: #0066cc;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
        }
        
        body {
            background-color: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .dashboard-header {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }
        
        .airline-logo {
            width: 30px;
            height: 30px;
            object-fit: contain;
            margin-right: 10px;
            border-radius: 50%;
            background: white;
            padding: 2px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .flight-row:hover {
            background-color: #f8f9fa;
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-scheduled { background-color: #e3f2fd; color: #1976d2; }
        .status-ontime { background-color: #e8f5e9; color: #388e3c; }
        .status-delayed { background-color: #fff3e0; color: #ffa000; }
        .status-cancelled { background-color: #ffebee; color: #d32f2f; }
        
        .search-container {
            position: relative;
            margin-bottom: 20px;
        }
        
        .weather-widget {
            background: linear-gradient(135deg, #4b6cb7, #182848);
            color: white;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.2);
        }
        
        .action-btn {
            border: none;
            background: none;
            padding: 5px;
            margin: 0 3px;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            transition: background 0.2s;
        }
        
        .action-btn:hover {
            background: #f0f0f0;
        }
        
        .action-btn.edit { color: var(--secondary-color); }
        .action-btn.delete { color: var(--danger-color); }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1050;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 25px;
            border-radius: 8px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: black;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="homepage.php">
                <i class="fas fa-plane me-2"></i>
                <strong>SkyPort</strong> AMS
            </a>
            <div class="d-flex align-items-center">
                <div class="me-3 text-white">
                    <i class="fas fa-user-circle me-1"></i>
                    <?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?>
                </div>
                <a href="login.html" class="btn btn-sm btn-outline-light">
                    <i class="fas fa-sign-out-alt me-1"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container py-4">
        <!-- Dashboard Header -->
        <div class="dashboard-header p-4 mb-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2><i class="fas fa-plane-departure me-2 text-primary"></i> Flight Management</h2>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="d-flex gap-2">
                        <a href="view_archives.php" class="btn btn-secondary">
                            <i class="fas fa-archive me-2"></i>View Archives
                        </a>                        <button class="btn btn-primary" id="addFlightBtn">
                            <i class="fas fa-plus me-2"></i> Add Flight
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Weather Widget -->
        <div class="row mb-4">
            <div class="col-md-8">                <div class="search-container">
                    <div class="input-group mb-3">
                        <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" id="searchInput" placeholder="Search by Flight No, Airline, Route...">
                        <select class="form-select" id="searchAirline" style="max-width: 150px;">
                            <option value="">All Airlines</option>
                            <option value="PIA">PIA</option>
                            <option value="Skyport">Skyport</option>
                            <option value="AirBlue">AirBlue</option>
                        </select>
                        <button class="btn btn-outline-primary" type="button" id="searchBtn">Search</button>
                    </div>
                </div>
            </div>            <div class="col-md-4">
                <div class="weather-widget d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0" id="currentTime"><?php echo $currentTime; ?></h5>
                        <small><?php echo date('M d, Y'); ?></small>
                    </div>
                    <div class="text-center">
                        <h5 class="mb-0"><?php echo $temperature; ?>°C</h5>
                        <small><?php echo htmlspecialchars($weatherDesc); ?></small>
                    </div>
                    <div>
                        <img src="<?php echo $weatherIcon; ?>" alt="Weather Icon" style="width: 64px; height: 64px;">
                    </div>
                </div>
            </div>
        </div>

        <!-- Flights Table -->
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Flight No.</th>
                                <th>Airline</th>
                                <th>Route</th>
                                <th>Departure</th>
                                <th>Arrival</th>
                                <th>Gate</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($flight = $flights->fetch_assoc()): ?>
                                <tr class="flight-row">
                                    <td><?php echo htmlspecialchars($flight['flight_no']); ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($flight['airline']); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($flight['route']); ?></td>
                                    <td><?php echo date('M j, Y g:i A', strtotime($flight['departure'])); ?></td>
                                    <td><?php echo date('M j, Y g:i A', strtotime($flight['arrival'])); ?></td>
                                    <td>Gate <?php echo htmlspecialchars($flight['gate']); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo strtolower($flight['status']); ?>">
                                            <?php echo htmlspecialchars($flight['status']); ?>
                                        </span>
                                    </td>                                <td>
                                    <button class="action-btn edit" onclick="editFlight('<?php echo htmlspecialchars($flight['flight_no']); ?>')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="action-btn delete" onclick="deleteFlight('<?php echo htmlspecialchars($flight['flight_no']); ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Flight Modal -->
    <div id="flightModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h4 class="mb-4"><i class="fas fa-plus-circle me-2 text-primary"></i>Add New Flight</h4>
            <form action="addflight.php" method="post">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="flight_no" class="form-label">Flight Number</label>
                        <input type="text" class="form-control" id="flight_no" name="flight_no" required>
                    </div>                    <div class="col-md-6">
                        <label for="airline" class="form-label">Airline</label>
                        <select class="form-select" id="airline" name="airline" required>
                            <option value="">Select Airline</option>
                            <option value="PIA">PIA</option>
                            <option value="Skyport">Skyport</option>
                            <option value="AirBlue">AirBlue</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="route" class="form-label">Route (e.g., JFK→LAX)</label>
                    <input type="text" class="form-control" id="route" name="route" placeholder="Origin→Destination" required>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="departure" class="form-label">Departure</label>
                        <input type="datetime-local" class="form-control" id="departure" name="departure" required>
                    </div>
                    <div class="col-md-6">
                        <label for="arrival" class="form-label">Arrival</label>
                        <input type="datetime-local" class="form-control" id="arrival" name="arrival" required>
                    </div>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="gate" class="form-label">Gate</label>
                        <input type="text" class="form-control" id="gate" name="gate" required>
                    </div>
                    <div class="col-md-6">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="Scheduled">Scheduled</option>
                            <option value="On Time">On Time</option>
                            <option value="Delayed">Delayed</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
                <div class="d-flex justify-content-end">
                    <button type="button" class="btn btn-secondary me-2" id="cancelModal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Flight</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Flight Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h4 class="mb-4"><i class="fas fa-edit me-2 text-primary"></i>Edit Flight</h4>
            <form id="editFlightForm">
                <input type="hidden" id="edit_flight_id" name="flight_id">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Flight Number</label>
                        <input type="text" class="form-control" id="edit_flight_no" name="flight_no" required>
                    </div>
                    <div class="col-md-6 mb-3">                        <label>Airline</label>
                        <select class="form-select" id="edit_airline" name="airline" required>
                            <option value="">Select Airline</option>
                            <option value="PIA">PIA</option>
                            <option value="Skyport">Skyport</option>
                            <option value="AirBlue">AirBlue</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label>Route</label>
                    <input type="text" class="form-control" id="edit_route" name="route" required>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Departure</label>
                        <input type="datetime-local" class="form-control" id="edit_departure" name="departure" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Arrival</label>
                        <input type="datetime-local" class="form-control" id="edit_arrival" name="arrival" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Gate</label>
                        <input type="text" class="form-control" id="edit_gate" name="gate" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Status</label>
                        <select class="form-control" id="edit_status" name="status" required>
                            <option value="SCHEDULED">Scheduled</option>
                            <option value="ON TIME">On Time</option>
                            <option value="DELAYED">Delayed</option>
                            <option value="CANCELLED">Cancelled</option>
                        </select>
                    </div>
                </div>
                <div class="text-end mt-4">
                    <button type="button" class="btn btn-secondary me-2" id="cancelEditModal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Modal functionality
        const modal = document.getElementById("flightModal");
        const addBtn = document.getElementById("addFlightBtn");
        const closeBtn = document.querySelector(".close");
        const cancelBtn = document.getElementById("cancelModal");

        // Open modal
        addBtn.onclick = function() {
            modal.style.display = "block";
            // Set current datetime as default
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            
            document.getElementById('departure').value = `${year}-${month}-${day}T${hours}:${minutes}`;
            document.getElementById('arrival').value = `${year}-${month}-${day}T${hours}:${minutes}`;
        }

        // Close modal
        closeBtn.onclick = function() {
            modal.style.display = "none";
        }

        cancelBtn.onclick = function() {
            modal.style.display = "none";
        }

        // Close when clicking outside modal
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }        // Enhanced search functionality
        document.getElementById('searchBtn').addEventListener('click', function() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const selectedAirline = document.getElementById('searchAirline').value;
            const rows = document.querySelectorAll('.flight-row');
            
            rows.forEach(row => {
                const rowText = row.textContent.toLowerCase();
                const airlineMatch = selectedAirline === '' || row.querySelector('td:nth-child(2)').textContent.trim() === selectedAirline;
                const textMatch = searchTerm === '' || rowText.includes(searchTerm);
                if (rowText.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

    // Edit Flight
    const editModal = document.getElementById("editModal");
    const editCloseBtn = editModal.querySelector(".close");
    const cancelEditBtn = document.getElementById("cancelEditModal");

    function editFlight(flightNo) {
        // Fetch flight details
        fetch(`edit.php?id=${encodeURIComponent(flightNo)}`)
            .then(response => response.json())
            .then(flight => {
                document.getElementById('edit_flight_id').value = flight.flight_no; // Store original flight number
                document.getElementById('edit_flight_no').value = flight.flight_no;
                document.getElementById('edit_airline').value = flight.airline;
                document.getElementById('edit_route').value = flight.route;
                document.getElementById('edit_departure').value = flight.departure.replace(' ', 'T');
                document.getElementById('edit_arrival').value = flight.arrival.replace(' ', 'T');
                document.getElementById('edit_gate').value = flight.gate;
                document.getElementById('edit_status').value = flight.status;
                editModal.style.display = "block";
            });
        }

        // Close edit modal
        editCloseBtn.onclick = function() {
            editModal.style.display = "none";
        }

        cancelEditBtn.onclick = function() {
            editModal.style.display = "none";
        }

        // Handle edit form submission
        document.getElementById('editFlightForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('edit.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    editModal.style.display = "none";
                    location.reload();
                } else {
                    alert('Error updating flight: ' + data.error);
                }
            });
        });

    // Delete Flight
    function deleteFlight(flightNo) {
        if (confirm('Are you sure you want to delete this flight?')) {
            const formData = new FormData();
            formData.append('flight_id', flightNo);
                
                fetch('delete.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error deleting flight: ' + data.error);
                    }
                });
            }
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            if (event.target == editModal) {
                editModal.style.display = "none";
            }
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>