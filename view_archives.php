<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Handle permanent deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_archive'])) {
    $archive_id = $_POST['archive_id'];
    $delete_sql = "DELETE FROM flight_archives WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $archive_id);
    
    if ($delete_stmt->execute()) {
        header('Location: view_archives.php');
        exit();
    } else {
        $error_message = "Error deleting archived flight";
    }
}

// Create flight_archives table if it doesn't exist
$archiveTableSql = "CREATE TABLE IF NOT EXISTS flight_archives (
    id INT AUTO_INCREMENT PRIMARY KEY,
    flight_no VARCHAR(20) NOT NULL,
    airline VARCHAR(100) NOT NULL,
    route VARCHAR(100) NOT NULL,
    departure DATETIME NOT NULL,
    arrival DATETIME NOT NULL,
    gate VARCHAR(10) NOT NULL,
    status VARCHAR(20) NOT NULL,
    archived_at DATETIME NOT NULL,
    archive_reason VARCHAR(100) NOT NULL
)";

if ($conn->query($archiveTableSql) === FALSE) {
    die('Error creating archive table: ' . $conn->error);
}

// Fetch archived flights from database
$archives = $conn->query("SELECT * FROM flight_archives ORDER BY archived_at DESC");
if (!$archives) {
    die('Error fetching archives: ' . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archived Flights - SkyPort AMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        :root {
            --primary-color: #003366;
            --secondary-color: #0066cc;
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
        
        .archive-row:hover {
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
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="homepage.php">
                <i class="fas fa-plane-departure me-2"></i>SkyPort AMS
            </a>
            <a href="homepage.php" class="btn btn-outline-light">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container py-4">
        <!-- Dashboard Header -->
        <div class="dashboard-header p-4 mb-4">
            <div class="row align-items-center">                <div class="col-md-6">
                    <h2 class="mb-0"><i class="fas fa-archive me-2 text-primary"></i>Archived Flights</h2>
                </div>
                <div class="col-md-6">
                    <div class="d-flex gap-2">
                        <input type="date" class="form-control" id="searchDate" name="searchDate">
                        <button class="btn btn-outline-primary" type="button" id="searchDateBtn">
                            <i class="fas fa-search"></i> Search by Date
                        </button>
                        <button class="btn btn-outline-secondary" type="button" id="resetSearch">
                            <i class="fas fa-redo"></i> Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Archives Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>                                <th>Flight No.</th>
                                <th>Airline</th>
                                <th>Route</th>
                                <th>Departure</th>
                                <th>Arrival</th>
                                <th>Gate</th>
                                <th>Final Status</th>
                                <th>Archived On</th>
                                <th>Reason</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($archive = $archives->fetch_assoc()): ?>                            <tr class="archive-row">
                                <td><?php echo htmlspecialchars($archive['flight_no']); ?></td>
                                <td><?php echo htmlspecialchars($archive['airline']); ?></td>
                                <td><?php echo htmlspecialchars($archive['route']); ?></td>
                                <td><?php echo date('Y-m-d H:i', strtotime($archive['departure'])); ?></td>
                                <td><?php echo date('Y-m-d H:i', strtotime($archive['arrival'])); ?></td>
                                <td><?php echo htmlspecialchars($archive['gate']); ?></td>
                                <td>
                                    <span class="status-badge bg-secondary text-white">
                                        <?php echo htmlspecialchars($archive['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('Y-m-d H:i', strtotime($archive['archived_at'])); ?></td>
                                <td><?php echo htmlspecialchars($archive['archive_reason']); ?></td>
                                <td>
                                    <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to permanently delete this archived flight?');">
                                        <input type="hidden" name="archive_id" value="<?php echo $archive['id']; ?>">
                                        <button type="submit" name="delete_archive" class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php if($archives->num_rows === 0): ?>
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <i class="fas fa-info-circle me-2 text-info"></i>No archived flights found
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Date search functionality
        document.getElementById('searchDateBtn').addEventListener('click', function() {
            const searchDate = document.getElementById('searchDate').value;
            const rows = document.querySelectorAll('.archive-row');
            
            if (!searchDate) {
                alert('Please select a date to search');
                return;
            }

            rows.forEach(row => {
                const archivedDate = row.querySelector('td:nth-child(8)').textContent.split(' ')[0]; // Get just the date part
                if (archivedDate === searchDate) {
                    row.style.display = '';
                    row.classList.add('table-warning'); // Highlight matched rows
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Reset search
        document.getElementById('resetSearch').addEventListener('click', function() {
            const rows = document.querySelectorAll('.archive-row');
            rows.forEach(row => {
                row.style.display = '';
                row.classList.remove('table-warning');
            });
            document.getElementById('searchDate').value = '';
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>
