<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch flights from database
$flights = $conn->query("SELECT * FROM flights ORDER BY departure ASC");
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
                    <button class="btn btn-primary" id="addFlightBtn">
                        <i class="fas fa-plus me-2"></i> Add Flight
                    </button>
                </div>
            </div>
        </div>

        <!-- Search and Weather Widget -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="search-container">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" placeholder="Search flights...">
                        <button class="btn btn-outline-secondary" type="button">Search</button>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="weather-widget d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">24°C</h5>
                        <small>Smoke</small>
                    </div>
                    <div class="text-end">
                        <h5 class="mb-0"><?php echo date('g:i A'); ?></h5>
                        <small><?php echo date('m/d/Y'); ?></small>
                    </div>
                    <i class="fas fa-smog fa-2x"></i>
                </div>
            </div>
        </div>

        <!-- Flights Table -->
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
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
                            <?php if ($flights->num_rows > 0): ?>
                                <?php while($row = $flights->fetch_assoc()): 
                                    $statusClass = strtolower(str_replace(' ', '', $row['status']));
                                    $routeParts = explode('→', $row['route']);
                                ?>
                                <tr class="flight-row">
                                    <td class="fw-bold"><?php echo htmlspecialchars($row['flight_no']); ?></td>
                                    <td>
                                        <!-- <img src="airline-logos/<?php echo strtolower($row['airline']); ?>.png" 
                                             alt="<?php echo htmlspecialchars($row['airline']); ?>" 
                                             class="airline-logo" 
                                             onerror="this.src='airline-logos/default.png'"> -->
                                        <?php echo htmlspecialchars($row['airline']); ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark me-1">
                                            <?php echo htmlspecialchars(trim($routeParts[0])); ?>
                                        </span>
                                        <i class="fas fa-arrow-right text-muted mx-1"></i>
                                        <span class="badge bg-light text-dark">
                                            <?php echo htmlspecialchars(trim($routeParts[1] ?? '')); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-bold"><?php echo date('M j, Y', strtotime($row['departure'])); ?></div>
                                        <small class="text-muted"><?php echo date('g:i A', strtotime($row['departure'])); ?></small>
                                    </td>
                                    <td>
                                        <div class="fw-bold"><?php echo date('M j, Y', strtotime($row['arrival'])); ?></div>
                                        <small class="text-muted"><?php echo date('g:i A', strtotime($row['arrival'])); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">Gate <?php echo htmlspecialchars($row['gate']); ?></span>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo $statusClass; ?>">
                                            <?php echo htmlspecialchars($row['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="action-btn edit" title="Edit">
                                            <i class="fas fa-pencil-alt"></i>
                                        </button>
                                        <button class="action-btn delete" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="fas fa-plane-slash fa-2x text-muted mb-3"></i>
                                        <h5>No flights scheduled</h5>
                                        <p class="text-muted">Add your first flight using the button above</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
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
                    </div>
                    <div class="col-md-6">
                        <label for="airline" class="form-label">Airline</label>
                        <input type="text" class="form-control" id="airline" name="airline" required>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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
        }

        // Basic search functionality
        document.querySelector('.search-container button').addEventListener('click', function() {
            const searchTerm = document.querySelector('.search-container input').value.toLowerCase();
            const rows = document.querySelectorAll('.flight-row');
            
            rows.forEach(row => {
                const rowText = row.textContent.toLowerCase();
                if (rowText.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
<?php
$conn->close();
?>